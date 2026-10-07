<?php
declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

final class SellerBookingClientIdentityMatcher
{
    /**
     * Contact fields may independently match a registered account and a
     * local client. Never select one silently when those matches differ.
     */
    public static function singleOrConflict(Collection $matches): ?array
    {
        $matches = $matches
            ->unique(fn (array $match) => data_get($match, 'resource.kind') . ':' . data_get($match, 'resource.id'))
            ->values();

        if ($matches->count() > 1) {
            throw ValidationException::withMessages([
                'phone' => ['Phone and email match different authorized clients. Search and select the intended client, or correct the contact details.'],
            ]);
        }

        return $matches->first();
    }
}