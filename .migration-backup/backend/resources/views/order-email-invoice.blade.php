<!DOCTYPE html>
<html lang="en">
<?php
/** @var App\Models\Order $order */
/** @var string $logo */
/** @var string $lang */

use App\Models\Order;
use App\Models\Settings;
use App\Models\Transaction;
use App\Models\Translation;
use App\Support\EmailPresentation;

$socials = Settings::whereIn('key', ['instagram', 'facebook', 'twitter', 'linkedin'])
    ->pluck('value', 'key');
$presentation = EmailPresentation::prepare($mailer ?? null, ['logo' => $logo] + $socials->all());

$keys = [
    'order.summary',
    'order.number',
    'item.name',
    'quantity',
    'price',
    'title',
    'payment.method',
    'shop',
    'delivery.address',
    'delivery.time',
    'subtotal',
    'tax',
    'total.price',
    'order.status',
    'coupon',
    'discount',
    'delivery.fee',
    'zipcode',
    'street_house_number',
    $order?->status
];

$paymentMethod = $order?->transaction?->paymentSystem?->tag;
$trxStatus = $order?->transaction?->status ?? Transaction::STATUS_PROGRESS;

if (!empty($paymentMethod)) {
    $keys[] = $paymentMethod;
}

if (!empty($trxStatus)) {
    $keys[] = $trxStatus;
}

$translations     = Translation::where('locale', $lang)->whereIn('key', $keys)->get();

$orderSummary     = $translations->where('key', 'order.summary')   ->first()?->value ?? 'order.summary';
$orderNumber      = $translations->where('key', 'order.number')    ->first()?->value ?? 'order.number';
$itemName         = $translations->where('key', 'item.name')       ->first()?->value ?? 'item.name';
$quantity         = $translations->where('key', 'quantity')        ->first()?->value ?? 'quantity';
$price            = $translations->where('key', 'price')           ->first()?->value ?? 'price';
$appName          = $translations->where('key', 'title')           ->first()?->value ?? env('APP_NAME');
$paymentTitle     = $translations->where('key', 'payment.method')  ->first()?->value ?? 'payment.method';
$shop             = $translations->where('key', 'shop')            ->first()?->value ?? 'shop';
$deliveryAddress  = $translations->where('key', 'delivery.address')->first()?->value ?? 'delivery.address';
$orderStatus      = $translations->where('key', 'order.status')    ->first()?->value ?? 'order.status';
$deliveryTitle    = $translations->where('key', 'delivery.time')   ->first()?->value ?? 'delivery.time';
$subtotal         = $translations->where('key', 'subtotal')        ->first()?->value ?? 'subtotal';
$taxTitle         = $translations->where('key', 'tax')             ->first()?->value ?? 'tax';
$totalTitle       = $translations->where('key', 'total.price')     ->first()?->value ?? 'total.price';
$paymentMethod    = $translations->where('key', $paymentMethod)    ->first()?->value ?? $paymentMethod;
$trxStatus        = $translations->where('key', $trxStatus)        ->first()?->value ?? $trxStatus;
$couponTitle      = $translations->where('key', 'coupon')          ->first()?->value ?? 'coupon';
$discountTitle    = $translations->where('key', 'discount')        ->first()?->value ?? 'discount';
$deliveryFeeTitle = $translations->where('key', 'delivery.fee')    ->first()?->value ?? 'delivery.fee';
$zipcode          = $translations->where('key', 'zipcode')         ->first()?->value ?? 'zipcode';
$house            = $translations->where('key', 'street_house_number')->first()?->value ?? 'street_house_number';

$userName        = $order->username ?? "{$order->user?->firstname} {$order->user?->lastname}";
$userPhone       = $order->phone ?? $order->user?->phone;
$position        = $order?->currency?->position;
$symbol          = $order?->currency?->symbol;
$status          = $translations->where('key', $order?->status)->first()?->value ?? $order?->status;
$shopPhone       = $order->shop?->phone ?? $order->shop?->seller?->phone;
$shopTitle       = $order->shop?->translation?->title;
$shopAddress     = $order->shop?->translation?->address;
$deliveryTime    = date('m/d/Y', strtotime("$order->delivery_date $order->delivery_time"));
$createdAt       = date('m/d/Y', strtotime($order->created_at));
$address         = data_get($order, 'address.address');
$myAddress       = $order->myAddress?->address;

