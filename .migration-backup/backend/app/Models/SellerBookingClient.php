<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class SellerBookingClient extends Model
{
    protected $table = 'seller_booking_clients';

    protected $guarded = ['id'];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function shopLocation(): BelongsTo
    {
        return $this->belongsTo(ShopLocation::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'local_client_id');
    }

    public static function normalizeName(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            throw ValidationException::withMessages([
                'name' => ['Enter a client name.'],
            ]);
        }

        return $name;
    }

    public static function normalizePhone(?string $phone): ?string
    {
        $phone = preg_replace('/\D+/', '', (string) $phone);

        return $phone !== '' ? $phone : null;
    }

    public static function normalizeEmail(?string $email): ?string
    {
        $email = mb_strtolower(trim((string) $email));

        return $email !== '' ? $email : null;
    }
}