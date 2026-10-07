<?php
declare(strict_types=1);

namespace App\Services\PaymentEligibility;

use App\Helpers\EnvironmentPolicy;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

/**
 * Structural readiness only. Never decrypt credentials, contact a provider,
 * establish legal settlement custody, or infer capability from stored secrets.
 */
final class ProviderState
{
    public const TAGS = ['mtn', 'orange', 'flutter-wave', 'paystack', 'stripe', 'paypal'];

    public function capability(string $tag, array $context): array
    {
        $direct = in_array($tag, ['mtn', 'orange'], true);
        $currencies = config("payment_eligibility.currencies.$tag");
        if ($tag === 'mtn') {
            $currencies = config('payment_eligibility.vendor_direct_currencies.mtn', []);
            if (config('app.env') === 'testing') {
                $currencies = array_merge($currencies, config('payment_eligibility.vendor_direct_sandbox_currencies.mtn', []));
            }
        }
        $currency = is_array($currencies)
            ? in_array(strtoupper((string) ($context['transaction_currency'] ?? '')), $currencies, true)
            : null;
        $mode = $context['collection_mode'] ?? null;
        $modeSupported = in_array($mode, $direct ? ['platform', 'vendor_direct'] : ['platform'], true);
        // These adapters route by merchant/currency, not a hardcoded country ID.
        // This is code-level routing, NOT proof of country-specific onboarding.
        $supported = !$modeSupported || $currency === false ? false : ($currency === true ? true : null);
        return [
            'state' => $supported === null ? 'UNKNOWN' : ($supported ? 'SUPPORTED' : 'UNSUPPORTED'),
            'supported' => $supported,
            'currency_supported' => $currency,
            'supported_currencies' => $currencies,
            'country_routing' => 'MERCHANT_PARAMETERIZED',
            'country_onboarding_verified' => false,
            'collection_modes' => $direct ? ['platform', 'vendor_direct'] : ['platform'],
            'collection_mode_supported' => $modeSupported,
            'configuration_supported' => $modeSupported && $currency !== false,
            'initialization' => $tag === 'orange' ? 'IMPLEMENTED_BUT_BLOCKED' : 'IMPLEMENTED',
            'verification' => $tag === 'orange' ? 'MISSING' : 'IMPLEMENTED',
            'callback' => $tag === 'orange' ? 'UNVERIFIED' : 'IMPLEMENTED',
            'refund_ready' => false,
            'settlement_verified' => false,
        ];
    }