if ($myAddress) {
    $location = $order?->myAddress?->location;
    $address = (is_array($myAddress) ? $myAddress['address'] ?? '' : (string)$myAddress) ?? data_get($location, 'address', '');
    $address .= ", $zipcode: {$order?->myAddress?->zipcode}, $house: {$order?->myAddress?->street_house_number}, {$order?->myAddress?->additional_details}";
}

if ($order->delivery_type !== Order::DELIVERY) {
    $address = $shopAddress;
}

// This is the order-email summary, not the printable invoice template.
if ($order->delivery_type === Order::PICKUP) {
    $deliveryAddress = 'Pickup at Shop';
    $deliveryTitle = 'Pickup timing';
    $address = data_get($order->pickup, 'address') ?: $shopAddress;
    $deliveryTime = 'As soon as your order is ready';
    $pickupStart = data_get($order->pickup, 'window_start');
    $pickupEnd = data_get($order->pickup, 'window_end');
    $pickupTimezone = data_get($order->pickup, 'timezone');
    if ($pickupStart && $pickupEnd && $pickupTimezone) {
        $deliveryTime = \Carbon\CarbonImmutable::parse($pickupStart)->setTimezone($pickupTimezone)->format('D, M j Y H:i')
            . ' – ' . \Carbon\CarbonImmutable::parse($pickupEnd)->setTimezone($pickupTimezone)->format('H:i')
            . ' (' . $pickupTimezone . ')';
    }
}

