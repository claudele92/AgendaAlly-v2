<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\ShopRole
 *
 * A seller's own named role for their shop's administrative staff (e.g.
 * "Front Desk", "Accountant"). Independent of Spatie's role/permission
 * tables — isolation between shops is structural (the shop_id column),
 * not enforced via Spatie Teams.
 *
 * @property int $id
 * @property int $shop_id
 * @property string $name
 */
class ShopRole extends Model
{
    protected $guarded = ['id'];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(ShopPermission::class, 'shop_role_permissions');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function hasPermission(string $key): bool
    {
        return $this->permissions->contains('key', $key);
    }
}