    public function decision(Payment $payment, array $context, bool $configuration = false): array
    {
        $tag = (string) $payment->tag;
        $mode = $configuration ? 'vendor_direct' : ($context['collection_mode'] ?? null);
        $capability = $this->capability($tag, ['collection_mode' => $mode] + $context);
        $valid = (bool) ($context['valid'] ?? false);
        $countryPermitted = $valid;
        foreach ($context['country_ids'] ?? [] as $countryId) {
            $countryPermitted = $countryPermitted && DB::table('country_payments')
                ->where('country_id', $countryId)->where('payment_id', $payment->id)->where('active', true)->exists();
        }
        $merchant = $this->merchant($payment, $context, $mode);
        $runtimeReasons = $merchant['missing'];
        if ($tag === 'orange') $runtimeReasons[] = 'authoritative_verifier_missing';
        if (!$this->callbackUrlReady()) $runtimeReasons[] = 'callback_url_not_configured';
        // Global payload rows lack the immutable revision required by the
        // accepted native contribution adapter. Do not invent that identity.
        if (in_array($tag, ['flutter-wave', 'paystack', 'stripe', 'paypal'], true)) {
            $revision = \App\Services\PaymentAccounting\MerchantRevisions::installed()
                ? DB::table('payment_payloads')->where('payment_id',$payment->id)->value('revision_id') : null;
            if (!$revision) $runtimeReasons[] = 'canonical_configuration_revision_unavailable';
            if ($merchant['exists'] && !$this->encryptedPayload($payment)) {
                $runtimeReasons[] = 'legacy_secret_storage_not_certified';
            }
            if (in_array($tag, ['stripe', 'paypal'], true) && config('app.env')!=='testing') {
                $runtimeReasons[] = 'provider_account_contract_not_certified';
            }
        }
        if ($merchant['exists'] && !$merchant['currency_matches']) $runtimeReasons[] = 'collector_currency_mismatch';
        if ($tag === 'mtn' && $merchant['configured']) {
            $query = DB::table($mode === 'vendor_direct' ? 'shop_payments' : 'platform_payment_configs')
                ->where('payment_id', $payment->id)
                ->where($mode === 'vendor_direct' ? 'shop_id' : 'country_id',
                    $mode === 'vendor_direct' ? ($context['shop_ids'][0] ?? null) : ($context['country_ids'][0] ?? null));
            $target = (string) $query->value('target_environment'); // Non-secret routing metadata.
            if (!preg_match('/^[a-z]+$/D', $target)) $runtimeReasons[] = 'provider_environment_invalid';
            if ($target !== 'sandbox') {
                $url = \Illuminate\Support\Facades\Schema::hasColumn($query->from, 'base_url')
                    ? (string) $query->value('base_url') : '';
                if (!filter_var($url, FILTER_VALIDATE_URL) || parse_url($url, PHP_URL_SCHEME) !== 'https'
                    || parse_url($url, PHP_URL_USER) || parse_url($url, PHP_URL_PASS)) {
                    $runtimeReasons[] = 'provider_endpoint_not_configured';
                }
            }
            if ($target === 'sandbox' && strtoupper((string) ($context['transaction_currency'] ?? '')) !== 'EUR') {
                $runtimeReasons[] = 'mtn_sandbox_currency_mismatch';
            }
        }
        $runtimeReady = $merchant['configured'] && $runtimeReasons === [];
        $environment = EnvironmentPolicy::paymentProviderEnabled($tag);
        // Phase 3A has no live/sandbox activation authority. Synthetic tests
        // remain possible only inside the already isolated testing environment.
        $activation = config('app.env') === 'testing' && $environment;
        $quote = $context['quote_supported'] ?? null;
        $reasons = [];
        if (!$valid) $reasons[] = $context['reason'] ?? 'invalid_business_context';
        if (!$payment->active) $reasons[] = 'globally_disabled';
        if (!$countryPermitted) $reasons[] = 'country_policy_denied';
        if (!$capability['collection_mode_supported']) $reasons[] = 'collection_mode_unsupported';
        if ($capability['currency_supported'] !== true) {
            $reasons[] = $capability['currency_supported'] === null ? 'currency_support_unknown' : 'currency_unsupported';
        }
        if (!in_array($context['transaction_type'] ?? null, ['product', 'booking'], true)) $reasons[] = 'transaction_type_unsupported';
        if (count($context['shop_ids'] ?? []) === 0) $reasons[] = 'checkout_target_required';
        elseif (count($context['shop_ids']) > 1) $reasons[] = 'multi_shop_collection_unsupported';
        if (!$merchant['configured']) $reasons[] = 'collector_not_configured';
        if (!$merchant['enabled']) $reasons[] = 'collector_disabled';
        if (!$runtimeReady) $reasons[] = 'runtime_not_ready';
        if (!$environment) $reasons[] = 'environment_disabled';
        if (!$activation) $reasons[] = 'checkout_activation_disabled';
        if ($quote === false) $reasons[] = 'canonical_quote_unsupported';
        $reasons = array_values(array_unique(array_merge($reasons, $runtimeReasons)));
        $configurable = $valid && count($context['shop_ids'] ?? []) === 1
            && in_array($tag, ['mtn', 'orange'], true)
            && $capability['configuration_supported'];
        return [
            'id' => (int) $payment->id, 'tag' => $tag,
            'globally_active' => (bool) $payment->active,
            'capability' => $capability, 'capability_state' => $capability['state'],
            'configuration' => $merchant, 'configuration_state' => $merchant['state'],
            'runtime_state' => $runtimeReady ? 'READY' : 'NOT_READY',
            'runtime_ready' => $runtimeReady, 'runtime_reasons' => array_values(array_unique($runtimeReasons)),
            'integration_ready' => $tag !== 'orange',
            'vendor_configurable' => in_array($tag, ['mtn', 'orange'], true),
            'transaction_types' => ['booking', 'product'],
            'collection_modes' => $capability['collection_modes'],
            'country_permitted' => $countryPermitted,
            'currency_supported' => $capability['currency_supported'],
            'supported_currencies' => $capability['supported_currencies'],
            'collection_mode' => $mode, 'configured' => $merchant['configured'],
            // Discovery eligibility is a candidate; the existing native quote
            // writer MUST validate the quote before initiation or funding.
            'eligible' => $reasons === [],
            'checkout_available' => $reasons === [] && $quote === true,
            'checkout_state' => $reasons === [] && $quote === true ? 'CHECKOUT_AVAILABLE' : 'CHECKOUT_UNAVAILABLE',
            'quote_state' => $quote === null ? 'REQUIRES_NATIVE_VALIDATION' : ($quote ? 'SUPPORTED' : 'UNSUPPORTED'),
            'activation_allowed' => $activation,
            'available_for_configuration' => $configuration && $configurable,
            'reasons' => $reasons,
        ];
    }

