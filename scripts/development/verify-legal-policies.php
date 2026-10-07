<?php
declare(strict_types=1);

// Isolated policy/content verification only. Never migrate/reseed normal data.
if (PHP_SAPI !== 'cli') throw new RuntimeException('CLI only.');
$root = dirname(__DIR__, 2);
$backend = $root . '/.migration-backup/backend';
require $backend . '/vendor/autoload.php';
$app = require $backend . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$source = new PDO('sqlite:' . $backend . '/database/development/agendaally.sqlite');
$source->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$source->exec('PRAGMA query_only=ON');
$tables = ['languages', 'term_conditions', 'term_condition_translations',
    'privacy_policies', 'privacy_policy_translations', 'pages', 'page_translations'];
config(['database.default' => 'legal_policy_validation',
    'database.connections.legal_policy_validation' => [
        'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        'foreign_key_constraints' => true,
    ], 'cache.default' => 'array', 'session.driver' => 'array']);
$db = Illuminate\Support\Facades\DB::connection('legal_policy_validation');
if ($db->getDatabaseName() !== ':memory:') throw new RuntimeException('Disposable database required.');
foreach ($tables as $table) {
    $stmt = $source->prepare("SELECT sql FROM sqlite_master WHERE type='table' AND name=?");
    $stmt->execute([$table]);
    $sql = $stmt->fetchColumn();
    if (!$sql) throw new RuntimeException("Missing native policy schema: $table");
    $db->unprepared($sql);
    $stmt = $source->prepare("SELECT sql FROM sqlite_master WHERE type='index' AND tbl_name=? AND sql IS NOT NULL");
    $stmt->execute([$table]);
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $index) $db->unprepared($index);
}
App\Models\Language::query()->create([
    'locale' => 'en', 'title' => 'English', 'default' => true, 'active' => true, 'backward' => false,
]);
$checks = [];
$assert = function (bool $pass, string $label) use (&$checks): void {
    if (!$pass) throw new RuntimeException($label);
    $checks[] = $label;
};
$seed = fn() => $app->make(Database\Seeders\ContentPagesSeeder::class)->run();
$seed();
$terms = App\Models\TermCondition::first()->translations()->where('locale', 'en')->first();
$privacy = App\Models\PrivacyPolicy::first()->translations()->where('locale', 'en')->first();
$refund = App\Models\Page::where('type', App\Models\Page::REFUND_CANCELLATION)->first();
$refundText = $refund?->translations()->where('locale', 'en')->first();
$assert($terms?->description === Database\Seeders\Support\LegalPolicyContent::terms(), 'Fresh Terms default matches authoritative content');
$assert($privacy?->description === Database\Seeders\Support\LegalPolicyContent::privacy(), 'Fresh Privacy default matches authoritative content');
$assert($refundText?->description === Database\Seeders\Support\LegalPolicyContent::refund(), 'Fresh Refund/Cancellation default matches authoritative content');
$assert(App\Models\Page::count() === 4, 'Existing About defaults plus distinct legal Page seeded');
$snapshot = fn() => array_map(fn($t) => hash('sha256', serialize($db->table($t)->orderBy('id')->get()->toArray())), $tables);
$before = $snapshot();
$seed();
$assert($before === $snapshot(), 'Reseeding is an exact no-op');
$legacy = Database\Seeders\DevelopmentPreviewContentSeeder::legacyLegalDrafts();
$terms->update(['title' => 'Terms & Conditions', 'description' => $legacy['terms']]);
$privacy->update(['description' => Database\Seeders\ContentPagesSeeder::legacyPrivacyHtml()]);
$seed();
$assert($terms->fresh()->description === Database\Seeders\Support\LegalPolicyContent::terms(), 'Exact previous preview Terms upgraded');
$assert($privacy->fresh()->description === Database\Seeders\Support\LegalPolicyContent::privacy(), 'Exact historical Privacy placeholder upgraded');
$custom = $legacy['terms'] . '<p>Owner-customized clause.</p>';
$terms->update(['description' => $custom]);
$refundText->update(['description' => '<p>Owner refund text.</p>']);
$privacy->update(['title' => 'Owner title', 'description' => $legacy['privacy']]);
$seed();
$assert($terms->fresh()->description === $custom, 'Admin-customized Terms preserved, including additions to a known draft');
$assert($refundText->fresh()->description === '<p>Owner refund text.</p>', 'Admin-customized Refund text preserved');
$assert($privacy->fresh()->title === 'Owner title' && $privacy->fresh()->description === $legacy['privacy'], 'Owner-customized policy title preserved');
App\Models\TermCondition::first()->translations()->create(['locale' => 'fr', 'title' => 'Propriétaire', 'description' => '<p>Texte propriétaire.</p>']);
$seed();
$assert(App\Models\TermCondition::first()->translations()->where('locale', 'fr')->value('description') === '<p>Texte propriétaire.</p>', 'Non-English owner content preserved');
$refundText->delete();
$seed();
$assert($refund->translations()->where('locale', 'en')->value('description') === Database\Seeders\Support\LegalPolicyContent::refund(), 'Missing legal translation filled without replacing the Page');
$validator = Illuminate\Support\Facades\Validator::make([
    'type' => 'refund_cancellation', 'active' => 1, 'title' => ['en' => 'Refund & Cancellation Policy'],
    'description' => ['en' => '<p>Edited legal content.</p>'], 'images' => [],
], (new App\Http\Requests\Page\StoreRequest())->rules());
// The create request must reject the existing singleton type; ignore only
// that expected uniqueness failure to verify the image-free legal contract.
$errors = $validator->errors()->toArray();
$assert(!isset($errors['images']), 'Text-only Refund Page can be edited without a fabricated image');
$normalImages = Illuminate\Support\Facades\Validator::make(['type' => 'about', 'images' => []], [
    'images' => (new App\Http\Requests\Page\StoreRequest())->rules()['images'],
]);
$assert($normalImages->fails(), 'Nonlegal CMS image requirement unchanged');
$assert(in_array('refund_cancellation', App\Models\Page::TYPES, true), 'New policy included in native public route and Admin type allowlist');
echo json_encode(['status' => 'PASS', 'storage' => 'disposable :memory: using native policy schema',
    'checks' => $checks, 'scope' => 'Policy/content seeding only; not full application reseeding or legal certification'],
    JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR), "\n";
