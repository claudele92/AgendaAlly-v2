<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopPickupPolicy extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'weekly_hours' => 'array',
        'blackout_dates' => 'array',
        'same_day' => 'boolean',
        'preparation_minutes' => 'integer',
        'window_minutes' => 'integer',
    ];
}