    private function merchant(Payment $payment, array $context, ?string $mode): array
    {
        $tag = (string) $payment->tag;
        $direct = $mode === 'vendor_direct';
        $ownerId = $direct ? ($context['shop_ids'][0] ?? null) : ($context['country_ids'][0] ?? null);
        $local = in_array($tag, ['mtn', 'orange'], true);
        $query = DB::table($local ? ($direct ? 'shop_payments' : 'platform_payment_configs') : 'payment_payloads')
            ->where('payment_id', $payment->id);
        if ($local) $query->where($direct ? 'shop_id' : 'country_id', $ownerId);
        if (!$local && $direct) $query->whereRaw('1 = 0'); // No platform fallback.
        $required = match ($tag) {
            'mtn' => ['api_key', 'subscription_key', 'api_user', 'target_environment', 'currency'],
            'orange' => ['client_id', 'merchant_key', 'currency'],
            'flutter-wave' => ['payload->flw_sk', 'payload->flw_webhook_secret_hash', 'payload->flw_account_id'],
            'paystack' => ['payload->paystack_sk'],
            'stripe' => ['payload->stripe_pk', 'payload->stripe_sk', 'payload->stripe_webhook_secret'],
            'paypal' => ['payload->paypal_mode', 'payload->paypal_webhook_id'],
            default => [],
        };
        if ($tag === 'paypal') {
            $paypalMode = (clone $query)->value('payload->paypal_mode');
            if (in_array($paypalMode, ['sandbox', 'live'], true)) {
                $required = array_merge($required, ["payload->paypal_{$paypalMode}_client_id", "payload->paypal_{$paypalMode}_client_secret"]);
            } else {
                $required[] = 'payload->paypal_mode_invalid';
            }
        }
        $exists = $query->exists();
        $presence = [];
        foreach ($required as $field) {
            $presence[str_replace('payload->', '', $field)] = (clone $query)
                ->whereNotNull($field)->where($field, '!=', '')->exists();
        }
        $configured = $exists && !in_array(false, $presence, true);
        return [
            'owner_type' => $direct ? 'shop' : 'platform',
            'owner_id' => $direct ? $ownerId : null,
            'source' => $local ? ($direct ? 'shop' : 'platform_country') : 'global_payload',
            'exists' => $exists, 'configured' => $configured,
            'state' => !$exists ? 'NOT_CONFIGURED' : ($configured ? 'CONFIGURED' : 'CONFIGURATION_INCOMPLETE'),
            'enabled' => $local ? (clone $query)->where('status', true)->exists() : $exists,
            'presence' => $presence,
            'missing' => array_keys(array_filter($presence, fn ($present) => !$present)),
            'currency_matches' => $local ? (clone $query)
                ->whereRaw('UPPER(currency) = ?', [strtoupper((string) ($context['transaction_currency'] ?? ''))])->exists()
                : strtoupper((string) (clone $query)->value($tag === 'paypal' ? 'payload->paypal_currency' : 'payload->currency'))
                    === strtoupper((string) ($context['transaction_currency'] ?? '')),
        ];
    }

    private function encryptedPayload(Payment $payment): bool
    {
        // Read raw JSON only; structural readiness must never decrypt secrets.
        $raw = DB::table('payment_payloads')->where('payment_id', $payment->id)->value('payload');
        $payload = json_decode((string) $raw, true) ?: [];
        foreach (ProviderConfigurationFields::secrets((string) $payment->tag) as $key) {
            if (!empty($payload[$key]) && (!is_string($payload[$key])
                || !str_starts_with($payload[$key], \App\Casts\EncryptedProviderPayload::PREFIX))) return false;
        }
        return true;
    }

    private function callbackUrlReady(): bool
    {
        $url = config('development.urls.api') ?: config('app.url');
        return is_string($url) && filter_var($url, FILTER_VALIDATE_URL) !== false
            && parse_url($url, PHP_URL_SCHEME) === 'https'
            && !parse_url($url, PHP_URL_USER) && !parse_url($url, PHP_URL_PASS);
    }
}