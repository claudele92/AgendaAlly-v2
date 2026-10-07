<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\ShopPermission
 *
 * @property int $id
 * @property string $key
 * @property string $group
 * @property string $label
 */
class ShopPermission extends Model
{
    protected $guarded = ['id'];

    public function shopRoles(): BelongsToMany
    {
        return $this->belongsToMany(ShopRole::class, 'shop_role_permissions');
    }
}
