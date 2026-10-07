<?php
declare(strict_types=1);

namespace App\Casts;

use App\Services\PaymentEligibility\ProviderConfigurationFields;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\Crypt;

/**
 * Keep native JSON metadata queryable while encrypting recognized secrets.
 * Existing plaintext can be read internally, but never exported by resources;
 * it remains uncertified until an explicit configuration update replaces it.
 */
final class EncryptedProviderPayload implements CastsAttributes
{
    public const PREFIX = 'encrypted:v1:';

    public function get($model, string $key, $value, array $attributes): ?array
    {
        if ($value === null) return null;
        $payload = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        foreach (ProviderConfigurationFields::SECRET_KEYS as $field) {
            if (is_string($payload[$field] ?? null) && str_starts_with($payload[$field], self::PREFIX)) {
                $payload[$field] = Crypt::decryptString(substr($payload[$field], strlen(self::PREFIX)));
            }
        }
        return $payload;
    }

    public function set($model, string $key, $value, array $attributes): ?string
    {
        if ($value === null) return null;
        if (!is_array($value)) throw new \InvalidArgumentException('Provider configuration must be an object.');
        foreach (ProviderConfigurationFields::SECRET_KEYS as $field) {
            if (!array_key_exists($field, $value) || $value[$field] === null || $value[$field] === '') continue;
            if (!is_string($value[$field])) throw new \InvalidArgumentException('Invalid provider credential type.');
            // Inputs are plaintext; never accept caller-supplied ciphertext.
            $value[$field] = self::PREFIX.Crypt::encryptString($value[$field]);
        }
        return json_encode($value, JSON_THROW_ON_ERROR);
    }
}