<?php
declare(strict_types=1);

use App\Console\Commands\DevelopmentDatabaseGuard;
use App\Models\Booking;
use App\Repositories\OrderRepository\OrderRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;

// Read existing local bookings only. QA document files stay in the private .local tree.
$base = dirname(__DIR__, 2) . '/.migration-backup/backend';
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
DevelopmentDatabaseGuard::requireOptIn((string) config('app.env'), env('AGENDAALLY_DEVELOPMENT_DATABASE'), env('DEVELOPMENT_MODE'));
$manifest = DevelopmentDatabaseGuard::reviewedManifest($base);
$path = DevelopmentDatabaseGuard::resolveSqlitePath($base, (string) config('database.default'), (array) config('database.connections.sqlite'));
if (!DevelopmentDatabaseGuard::ownsDatabase($path, $base, $manifest)) throw new RuntimeException('Owned local database required.');
$out = dirname(__DIR__, 2) . '/.local/qa/invoice-footer-payment';
if (!is_dir($out)) mkdir($out, 0700, true);
$original = shell_exec('git -C ' . escapeshellarg(dirname(__DIR__, 2)) . ' show HEAD:.migration-backup/backend/resources/views/booking-invoice.blade.php');
if (!$original) throw new RuntimeException('Cannot inspect original invoice template.');
$repository = new OrderRepository();
$logoMethod = new ReflectionMethod($repository, 'bookingShopLogoDataUri');
if ($logoMethod->invoke($repository, null) !== null
    || $logoMethod->invoke($repository, 'images/../../.env') !== null
    || $logoMethod->invoke($repository, 'images/missing.png') !== null) {
    throw new RuntimeException('Logo boundary did not fail closed.');
}
$before = hash('sha256', DB::table('bookings')->orderBy('id')->get()->toJson()
    . DB::table('transactions')->orderBy('id')->get()->toJson());
$bookings = Booking::with(['master', 'user', 'currency', 'extraTimes', 'transactions.paymentSystem',
    'transactions.children', 'shop.translation', 'shopLocation.city.translation', 'shopLocation.country.translation',
    'serviceMaster.service.translation', 'extras.translation', 'children.master', 'children.extraTimes',
    'children.serviceMaster.service.translation', 'children.extras.translation'])->whereNull('parent_id')->get();
if ($bookings->isEmpty()) throw new RuntimeException('No existing root booking is available; do not seed one for verification.');
$results = [];
foreach ($bookings as $booking) {
    $shopLogo = $logoMethod->invoke($repository, $booking->shop?->logo_img);
    $vars = ['model' => $booking, 'lang' => 'en', 'title' => '', 'logo' => '', 'shopLogo' => $shopLogo];
    $oldHtml = Blade::render($original, $vars);
    $newHtml = view('booking-invoice', $vars)->render();
    foreach ([$booking, ...$booking->children] as $service) {
        foreach (['discount', 'service_fee', 'extra_price', 'coupon_price', 'total_price'] as $field) {
            $amount = number_format($service->{"rate_$field"} ?? 0, 2);
            if (!str_contains($oldHtml, $amount) || !str_contains($newHtml, $amount)) {
                throw new RuntimeException("Original authoritative $field amount missing from a document.");
            }
        }
    }
    if (str_contains($newHtml, '>Master<') || !str_contains($newHtml, 'Specialist')) throw new RuntimeException('Presentation terminology regression.');
    if (!str_contains($newHtml, 'Agenda') || !str_contains($newHtml, "#{$booking->id}")) throw new RuntimeException('Invoice identity missing.');
    $response = $repository->bookingExportPDF($booking->id);
    if (is_array($response) || !str_starts_with($response->getContent(), '%PDF-')) throw new RuntimeException('Native PDF generation failed.');
    file_put_contents("$out/booking-{$booking->id}.pdf", $response->getContent());
    file_put_contents("$out/booking-{$booking->id}.html", $newHtml);
    $fallback = view('booking-invoice', array_replace($vars, ['shopLogo' => null]))->render();
    if (!str_contains($fallback, 'vendor-placeholder')) throw new RuntimeException('Neutral logo fallback missing.');
    Pdf::loadHTML($fallback)->output(); // Exercise actual DomPDF fallback, without changing the booking/shop.
    $results[] = ['booking_id' => $booking->id, 'local_vendor_logo' => $shopLogo !== null,
        'authoritative_values_preserved' => true, 'pdf_generated' => true, 'fallback_rendered' => true];
}
$after = hash('sha256', DB::table('bookings')->orderBy('id')->get()->toJson()
    . DB::table('transactions')->orderBy('id')->get()->toJson());
if ($after !== $before) throw new RuntimeException('Rendering changed an existing booking or transaction.');
echo json_encode(['booking_transaction_rows_unchanged' => true, 'documents' => $results], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n";