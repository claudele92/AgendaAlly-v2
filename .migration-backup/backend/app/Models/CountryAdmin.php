<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\CountryAdmin
 *
 * Restricts a user (already holding the global 'admin'/'manager' role) to
 * exactly one country. See the migration for why "no row = superadmin".
 *
 * @property int $id
 * @property int $user_id
 * @property int $country_id
 * @property int|null $created_by
 * @property bool $manager_role_granted
 */
class CountryAdmin extends Model
{
    protected $guarded = ['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
