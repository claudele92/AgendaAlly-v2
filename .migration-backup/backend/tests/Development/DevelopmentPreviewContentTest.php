<?php

declare(strict_types=1);

namespace Tests\Development;

use App\Models\Blog;
use App\Models\Faq;
use App\Models\Page;
use App\Models\PrivacyPolicy;
use App\Models\Settings;
use App\Models\TermCondition;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Symfony\Component\Console\Output\BufferedOutput;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\TestCase;

/**
 * Uses a fresh owned disposable SQLite bootstrap; never refreshes or touches
 * the preview's application database.
 */
class DevelopmentPreviewContentTest extends TestCase
{
    private ?\Illuminate\Foundation\Application $app = null;

    private ?string $databasePath = null;

    private array $originalEnvironment = [];

    protected function setUp(): void
    {
        parent::setUp();

        $backendPath = dirname(__DIR__, 2);
        $this->databasePath = $backendPath . '/database/development/.preview-content-test-'
            . bin2hex(random_bytes(8)) . '.sqlite';
        $this->setEnvironment([
            'APP_ENV' => 'local',
            'APP_DEBUG' => 'false',
            'APP_KEY' => 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=',
            'AGENDAALLY_DEVELOPMENT_DATABASE' => 'true',
            'DEVELOPMENT_MODE' => 'true',
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => $this->databasePath,
            'DATABASE_URL' => '',
            'DB_FOREIGN_KEYS' => 'true',
            'CACHE_DRIVER' => 'array',
            'SESSION_DRIVER' => 'array',
            'QUEUE_CONNECTION' => 'sync',
            'MAIL_MAILER' => 'array',
        ]);

        $this->app = require $backendPath . '/bootstrap/app.php';
        $this->app->make(ConsoleKernel::class)->bootstrap();
        $this->app['config']->set('database.default', 'sqlite');
        $this->app['config']->set('database.connections.sqlite.database', $this->databasePath);
        $this->app['config']->set('database.connections.sqlite.url', null);
        $this->app['config']->set('cache.default', 'array');
        $this->app['config']->set('session.driver', 'array');
        $this->app['config']->set('queue.default', 'sync');
        $this->app['config']->set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');
        DB::purge('sqlite');

        self::assertSame(
            Command::SUCCESS,
            $this->app->make(ConsoleKernel::class)->call('development:database-bootstrap', [
                '--confirm-empty-sqlite' => true,
            ]),
            'Focused coverage must begin with a newly owned disposable SQLite database.'
        );
    }

    protected function tearDown(): void
    {
        if ($this->app !== null) {
            DB::disconnect('sqlite');
            DB::purge('sqlite');
            $this->app->flush();
        }

        if ($this->databasePath !== null) {
            foreach ([
                $this->databasePath,
                $this->databasePath . '-journal',
                $this->databasePath . '-wal',
                $this->databasePath . '-shm',
            ] as $path) {
                if (is_file($path) && !is_link($path)) {
                    @unlink($path);
                }
            }
        }

        foreach ($this->originalEnvironment as $name => $values) {
            foreach (['server' => '_SERVER', 'env' => '_ENV'] as $key => $superglobal) {
                if ($values[$key] === null) {
                    unset($GLOBALS[$superglobal][$name]);
                } else {
                    $GLOBALS[$superglobal][$name] = $values[$key];
                }
            }

            if ($values['process'] === false) {
                putenv($name);
            } else {
                putenv($name . '=' . $values['process']);
            }
        }

        parent::tearDown();
    }

