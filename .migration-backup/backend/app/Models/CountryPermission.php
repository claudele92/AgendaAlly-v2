<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\CountryPermission
 *
 * @property int $id
 * @property string $key
 * @property string $group
 * @property string $label
 */
class CountryPermission extends Model
{
    protected $guarded = ['id'];

    public function countryRoles(): BelongsToMany
    {
        return $this->belongsToMany(CountryRole::class, 'country_role_permissions');
    }
}
