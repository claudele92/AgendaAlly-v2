<?php

declare(strict_types=1);

namespace Database\Seeders\Fixtures;

use App\Models\Payment;

/**
 * A deliberately synthetic policy matrix for exercising country-aware
 * payment assignment. Membership is not evidence of provider availability.
 */
final class DevelopmentCountryPaymentPolicy
{
    /**
     * @return array<string, list<string>>
     */
    public static function assignments(): array
    {
        return [
            'cm' => [
                Payment::TAG_CASH,
                Payment::TAG_WALLET,
                Payment::TAG_MTN,
                Payment::TAG_ORANGE,
                Payment::TAG_PAY_STACK,
                Payment::TAG_FLUTTER_WAVE,
            ],
            'ng' => [
                Payment::TAG_CASH,
                Payment::TAG_WALLET,
                Payment::TAG_MTN,
                Payment::TAG_PAY_STACK,
                Payment::TAG_FLUTTER_WAVE,
                Payment::TAG_STRIPE,
                Payment::TAG_PAY_PAL,
            ],
            'gh' => [
                Payment::TAG_CASH,
                Payment::TAG_WALLET,
                Payment::TAG_MTN,
                Payment::TAG_PAY_STACK,
                Payment::TAG_FLUTTER_WAVE,
                Payment::TAG_PAY_PAL,
            ],
            'bf' => [
                Payment::TAG_CASH,
                Payment::TAG_WALLET,
                Payment::TAG_MTN,
                Payment::TAG_ORANGE,
                Payment::TAG_FLUTTER_WAVE,
                Payment::TAG_PAY_STACK,
            ],
        ];
    }
}