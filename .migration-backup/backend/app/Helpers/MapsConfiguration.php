<?php
declare(strict_types=1);

namespace App\Helpers;

use App\Models\Settings;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * google_map_key is a browser/referrer-restricted public key, not a server
 * credential. Native Android/iOS SDK keys remain platform build configuration.
 */
final class MapsConfiguration
{
    public const SERVER_KEY = 'google_map_server_key';
    public const INPUTS = ['google_map_key', 'google_map_server_key', 'maps_enabled',
        'clear_google_map_key', 'clear_google_map_server_key'];

    public static function canManage(?User $actor): bool
    {
        return $actor !== null && $actor->isSuperAdmin();
    }

    public static function enabled(): bool
    {
        return filter_var(Settings::where('key', 'maps_enabled')->value('value') ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    public static function browserEnvironmentPermitted(): bool
    {
        // An Admin's explicit browser opt-in replaces the legacy local import
        // default; production/staging still honor the deployment kill switch.
        $local = config('app.env') === 'local' && config('development.enabled') === true;
        return $local || EnvironmentPolicy::mapsEnabled();
    }

    public static function settings(Collection $settings, bool $admin = false): Collection
    {
        $serverConfigured = $settings->contains(fn ($row) =>
            $row->key === self::SERVER_KEY && strlen((string) $row->value) > 0);
        $rows = $settings->reject(fn ($row) =>
            $row->key === self::SERVER_KEY ||
            in_array($row->key, ['maps_enabled', 'maps_environment_permitted', 'google_map_server_key_configured'], true));
        $rows = $rows->map(fn ($row) => $row->toArray())->values();
        // Preserve the native string-valued settings contract.
        $rows->push(['key' => 'maps_enabled', 'value' => self::enabled() ? '1' : '0']);
        $rows->push(['key' => 'maps_environment_permitted', 'value' => self::browserEnvironmentPermitted() ? '1' : '0']);
        if ($admin) $rows->push(['key' => 'google_map_server_key_configured', 'value' => $serverConfigured ? '1' : '0']);
        return $rows;
    }

    /** Blank password fields mean keep; erasure requires an explicit flag. */
    public static function normalizeInput(array $input): array
    {
        foreach (['google_map_key', self::SERVER_KEY] as $key) {
            if (filter_var($input["clear_$key"] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                if (!empty($input[$key])) {
                    throw ValidationException::withMessages([$key => 'Choose either replacement or removal, not both.']);
                }
                $input[$key] = '';
            } elseif (array_key_exists($key, $input)) {
                $input[$key] = trim((string) $input[$key]);
                if ($input[$key] === '') unset($input[$key]);
            }
            unset($input["clear_$key"]);
        }
        return $input;
    }
}