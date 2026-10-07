<?php
declare(strict_types=1);
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Read-only domain projection. Financial writes go through the locked writer. */
final class CommercePaymentAllocation extends Model
{
    protected $guarded = ['*'];
    protected $hidden = ['native_components', 'payer_user_id', 'local_client_id', 'vendor_user_id', 'checkout_key'];
    protected $casts = ['native_components' => 'array', 'committed_at' => 'datetime', 'finalized_at' => 'datetime'];

    protected static function booted(): void
    {
        static::saving(fn () => throw new \DomainException('Allocation CRUD is forbidden.'));
        static::deleting(fn () => throw new \DomainException('Allocation evidence cannot be deleted.'));
    }
}