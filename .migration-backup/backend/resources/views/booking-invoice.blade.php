<!doctype html>
<html lang="{{ $lang }}">
<?php
/** @var \App\Models\Booking $model */

use App\Helpers\ResponseError;
use App\Models\Booking;
use App\Models\Transaction;
use App\Models\Translation;

$paymentMethod = $model?->transaction?->paymentSystem?->tag;
$transactionStatus = $model?->transaction?->status;

$keys = array_merge(
    ['online', 'offline_in', 'offline_out'],
    Transaction::STATUSES,
    Booking::STATUSES
);

if (!empty($paymentMethod)) {
    $keys[] = $paymentMethod;
}

if (!empty($transactionStatus)) {
    $keys[] = $transactionStatus;
}

$translations = Translation::where('locale', $lang)
    ->whereIn('key', array_values(array_unique($keys)))
    ->pluck('value', 'key')
    ->toArray();

$paymentMethodLabel = $translations[$paymentMethod] ?? $paymentMethod;
$transactionStatusLabel = $translations[$transactionStatus] ?? $transactionStatus;
$position = $model?->currency?->position;
$symbol = $model?->currency?->symbol;
$user = $model?->user;
$shop = $model?->shop;

$shopLocation = $model?->shopLocation;
if ($shopLocation) {
    $shopLocationCityCountry = collect([
        $shopLocation->city?->translation?->title,
        $shopLocation->country?->translation?->title,
    ])->filter()->implode(', ');
    $shopAddress = $shopLocation->address ?: $shopLocationCityCountry;
} else {
    $shopAddress = $shop?->translation?->address;
}

$clientAddress = data_get($model?->data, 'address');
$shopPhone = $shop?->phone;
// The shop has no dedicated email field; don't substitute a private owner account.
$shopEmail = null;
$bookingStatus = $translations[$model?->status] ?? $model?->status;
$bookingType = $translations[$model?->type] ?? $model?->type;
$genders = [
    1 => ResponseError::MALE,
    2 => ResponseError::FEMALE,
    3 => ResponseError::ALL_GENDER,
];

$formatDate = static function ($value, string $format): ?string {
    if (empty($value)) {
        return null;
    }

    $timestamp = strtotime((string) $value);

    return $timestamp === false ? null : date($format, $timestamp);
};

$formatAmount = static function ($amount) use ($position, $symbol): string {
    $number = number_format($amount ?? 0, 2);

    if ($position === 'before') {
        return trim((string) $symbol) . ' ' . $number;
    }

    if ($position === 'after') {
        return $number . ' ' . trim((string) $symbol);
    }

    return $number;
};

$makeServiceRow = static function ($service, $fallbackType) use ($translations, $genders, $formatDate, $lang): array {
    $genderKey = data_get($genders, $service->gender, 'all.gender');
    $type = $service->type ?? $fallbackType;

    return [
        'title' => $service->serviceMaster?->service?->translation?->title,
        'specialist' => $service->master?->full_name,
        'date' => $formatDate($service->start_date, 'D, d M Y'),
        'from' => $formatDate($service->start_date, 'g:i A'),
        'to' => $formatDate($service->end_date, 'g:i A'),
        'status' => $translations[$service->status] ?? $service->status,
        'type' => $translations[$type] ?? $type,
        'gender' => __("errors.$genderKey", locale: $lang),
        'discount' => $service->rate_discount,
        'gift_card' => $service->rate_gift_cart_price,
        'service_fee' => $service->rate_service_fee,
        'extra_price' => $service->rate_extra_price,
        'coupon_price' => $service->rate_coupon_price,
        'total_price' => $service->rate_total_price,
    ];
};

$services = [$makeServiceRow($model, $model?->type)];
foreach ($model?->children ?? [] as $child) {
    $services[] = $makeServiceRow($child, $model?->type);
}

$hasGiftCardPrice = collect($services)->contains(
    static fn(array $service): bool => $service['gift_card'] !== null && (float) $service['gift_card'] !== 0.0
);
$frontUrl = rtrim((string) config('app.front_url'), '/');
$frontHost = parse_url($frontUrl, PHP_URL_HOST);
$websiteUrl = filter_var($frontUrl, FILTER_VALIDATE_URL)
    && in_array(parse_url($frontUrl, PHP_URL_SCHEME), ['http', 'https'], true)
    && $frontHost
    && !in_array(strtolower($frontHost), ['localhost', '127.0.0.1', '::1'], true)
    ? $frontUrl
    : null;
