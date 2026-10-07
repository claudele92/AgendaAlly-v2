<?php

return [
    // Deliberately disabled by default. Enabling this uses the platform's
    // configured Laravel mail transport and requires the native UI origin.
    'invitation_email_enabled' => (bool) env('DELIVERY_DRIVER_INVITATION_EMAIL_ENABLED', false),
    'native_public_origin' => env('NATIVE_PUBLIC_ORIGIN', env('VENDOR_ADMIN_URL', env('ADMIN_URL'))),
];