    public function test_footer_only_seed_is_idempotent_and_preserves_custom_destinations(): void
    {
        Settings::query()->create(['key' => 'instagram', 'value' => 'https://www.instagram.com/ownerchosen']);
        Settings::query()->create(['key' => 'facebook', 'value' => '']);
        Settings::query()->create(['key' => 'linkedin', 'value' => 'linkedin.com/company/agendaally']);
        Settings::query()->create(['key' => 'tiktok', 'value' => 'https://www.tiktok.com/@ownerchosen']);
        Settings::query()->create(['key' => 'customer_app_android', 'value' => 'Owner app configuration']);
        Settings::query()->create(['key' => 'description', 'value' => 'Owner-edited footer copy.']);
        $kernel = $this->app->make(ConsoleKernel::class);
        self::assertSame(Command::SUCCESS, $kernel->call('development:database-seed', ['--footer-only' => true]));
        self::assertSame('https://www.instagram.com/ownerchosen', Settings::where('key', 'instagram')->value('value'));
        self::assertSame('https://www.facebook.com/AgendaAlly/', Settings::where('key', 'facebook')->value('value'));
        self::assertSame('https://www.linkedin.com/company/agendaally', Settings::where('key', 'linkedin')->value('value'));
        self::assertSame('https://www.tiktok.com/@ownerchosen', Settings::where('key', 'tiktok')->value('value'));
        self::assertSame('Owner app configuration', Settings::where('key', 'customer_app_android')->value('value'));
        self::assertSame('Owner-edited footer copy.', Settings::where('key', 'description')->value('value'));
        $first = DB::table('settings')->orderBy('id')->get()->toJson();
        self::assertSame(Command::SUCCESS, $kernel->call('development:database-seed', ['--footer-only' => true]));
        self::assertSame($first, DB::table('settings')->orderBy('id')->get()->toJson());
        self::assertSame(0, DB::table('bookings')->count());
        self::assertSame(0, DB::table('transactions')->count());
        self::assertSame(0, DB::table('products')->count());
    }

