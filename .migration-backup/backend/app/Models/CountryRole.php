<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\CountryRole
 *
 * A country admin's own named role for their country's staff. Independent
 * of Spatie's role/permission tables — same bespoke pattern as ShopRole.
 *
 * A null country_id marks a platform-wide role (e.g. "Main Accountant") —
 * not scoped to any single country's staff.
 *
 * @property int $id
 * @property int|null $country_id
 * @property string $name
 */
class CountryRole extends Model
{
    protected $guarded = ['id'];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(CountryPermission::class, 'country_role_permissions');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(CountryInvitation::class);
    }

    public function hasPermission(string $key): bool
    {
        return $this->permissions->contains('key', $key);
    }
}
