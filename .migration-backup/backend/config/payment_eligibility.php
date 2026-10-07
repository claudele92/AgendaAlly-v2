<?php

// Code-level capability declarations only, NOT merchant/legal availability.
// Unknown currency lists deliberately fail closed. No provider is activated here.
return [
    // Preserve accepted cash/wallet behaviour until an explicit business-policy decision.
    'internal_country_policy' => 'legacy_compatibility',
    // Capability only; country assignments, provider/environment policy and
    // registered merchant configuration remain separate, mandatory gates.
    'vendor_direct_currencies' => [
        'mtn' => ['XAF', 'XOF', 'GHS', 'UGX', 'ZMW', 'RWF', 'SZL'],
    ],
    'vendor_direct_sandbox_currencies' => ['mtn' => ['EUR']],
    'currencies' => [
        'stripe' => ['USD', 'CAD', 'EUR', 'GBP'], // Narrow two-decimal intent foundation.
        'paypal' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP',
            'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'MYR', 'NOK', 'NZD', 'PHP',
            'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'], // PayPalVerification::amount.
        'paystack' => null,
        'flutter-wave' => null,
    ],
];