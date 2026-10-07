<?php
declare(strict_types=1);

namespace App\Models\Concerns;

use App\Services\PaymentAccounting\NativeFinancialBoundary;

/** Operational edits are not authority to change retained financial evidence. */
trait ProtectsCanonicalPayment
{
    public static function bootProtectsCanonicalPayment(): void
    {
        static::saving(fn ($model) => (new NativeFinancialBoundary)->saving($model));
    }
}