?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f8f8;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            word-wrap: break-word;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .header {
            text-align: center;
        }

        .header img {
            width: 150px;
        }

        .content {
            padding: 20px;
        }

        .order-summary {
            margin-top: 20px;
        }

        .order-summary h3 {
            margin-top: 0;
            border-bottom: 1px solid #ccc;
            padding-bottom: 20px;
        }

        .order-details {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .order-details th, .order-details td {
            padding: 10px;
            border: 1px solid #ddd;
        }

        .order-details th {
            background-color: #f1f1f1;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
        }

        .social-icon {
            display: inline-block;
            width: 32px;
            height: 32px;
            line-height: 32px;
            border-radius: 50%;
            background-color: #3f3f46;
            color: #ffffff;
            text-align: center;
            text-decoration: none;
            font-size: 12px;
            font-weight: 700;
            margin: 0 4px;
        }

        .order-number {
            display: table;
            table-layout: fixed;
            width: 100%;
        }

        .order-number-date {
            width: 50%;
            display: table-cell;
        }

        .order-number-phone {
            padding-left: 10px;
        }

        .blue-color {
            color: #065c94;
        }

        @media (max-width: 600px) {
            .container {
                margin: 10px;
                padding: 10px;
            }

            .order-details th, .order-details td {
                padding: 5px;
            }
        }
    </style>
</head>
<body>
<div class="container" style="max-width:600px; word-wrap:break-word; overflow-wrap:anywhere; word-break:break-word;">
    <div class="header">
        @if($presentation['logo'])
            <img src="{{ $presentation['logo']['src'] }}" alt="{{ $appName }}" width="{{ $presentation['logo']['width'] }}" height="{{ $presentation['logo']['height'] }}" style="display:block; border:0;">
        @else
            <strong>{{ $appName }}</strong>
        @endif
        <h2 class="blue-color">{{ $title }}</h2>
    </div>
    <div class="content">
        <div class="order-summary">
            <h3>{{ $orderSummary }}</h3>
            <strong>{{ $userName }}</strong>
            <div class="order-number">
                <div class="order-number-date">{{ $createdAt }}</div>
                <div class="order-number-phone">{{ $orderNumber }} #{{$order->id}}</div>
            </div>
            <p><strong>{{ $paymentTitle }}*</strong><br>{{ "$paymentMethod $trxStatus" }}</p>
            <div class="order-number">
                <div class="order-number-date"><strong>{{ $shop }}</strong><br> {{ "$shopTitle $shopAddress" }}</div>
                <div class="order-number-phone">{{ $shopPhone }}</div>
            </div>
            <p><strong>{{ $deliveryAddress }}</strong><br>{{ $address }}</p>
            <p><strong>{{ $orderStatus }}</strong><br>{{ $status }}</p>
            <p><strong>{{ $deliveryTitle }}</strong><br>{{ $deliveryTime }}</p>
            <table class="order-details" style="width:100%; table-layout:fixed; word-wrap:break-word; overflow-wrap:anywhere; word-break:break-word;">
                <thead>
                <tr>
                    <th>{{ $itemName }}</th>
                    <th>{{ $quantity }}</th>
                    <th>{{ $price }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($order->orderDetails as $orderDetail)
                    @php
                        $addons = '';
                        $orderDetail->children?->transform(function ($i) use(&$addons, $order, $symbol) {
                            $addons .= $i?->stock?->product?->translation?->title . " x $i?->quantity $symbol$i?->rate_total_price, ";
                        });
                        $addons = substr($addons, 0, -2);

                        $extras = '';

                        foreach($orderDetail->stock->stockExtras ?? (object)[] as $extra) {

                            if(!$extra?->value?->value) {
                                continue;
                            }

                            $extras .= ',' . $extra?->value?->value;
                        }

                    @endphp
                    <tr>
                        <td class="blue-color">{{ $orderDetail->stock?->product?->translation?->title }}{{ $extras }}{{ $addons }}</td>
                        <td>{{ $orderDetail->quantity }}</td>
                        <td>
                            {{ $position === 'before' ? $symbol : '' }}
                            {{ number_format($orderDetail->rate_total_price, 2)  }}
                            {{ $position === 'after' ? $symbol : '' }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <p>
                <strong>
                    {{ $subtotal }}:
                    {{ $position === 'before' ? $symbol : '' }}
                    {{ number_format($order->rate_total_price + $order->rate_total_discount - $order->rate_tax - $order->rate_delivery_fee + $order->coupon?->price, 2)  }}
                    {{ $position === 'after' ? $symbol : '' }}
                </strong>
            </p>
            <p>
                <strong>
                    {{ $taxTitle }}:
                    {{ $position === 'before' ? $symbol : '' }}
                    {{ number_format($order->rate_tax, 2)  }}
                    {{ $position === 'after' ? $symbol : '' }}
                </strong>
            </p>
            <p>
                <strong>
                    {{ $deliveryFeeTitle }}:
                    {{ $position === 'before' ? $symbol : '' }}
                    {{ number_format($order->rate_delivery_fee, 2)  }}
                    {{ $position === 'after' ? $symbol : '' }}
                </strong>
            </p>
            @if($order->rate_coupon_sum_price)
                <p>
                    <strong>
                        {{ $couponTitle }}:
                        {{ $position === 'before' ? $symbol : '' }}
                        <span style="color: red">{{ number_format($order->rate_coupon_sum_price, 2)  }}</span>
                        {{ $position === 'after' ? $symbol : '' }}
                    </strong>
                </p>
            @endif
            @if($order->rate_total_discount)
                <p>
                    <strong>
                        {{ $discountTitle }}:
                        {{ $position === 'before' ? $symbol : '' }}
                        <span style="color: red">- {{ number_format($order->rate_total_discount, 2)  }}</span>
                        {{ $position === 'after' ? $symbol : '' }}
                    </strong>
                </p>
            @endif
            <p>
                <strong>
                    {{ $totalTitle }}:
                    {{ $position === 'before' ? $symbol : '' }}
                    {{ number_format($order->rate_total_price, 2)  }}
                    {{ $position === 'after' ? $symbol : '' }}
                </strong>
            </p>
        </div>
    </div>
    <div class="footer">
        @if(count($presentation['socials']))
            <table role="presentation" cellpadding="0" cellspacing="0" align="center">
                <tr>
                    @foreach($presentation['socials'] as $social)
                        <td style="padding:0 8px;">
                            <a href="{{ $social['url'] }}" aria-label="{{ $social['label'] }}" style="color:#111827; font-size:12px;">
                                @if($social['icon'])
                                    <img src="{{ $social['icon']['src'] }}" alt="{{ $social['label'] }}" width="32" height="32" style="display:block; border:0;">
                                @else
                                    {{ $social['label'] }}
                                @endif
                            </a>
                        </td>
                    @endforeach
                </tr>
            </table>
        @endif
        <p>&copy; {{ date('Y') }} {{ $appName }}</p>
    </div>
</div>
</body>
</html>
