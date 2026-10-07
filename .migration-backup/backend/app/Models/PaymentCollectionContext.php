<?php
declare(strict_types=1);
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Never expose raw contribution provenance through generic commerce resources. */
final class PaymentCollectionContext extends Model
{
    protected $guarded = ['*'];
    protected $hidden = [
        'funding_key', 'funding_event_key', 'receipt_claim_key', 'receipt_anchor_context_id',
        'configuration_reference', 'configuration_revision', 'merchant_binding_reference',
        'payment_process_reference', 'provider_payment_reference', 'source_transaction_id',
        'wallet_id', 'wallet_history_reference', 'credential_owner_id', 'expected_collector_id', 'confirmed_collector_id',
    ];
    protected $casts = ['committed_at' => 'datetime', 'confirmed_at' => 'datetime'];

    protected static function booted(): void
    {
        static::saving(fn () => throw new \DomainException('Contribution CRUD is forbidden.'));
        static::deleting(fn () => throw new \DomainException('Contribution evidence cannot be deleted.'));
    }
}