    public function test_content_only_command_is_owned_idempotent_and_serves_native_content_routes(): void
    {
        $this->createPreviewAuthor();
        DB::table('payments')->insert([
            [
                'tag' => 'cash',
                'input' => 2,
                'active' => true,
                'sandbox' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tag' => 'stripe',
                'input' => 9,
                'active' => true,
                'sandbox' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        $paymentsBefore = $this->paymentRows();
        $untouchedTables = ['users', 'shops', 'products', 'stocks', 'orders', 'bookings', 'carts'];
        $untouchedBefore = $this->tableCounts($untouchedTables);

        $kernel = $this->app->make(ConsoleKernel::class);
        self::assertSame(
            Command::SUCCESS,
            $kernel->call('development:database-seed', ['--content-only' => true])
        );
        $firstCounts = $this->contentCounts();
        $this->assertPreviewContent();
        self::assertSame($paymentsBefore, $this->paymentRows(), 'Content-only must leave existing payment records unchanged.');
        self::assertSame(
            $untouchedBefore,
            $this->tableCounts($untouchedTables),
            'Content-only must leave existing marketplace and synthetic author rows unchanged.'
        );
        $this->assertNativePublicContentRoutes();

        self::assertSame(
            Command::SUCCESS,
            $kernel->call('development:database-seed', ['--content-only' => true])
        );
        self::assertSame($firstCounts, $this->contentCounts(), 'Content-only reruns must be idempotent.');
        self::assertSame($paymentsBefore, $this->paymentRows(), 'Content-only reruns must not add or update payment records.');
        self::assertSame(0, DB::table('shops')->count());
        self::assertSame(0, DB::table('orders')->count());
        self::assertSame(0, DB::table('bookings')->count());
        self::assertSame([], DB::select('PRAGMA foreign_key_check'));

        // An intentionally edited field and a blank user setting must survive.
        Settings::query()->where('key', 'description')->update(['value' => 'Owner-edited footer copy.']);
        Settings::query()->where('key', 'facebook')->update(['value' => '']);
        self::assertSame(
            Command::SUCCESS,
            $kernel->call('development:database-seed', ['--content-only' => true])
        );
        self::assertSame('Owner-edited footer copy.', Settings::query()->where('key', 'description')->value('value'));
        self::assertSame('', Settings::query()->where('key', 'facebook')->value('value'));
        self::assertSame($paymentsBefore, $this->paymentRows(), 'Content-only must preserve payment records after owner content edits.');
    }

    public function test_existing_owner_content_is_never_replaced_and_partial_content_is_completed(): void
    {
        $this->createPreviewAuthor();

        // Simulate owner material sharing the singleton/table identities. The
        // seed must preserve it rather than apply template-style replacements.
        $term = TermCondition::query()->create([]);
        $term->translations()->create([
            'locale' => 'en',
            'title' => 'Owner Terms',
            'description' => '<p>Owner-authored and intentionally retained.</p>',
        ]);
        $policy = PrivacyPolicy::query()->create([]);
        $policy->translations()->create([
            'locale' => 'en',
            'title' => 'Owner Privacy',
            'description' => '<p>Owner-authored privacy terms.</p>',
        ]);
        $page = Page::query()->create([
            'type' => Page::ABOUT,
            'active' => true,
            'img' => null,
            'bg_img' => null,
            'buttons' => [],
        ]);
        $page->translations()->create([
            'locale' => 'en',
            'title' => 'Owner About',
            'description' => '<p>Owner introduction.</p>',
        ]);
        $blog = Blog::query()->create([
            'uuid' => 'ccf7fda8-c323-4a24-90ce-ae5e00000011',
            'user_id' => User::query()->where('email', 'admin@agendaally.test')->value('id'),
            'type' => Blog::TYPES['blog'],
            'active' => true,
            'published_at' => now()->toDateString(),
            'img' => null,
        ]);
        $blog->translations()->create([
            'locale' => 'en',
            'title' => 'Owner article title',
            'short_desc' => 'Owner introduction.',
            'description' => '<p>Owner article content.</p>',
        ]);
        $faq = Faq::query()->create([
            'uuid' => 'bd4a92f2-00de-4ac3-877d-ae0a6b4a0011',
            'type' => 'general',
            'active' => false,
        ]);
        $faq->translations()->create([
            'locale' => 'en',
            'question' => 'Owner question',
            'answer' => 'Owner answer',
        ]);
        Settings::query()->create(['key' => 'instagram', 'value' => 'owner.example.test/account']);
        Settings::query()->create(['key' => 'phone', 'value' => '+237 677 000 000']);

        self::assertSame(
            Command::SUCCESS,
            $this->app->make(ConsoleKernel::class)->call('development:database-seed', [
                '--content-only' => true,
            ])
        );

        self::assertSame('<p>Owner-authored and intentionally retained.</p>', $term->fresh()->translation->description);
        self::assertSame('<p>Owner-authored privacy terms.</p>', $policy->fresh()->translation->description);
        self::assertSame('<p>Owner introduction.</p>', $page->fresh()->translation->description);
        self::assertSame('Owner article title', $blog->fresh()->translation->title);
        self::assertSame('Owner answer', $faq->fresh()->translation->answer);
        self::assertSame('owner.example.test/account', Settings::query()->where('key', 'instagram')->value('value'));
        self::assertSame('+237 677 000 000', Settings::query()->where('key', 'phone')->value('value'));

        // Partial records stay singular; other types/pages/articles are added.
        self::assertSame(1, TermCondition::query()->count());
        self::assertSame(1, PrivacyPolicy::query()->count());
        self::assertSame(1, Page::query()->where('type', Page::ABOUT)->count());
        self::assertSame(1, Blog::query()->where('uuid', 'ccf7fda8-c323-4a24-90ce-ae5e00000011')->count());
        self::assertSame(1, Faq::query()->where('uuid', 'bd4a92f2-00de-4ac3-877d-ae0a6b4a0011')->count());
        self::assertSame(3, Page::query()->whereIn('type', [
            Page::ABOUT,
            Page::ABOUT_SECOND,
            Page::ABOUT_THREE,
        ])->count());
        self::assertSame(3, Blog::query()->count());
        self::assertSame(4, Faq::query()->count());
    }

    public function test_content_only_rolls_back_partial_content_when_preview_author_is_missing(): void
    {
        $beforeCounts = $this->contentCounts();
        $paymentsBefore = $this->paymentRows();
        $output = new BufferedOutput();

        $exitCode = $this->app->make(ConsoleKernel::class)->call(
            'development:database-seed',
            ['--content-only' => true],
            $output
        );

        self::assertSame(Command::FAILURE, $exitCode);
        self::assertStringContainsString(
            'Run the owned development seed before --content-only.',
            $output->fetch(),
            'Missing blog ownership context must produce an explicit safe error.'
        );
        self::assertSame(
            $beforeCounts,
            $this->contentCounts(),
            'Failure after partial CMS seeding must roll back the translation/CMS/footer content.'
        );
        self::assertSame($paymentsBefore, $this->paymentRows(), 'Content-only failure must not touch payment records.');
        self::assertSame(0, DB::table('users')->count());
        self::assertSame(0, DB::connection()->transactionLevel());
    }

    private function createPreviewAuthor(): User
    {
        return User::factory()->create([
            'email' => 'admin@agendaally.test',
            'firstname' => 'Preview',
            'lastname' => 'Editor',
        ]);
    }

    /**
     * @return array<string, int>
     */
    private function contentCounts(): array
    {
        $tables = [
            'languages',
            'settings',
            'term_conditions',
            'term_condition_translations',
            'privacy_policies',
            'privacy_policy_translations',
            'pages',
            'page_translations',
            'faqs',
            'faq_translations',
            'blogs',
            'blog_translations',
            'payments',
        ];
        $counts = [];

        foreach ($tables as $table) {
            self::assertTrue(Schema::hasTable($table));
            $counts[$table] = DB::table($table)->count();
        }

        ksort($counts);

        return $counts;
    }

    /**
     * @param list<string> $tables
     * @return array<string, int>
     */
    private function tableCounts(array $tables): array
    {
        $counts = [];

        foreach ($tables as $table) {
            $counts[$table] = DB::table($table)->count();
        }

        ksort($counts);

        return $counts;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function paymentRows(): array
    {
        return DB::table('payments')
            ->orderBy('id')
            ->get(['id', 'tag', 'input', 'active', 'sandbox', 'created_at', 'updated_at'])
            ->map(fn ($payment) => (array) $payment)
            ->all();
    }

    private function assertPreviewContent(): void
    {
        self::assertSame(3, Page::query()->count());
        self::assertSame(4, Faq::query()->count());
        self::assertSame(3, Blog::query()->count());
        self::assertSame(1, TermCondition::query()->count());
        self::assertSame(1, PrivacyPolicy::query()->count());
        self::assertStringContainsString(
            'Draft for AgendaAlly owner and qualified legal review before production.',
            (string) TermCondition::query()->firstOrFail()->translation->description
        );
        self::assertStringContainsString(
            'Draft for AgendaAlly owner and qualified legal review before production.',
            (string) PrivacyPolicy::query()->firstOrFail()->translation->description
        );
        self::assertSame(0, DB::table('email_settings')->count());
        self::assertSame(0, DB::table('sms_gateways')->count());
        self::assertSame(0, DB::table('transactions')->count());
        self::assertSame(0, DB::table('payouts')->count());

        self::assertSame('', Settings::query()->where('key', 'phone')->value('value'));
        self::assertSame('', Settings::query()->where('key', 'address')->value('value'));
        self::assertSame('', Settings::query()->where('key', 'customer_app_ios')->value('value'));
        self::assertSame('', Settings::query()->where('key', 'customer_app_android')->value('value'));
        self::assertSame('', Settings::query()->where('key', 'instagram')->value('value'));
        self::assertSame('', Settings::query()->where('key', 'facebook')->value('value'));
        self::assertSame('', Settings::query()->where('key', 'twitter')->value('value'));
        self::assertSame(0, DB::table('shop_socials')->count());
        self::assertSame(0, DB::table('stories')->count());
    }

    private function assertNativePublicContentRoutes(): void
    {
        $kernel = $this->app->make(HttpKernel::class);
        $checks = [
            '/api/v1/rest/term?lang=en' => 'qualified legal review',
            '/api/v1/rest/policy?lang=en' => 'qualified legal review',
            '/api/v1/rest/pages/about?lang=en' => 'About AgendaAlly',
            '/api/v1/rest/faqs/paginate?perPage=20&lang=en' => 'service bookings work',
            '/api/v1/rest/blogs/paginate?perPage=20&lang=en' => 'local services',
            '/api/v1/rest/blogs/ccf7fda8-c323-4a24-90ce-ae5e00000011?lang=en' => 'local services',
        ];

        foreach ($checks as $path => $expectedText) {
            $request = Request::create($path, 'GET', [], [], [], [
                'HTTP_ACCEPT' => 'application/json',
                'REMOTE_ADDR' => '127.0.0.1',
                'SERVER_NAME' => 'localhost',
                'SERVER_PORT' => '80',
            ]);
            $response = $kernel->handle($request);

            try {
                self::assertSame(200, $response->getStatusCode(), "Native public content route {$path} should respond.");
                self::assertStringContainsString(
                    strtolower($expectedText),
                    strtolower((string) json_encode($response->getData(true), JSON_THROW_ON_ERROR)),
                    "Native public content route {$path} should return seeded content."
                );
            } finally {
                $kernel->terminate($request, $response);
                $this->app['session']->driver()->flush();
                \Illuminate\Support\Facades\Auth::forgetGuards();
            }
        }
    }

    /**
     * Set an isolated Laravel environment before bootstrapping service
     * providers. Test setup never reads developer or production credentials.
     *
     * @param array<string, string> $environment
     */
    private function setEnvironment(array $environment): void
    {
        foreach ($environment as $name => $value) {
            $this->originalEnvironment[$name] = [
                'server' => $_SERVER[$name] ?? null,
                'env' => $_ENV[$name] ?? null,
                'process' => getenv($name),
            ];

            $_SERVER[$name] = $value;
            $_ENV[$name] = $value;
            putenv($name . '=' . $value);
        }
    }
}