$generatedDate = now()->format('d M Y');
$bookingDate = $formatDate($model?->start_date, 'd M Y');
$shopName = $shop?->translation?->title;
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('errors.' . ResponseError::INVOICE, locale: $lang) }} #{{ $model?->id }}</title>
    <style>
        @page { size: A4; margin: 13mm 14mm 15mm; }
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; }
        body {
            color: #25231f;
            background: #fff;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 9pt;
            line-height: 1.5;
        }
        .document { width: 100%; }
        .top-rule { height: 4px; background: #a87945; margin: 0 0 20px; }
        .header { width: 100%; border-collapse: collapse; margin-bottom: 23px; }
        .header td { vertical-align: top; padding: 0; }
        .platform-logo-carrier { display: inline-block; background: #f8f5f0; padding: 4px 6px; }
        .platform-logo { display: block; width: 185px; max-height: 58px; object-fit: contain; }
        .tagline { color: #746e65; font-size: 8pt; margin-top: 4px; letter-spacing: .15px; }
        .invoice-heading { text-align: right; }
        .eyebrow { color: #8e6940; font-size: 8pt; font-weight: 700; letter-spacing: 1.7px; text-transform: uppercase; }
        .invoice-number { color: #26231f; font-size: 21px; font-weight: 700; margin-top: 2px; }
        .invoice-meta { color: #716b62; font-size: 8pt; margin-top: 5px; }
        .parties { width: 100%; border-collapse: separate; border-spacing: 8px 0; margin: 0 0 21px; table-layout: fixed; }
        .parties td { width: 50%; vertical-align: top; background: #f7f5f1; border: 1px solid #e9e4dc; padding: 12px 13px; }
        .section-kicker { color: #8e6940; font-size: 7px; font-weight: 700; letter-spacing: 1.35px; text-transform: uppercase; margin: 0 0 7px; }
        .party-name { color: #26231f; font-size: 11px; font-weight: 700; margin-bottom: 3px; overflow-wrap: anywhere; }
        .party-line { color: #655f56; font-size: 8pt; overflow-wrap: anywhere; }
        .vendor-heading { display: table; width: 100%; margin-bottom: 4px; }
        .vendor-mark, .vendor-copy { display: table-cell; vertical-align: middle; }
        .vendor-mark { width: 34px; }
        .vendor-logo { display: block; max-width: 29px; max-height: 29px; object-fit: contain; }
        .vendor-placeholder { width: 27px; height: 27px; border: 1px solid #d4c9b9; color: #a87945; font-size: 6px; font-weight: 700; line-height: 25px; text-align: center; letter-spacing: .25px; }
        .section-title { color: #292621; font-size: 11px; font-weight: 700; margin: 0 0 9px; }
        .details, .pricing { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .details { margin-bottom: 19px; }
        .details thead th, .pricing thead th {
            background: #f1eee8;
            color: #746b5f;
            font-size: 7pt;
            font-weight: 700;
            letter-spacing: .55px;
            padding: 7px 6px;
            text-align: left;
            text-transform: uppercase;
            border-bottom: 1px solid #e1dbd1;
        }
        .details tbody td, .pricing tbody td {
            padding: 8px 6px;
            border-bottom: 1px solid #ece8e2;
            vertical-align: top;
            overflow-wrap: anywhere;
            word-wrap: break-word;
        }
        .details tbody tr, .pricing tbody tr { page-break-inside: avoid; }
        .details .service-col { width: 30%; }
        .details .schedule-col { width: 27%; }
        .details .place-col { width: 21%; }
        .details .status-col { width: 22%; }
        .service-name { color: #28251f; font-size: 9pt; font-weight: 700; }
        .subline { display: block; color: #655f56; font-size: 8pt; margin-top: 2px; }
        .status { display: inline-block; color: #65563f; font-size: 8pt; font-weight: 700; background: #eee8dd; padding: 3px 6px; }
        .pricing { margin-bottom: 17px; }
        .pricing th, .pricing td { text-align: right !important; }
        .pricing th:first-child, .pricing td:first-child { text-align: left !important; }
        .pricing .amount-label { color: #70695f; font-weight: 500; }
        .pricing .total-cell { color: #25231f; font-size: 10pt; font-weight: 700; }
        .payment-panel { width: 100%; border: 1px solid #e8e2d9; background: #fbfaf8; border-collapse: collapse; margin: 5px 0 20px; page-break-inside: avoid; }
        .payment-panel td { width: 33.333%; padding: 10px 12px; vertical-align: top; }
        .payment-label { display: block; color: #655f56; font-size: 7pt; font-weight: 700; letter-spacing: .8px; text-transform: uppercase; margin-bottom: 3px; }
        .payment-value { color: #322f2a; font-size: 8pt; font-weight: 600; overflow-wrap: anywhere; }
        .footer { border-top: 1px solid #e6e0d7; padding-top: 10px; margin-top: 18px; color: #655f56; font-size: 7pt; page-break-inside: avoid; }
        .footer-row { width: 100%; border-collapse: collapse; }
        .footer-row td { padding: 0; vertical-align: top; }
        .footer-brand { color: #413b33; font-size: 8px; font-weight: 700; }
        .footer-tagline { margin-top: 2px; }
        .footer-links { text-align: right; }
        .footer a { color: #746042; text-decoration: none; }
        .copyright { margin-top: 7px; border-top: 1px solid #eeeae4; padding-top: 6px; color: #928a7e; }
        .muted { color: #817a70; }
        @media print {
            .top-rule { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .parties td, .details thead th, .pricing thead th, .status, .payment-panel { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
<main class="document">
    <div class="top-rule"></div>
    <table class="header" role="presentation">
        <tr>
            <td>
                @if(!empty($platformLogo))
                    <div class="platform-logo-carrier">
                        <img class="platform-logo" src="{{ $platformLogo }}" alt="AgendaAlly">
                    </div>
                @endif
            </td>
            <td class="invoice-heading">
                <div class="eyebrow">{{ __('errors.' . ResponseError::INVOICE, locale: $lang) }}</div>
                <div class="invoice-number">#{{ $model?->id }}</div>
                <div class="invoice-meta">
                    Issued {{ $generatedDate }}
                    @if($bookingDate)
                        <br>Booking date {{ $bookingDate }}
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <table class="parties">
        <tr>
            <td>
                <div class="section-kicker">Business</div>
                <div class="vendor-heading">
                    <div class="vendor-mark">
                        @if(!empty($shopLogo))
                            <img class="vendor-logo" src="{{ $shopLogo }}" alt="">
                        @else
                            <div class="vendor-placeholder">SHOP</div>
                        @endif
                    </div>
                    <div class="vendor-copy">
                        <div class="party-name">{{ $shopName ?: __('errors.' . ResponseError::SHOP, locale: $lang) }}</div>
                    </div>
                </div>
                @if(!empty($shopAddress))
                    <div class="party-line">{{ $shopAddress }}</div>
                @endif
                @if(!empty($shopPhone))
                    <div class="party-line">{{ $shopPhone }}</div>
                @endif
                @if(!empty($shopEmail))
                    <div class="party-line">{{ $shopEmail }}</div>
                @endif
            </td>
            <td>
                <div class="section-kicker">{{ __('errors.' . ResponseError::CLIENT, locale: $lang) }}</div>
                @if(!empty($user?->full_name))
                    <div class="party-name">{{ $user->full_name }}</div>
                @endif
                @if(!empty($user?->email))
                    <div class="party-line">{{ $user->email }}</div>
                @endif
                @if(!empty($user?->phone))
                    <div class="party-line">+{{ ltrim((string) $user->phone, '+') }}</div>
                @endif
                @if(!empty($clientAddress))
                    <div class="party-line">{{ $clientAddress }}</div>
                @endif
            </td>
        </tr>
    </table>

    <section>
        <h2 class="section-title">Service details</h2>
        <table class="details">
            <thead>
            <tr>
                <th class="service-col">{{ __('errors.' . ResponseError::SERVICE_NAME, locale: $lang) }}</th>
                <th class="schedule-col">Date &amp; time</th>
                <th class="place-col">{{ __('errors.' . ResponseError::PLACE, locale: $lang) }}</th>
                <th class="status-col">{{ __('errors.' . ResponseError::STATUS, locale: $lang) }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($services as $service)
                <tr>
                    <td>
                        <span class="service-name">{{ $service['title'] ?: __('errors.' . ResponseError::SERVICE_NAME, locale: $lang) }}</span>
                        @if(!empty($service['specialist']))
                            <span class="subline">Specialist: {{ $service['specialist'] }}</span>
                        @endif
                        @if(!empty($service['gender']))
                            <span class="subline">{{ $service['gender'] }}</span>
                        @endif
                    </td>
                    <td>
                        {{ $service['date'] ?: '—' }}
                        @if($service['from'] || $service['to'])
                            <span class="subline">{{ $service['from'] ?: '—' }} – {{ $service['to'] ?: '—' }}</span>
                        @endif
                    </td>
                    <td>
                        {{ $service['type'] ?: '—' }}
                        @if(!empty($shopAddress))
                            <span class="subline">{{ $shopAddress }}</span>
                        @endif
                    </td>
                    <td><span class="status">{{ $service['status'] ?: '—' }}</span></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>

    <section>
        <h2 class="section-title">Pricing summary</h2>
        <table class="pricing">
            <thead>
            <tr>
                <th>{{ __('errors.' . ResponseError::SERVICE_NAME, locale: $lang) }}</th>
                <th>{{ __('errors.' . ResponseError::DISCOUNT, locale: $lang) }}</th>
                <th>{{ __('errors.' . ResponseError::SERVICE_FEE, locale: $lang) }}</th>
                <th>{{ __('errors.' . ResponseError::EXTRA_PRICE, locale: $lang) }}</th>
                <th>{{ __('errors.' . ResponseError::COUPON, locale: $lang) }}</th>
                @if($hasGiftCardPrice)
                    <th>Gift card</th>
                @endif
                <th>{{ __('errors.' . ResponseError::TOTAL_PRICE, locale: $lang) }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($services as $index => $service)
                <tr>
                    <td class="amount-label">{{ $service['title'] ?: __('errors.' . ResponseError::SERVICE_NAME, locale: $lang) }}</td>
                    <td>{{ $formatAmount($service['discount']) }}</td>
                    <td>{{ $formatAmount($service['service_fee']) }}</td>
                    <td>{{ $formatAmount($service['extra_price']) }}</td>
                    <td>{{ $formatAmount($service['coupon_price']) }}</td>
                    @if($hasGiftCardPrice)
                        <td>{{ $formatAmount($service['gift_card']) }}</td>
                    @endif
                    <td class="total-cell">{{ $formatAmount($service['total_price']) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>

    <section>
        <h2 class="section-title">Payment information</h2>
        <table class="payment-panel">
            <tr>
                <td>
                    <span class="payment-label">{{ __('errors.' . ResponseError::PAYMENT_TYPE, locale: $lang) }}</span>
                    <span class="payment-value">{{ $paymentMethodLabel ?: '—' }}</span>
                </td>
                <td>
                    <span class="payment-label">{{ __('errors.' . ResponseError::TRANSACTION_STATUS, locale: $lang) }}</span>
                    <span class="payment-value">{{ $transactionStatusLabel ?: '—' }}</span>
                </td>
                <td>
                    <span class="payment-label">Booking status</span>
                    <span class="payment-value">{{ $bookingStatus ?: '—' }}</span>
                </td>
            </tr>
        </table>
    </section>

    <footer class="footer">
        <table class="footer-row" role="presentation">
            <tr>
                <td>
                    <div class="footer-brand">AgendaAlly</div>
                    <div class="footer-tagline">Book local expertise. Shop local businesses.</div>
                </td>
                <td class="footer-links">
                    @if($websiteUrl)
                        <a href="{{ $websiteUrl }}">Website</a>
                        &nbsp;·&nbsp;
                        <a href="{{ $websiteUrl }}/terms">Terms</a>
                        &nbsp;·&nbsp;
                        <a href="{{ $websiteUrl }}/privacy">Privacy</a>
                    @endif
                </td>
            </tr>
        </table>
        <div class="copyright">© {{ now()->year }} AgendaAlly. All rights reserved.</div>
    </footer>
</main>
</body>
</html>