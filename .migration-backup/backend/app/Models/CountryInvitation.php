<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\CountryInvitation
 *
 * Mirrors App\Models\Invitation for country-scoped staff — see the
 * migration for why this is a separate table.
 *
 * @property int $id
 * @property int $country_id
 * @property int $user_id
 * @property int $created_by
 * @property int|null $country_role_id
 * @property int $status
 */
class CountryInvitation extends Model
{
    protected $guarded = ['id'];

    const NEW      = 1;
    const ACCEPTED = 2;
    const REJECTED = 4;
    const CANCELED = 5;

    const STATUS = [
        'new'      => self::NEW,
        'accepted' => self::ACCEPTED,
        'rejected' => self::REJECTED,
        'canceled' => self::CANCELED,
    ];

    const STATUS_BY = [
        self::NEW      => 'new',
        self::ACCEPTED => 'accepted',
        self::REJECTED => 'rejected',
        self::CANCELED => 'canceled',
    ];

    public static function getStatusKey($value)
    {
        foreach (self::STATUS as $index => $status) {
            if ($value == $status) {
                return $index;
            }
        }
    }

    public function scopeFilter(Builder $query, array $filter): Builder
    {
        return $query
            ->when(data_get($filter, 'status'), fn ($q, $status) => $q->where('status', $status))
            ->when(data_get($filter, 'user_id'), fn ($q, $userId) => $q->where('user_id', $userId))
            ->when(data_get($filter, 'created_by'), fn ($q, $id) => $q->where('created_by', $id))
            ->when(data_get($filter, 'country_id'), fn ($q, $countryId) => $q->where('country_id', $countryId));
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function countryRole(): BelongsTo
    {
        return $this->belongsTo(CountryRole::class);
    }
}
