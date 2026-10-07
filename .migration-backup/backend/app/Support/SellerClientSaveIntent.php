<?php
declare(strict_types=1);

namespace App\Support;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Directory-only idempotency. Client and replay result commit in one native transaction.
 * Names are payload evidence, never a directory identity/deduplication criterion.
 */
final class SellerClientSaveIntent
{
    public const TABLE = 'seller_client_save_intents';

    public static function authority(int $actorId, int $shopId, string $key): array
    {
        return ['actor_id' => $actorId, 'shop_id' => $shopId, 'intent_key' => strtolower($key)];
    }

    public static function execute(
        int $actorId,
        int $shopId,
        string $key,
        array $payload,
        callable $create,
        callable $resolve
    ) {
        $authority = self::authority($actorId, $shopId, $key);
        $hash = hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
        try {
            return DB::transaction(function () use ($authority, $hash, $payload, $create, $resolve) {
                $existing = DB::table(self::TABLE)->where($authority)->lockForUpdate()->first();
                if ($existing !== null) {
                    self::assertPayload($existing, $hash);
                    return $resolve($existing);
                }
                $id = DB::table(self::TABLE)->insertGetId($authority + [
                    'shop_location_id' => $payload['shop_location_id'],
                    'payload_hash' => $hash,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $response = $create();
                $body = $response->getData(true);
                DB::table(self::TABLE)->where('id', $id)->update([
                    'result_kind' => $body['data']['kind'],
                    'result_id' => $body['data']['id'],
                    'reused_existing' => (bool) $body['reused_existing'],
                    'response_status' => $response->getStatusCode(),
                    'updated_at' => now(),
                ]);
                $body['client_save_intent'] = $authority['intent_key'];
                $response->setData($body);
                return $response;
            }, 3);
        } catch (QueryException $error) {
            // A competing insert may have won. Read its committed result in a NEW
            // transaction/current locking read, not the failed RR snapshot.
            if (!self::isUniqueViolation($error)) {
                throw $error;
            }
            return DB::transaction(function () use ($authority, $hash, $resolve, $error) {
                $existing = DB::table(self::TABLE)->where($authority)->lockForUpdate()->first();
                if ($existing === null) {
                    // Existing contact uniqueness belongs to the native caller, not this ledger.
                    throw $error;
                }
                self::assertPayload($existing, $hash);
                return $resolve($existing);
            }, 3);
        }
    }

    public static function isUniqueViolation(QueryException $error): bool
    {
        return in_array((string) $error->getCode(), ['23000', '23505'], true)
            && (str_contains(strtolower($error->getMessage()), 'unique')
                || str_contains(strtolower($error->getMessage()), 'duplicate'));
    }

    private static function assertPayload(object $intent, string $hash): void
    {
        if (!hash_equals($intent->payload_hash, $hash)) {
            throw new ConflictHttpException('This client-save reference belongs to different details. Resolve the original save before starting a new person.');
        }
        if ($intent->result_id === null || $intent->result_kind === null) {
            throw new ConflictHttpException('The client-save outcome is unresolved. Retry the same save reference.');
        }
    }
}