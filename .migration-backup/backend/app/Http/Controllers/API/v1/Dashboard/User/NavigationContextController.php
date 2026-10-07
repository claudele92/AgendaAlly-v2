<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Dashboard\User;

use App\Helpers\CountryContext;
use App\Models\Country;
use App\Models\CountryInvitation;
use App\Models\CountryPermission;
use App\Models\Invitation;
use App\Models\Shop;
use App\Models\ShopLocation;
use App\Models\ShopPermission;
use App\Models\User;
use Illuminate\Http\JsonResponse;

/**
 * Read-only, authenticated navigation context for the current actor.
 *
 * Permission decisions intentionally delegate to User's existing country and
 * shop authorization methods. This endpoint is descriptive only; it does not
 * authorize or switch the context of any domain request.
 */
class NavigationContextController extends UserBaseController
{
    public function show(): JsonResponse
    {
        /** @var User $user */
        $user = auth('sanctum')->user();
        $user->loadMissing(['roles', 'shop']);

        $role = $user->role;
        $isSuperAdmin = $user->isSuperAdmin();
        $countryId = CountryContext::restrictedCountryId();
        $countryContextUnknown = $this->countryContextUnknown($user, $countryId);
        $countryScope = $countryContextUnknown
            ? $this->emptyScope()
            : $this->countryScope($user, $countryId, $isSuperAdmin);

        [$shop, $owner, $shopContextUnknown] = $this->currentShop($user, $role);
        $shopScope = $this->emptyScope();
        $branches = ['unrestricted' => false, 'location_ids' => [], 'locations' => []];
        $country = null;
        $countrySource = null;
        $scopeStatus = $shopContextUnknown || $countryContextUnknown ? 'unknown' : 'known';

        if ($shop && !$shopContextUnknown) {
            $shopScope = $this->shopScope($user, $shop, $owner, $role);
            $branches = $this->branchScope($user, $shop, $role);

            if ($branches === null) {
                $scopeStatus = 'unknown';
                $shop = null;
                $owner = false;
                $shopScope = $this->emptyScope();
                $branches = ['unrestricted' => false, 'location_ids' => [], 'locations' => []];
            } else {
                $country = $this->countryForShopGeography($shop);
                if ($country === false) {
                    $scopeStatus = 'unknown';
                    $country = null;
                } elseif ($country !== null) {
                    $countrySource = 'shop_geography';
                }
            }
        }

        if ($countryId !== null && !$countryContextUnknown) {
            $country = $this->countryDisplay($countryId);
            $countrySource = $country !== null ? 'restricted_country' : null;
            if ($country === null) {
                $scopeStatus = 'unknown';
            }
        }

        return $this->successResponse(
            __('errors.' . \App\Helpers\ResponseError::NO_ERROR, locale: $this->language),
            [
                'user_id' => (int) $user->id,
                'role' => $role,
                'is_super_admin' => $isSuperAdmin,
                'country' => $country,
                'country_source' => $countrySource,
                'country_scope' => $countryScope,
                'shop' => $shop ? [
                    'id' => (int) $shop->id,
                    'name' => $this->shopName($shop),
                    'owner' => $owner,
                ] : null,
                'shop_scope' => $shopScope,
                'branches' => $branches,
                'scope_status' => $scopeStatus,
            ]
        );
    }

    /**
     * CountryContext is the authority for restricted actors. In particular,
     * this does not consult a request-supplied country_id.
     *
     * @return array{unrestricted: bool, permission_keys: string[]}
     */
    private function countryScope(User $user, ?int $countryId, bool $isSuperAdmin): array
    {
        if ($isSuperAdmin) {
            return [
                'unrestricted' => true,
                'permission_keys' => $this->countryPermissionKeys($user, null),
            ];
        }

        return [
            'unrestricted' => false,
            'permission_keys' => $countryId === null
                ? []
                : $this->countryPermissionKeys($user, $countryId),
        ];
    }

    /**
     * CountryContext's selected id is authoritative, but multiple accepted
     * country grants can make its implicit first-row choice unsafe. Direct
     * country administrators take precedence in CountryContext itself.
     */
    private function countryContextUnknown(User $user, ?int $countryId): bool
    {
        if ($countryId === null || $user->countryAdmin) {
            return false;
        }

        return $user->countryInvitations()
            ->where('status', CountryInvitation::ACCEPTED)
            ->whereNotNull('country_role_id')
            ->distinct()
            ->count('country_id') > 1;
    }

    /**
     * @return string[]
     */
    private function countryPermissionKeys(User $user, ?int $countryId): array
    {
        return CountryPermission::query()
            ->orderBy('group')
            ->orderBy('key')
            ->pluck('key')
            ->filter(fn (string $key) => $countryId === null
                ? $user->isSuperAdmin()
                : $user->hasCountryPermission($countryId, $key))
            ->values()
            ->all();
    }

    /**
     * Resolve only the actor's own shop or one accepted invitation in the
     * same role contexts used by the seller/master APIs. Multiple active
     * invitations are ambiguous; never silently select one.
     *
     * @return array{0: Shop|null, 1: bool, 2: bool}
     */
    private function currentShop(User $user, string $role): array
    {
        // The master dashboard resolves its specialist context from accepted
        // invitations, not from the seller-owner relation.
        if ($role !== 'master' && $user->shop) {
            return [$user->shop, true, false];
        }

        $shopRoles = ['moderator', 'shop_manager', 'deliveryman', 'master'];
        if (!in_array($role, $shopRoles, true)) {
            return [null, false, false];
        }

        $invitations = $user->invitations()
            ->where('status', Invitation::ACCEPTED)
            ->with('shop')
            ->get();

        if ($invitations->isEmpty()) {
            return [null, false, false];
        }

        if ($invitations->count() !== 1 || !$invitations->first()->shop) {
            return [null, false, true];
        }

        return [$invitations->first()->shop, false, false];
    }

    /**
     * @return array{unrestricted: bool, permission_keys: string[]}
     */
    private function shopScope(User $user, Shop $shop, bool $owner, string $role): array
    {
        // A master is a specialist, never a shop owner for this context.
        // Use the same User methods against a relation-cleared actor clone so
        // their structural owner bypass cannot leak into master navigation.
        $permissionActor = clone $user;
        if ($role === 'master') {
            $permissionActor->setRelation('shop', null);
        }

        $permissions = ShopPermission::query()
            ->orderBy('group')
            ->orderBy('key')
            ->pluck('key')
            ->filter(fn (string $key) => $permissionActor->hasShopPermission((int) $shop->id, $key))
            ->values()
            ->all();

        return [
            'unrestricted' => $owner,
            'permission_keys' => $permissions,
        ];
    }

    /**
     * @return array{unrestricted: bool, location_ids: int[], locations: array[]}|null
     */
    private function branchScope(User $user, Shop $shop, string $role): ?array
    {
        $branchActor = clone $user;
        if ($role === 'master') {
            $branchActor->setRelation('shop', null);
        }

        $scope = $branchActor->bookingBranchScope((int) $shop->id);
        $locationIds = array_values(array_unique(array_map('intval', $scope['location_ids'])));

        $locationsQuery = $shop->locations();
        if (!$scope['unrestricted']) {
            $locationsQuery->whereIn('id', $locationIds);
        }

        $locations = $locationsQuery
            ->with('city.translations')
            ->orderBy('id')
            ->get()
            ->map(fn (ShopLocation $location) => $this->locationDisplay($location))
            ->values()
            ->all();

        return [
            'unrestricted' => (bool) $scope['unrestricted'],
            'location_ids' => $locationIds,
            'locations' => $locations,
        ];
    }

    /**
     * @return array{unrestricted: bool, permission_keys: string[]}
     */
    private function emptyScope(): array
    {
        return ['unrestricted' => false, 'permission_keys' => []];
    }

    /**
     * @return array{id: int, name: string}|null
     */
    private function countryDisplay(int $countryId): ?array
    {
        $country = Country::with('translations')->find($countryId);
        if (!$country) {
            return null;
        }

        $name = $this->translationTitle($country->translations);
        if (!$name) {
            $name = $country->code;
        }
        if (!$name) {
            return null;
        }

        return [
            'id' => (int) $country->id,
            'name' => $name,
        ];
    }

    /**
     * @return array{id: int, name: string}|null|false
     * `false` denotes conflicting shop geographies and is intentionally
     * distinguishable from an absent country.
     */
    private function countryForShopGeography(Shop $shop): array|null|false
    {
        $countryIds = $shop->locations()
            ->whereNotNull('country_id')
            ->distinct()
            ->pluck('country_id')
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($countryIds->isEmpty()) {
            return null;
        }

        if ($countryIds->count() !== 1) {
            return false;
        }

        return $this->countryDisplay($countryIds->first());
    }

    /**
     * @return array{id: int, name: string, address?: string}|null
     */
    private function locationDisplay(ShopLocation $location): ?array
    {
        $name = $location->alias ?: $this->translationTitle($location->city?->translations ?? collect());
        if (!$name) {
            return null;
        }

        $display = ['id' => (int) $location->id, 'name' => $name];
        if ($location->address !== null && $location->address !== '') {
            $display['address'] = $location->address;
        }

        return $display;
    }

    private function shopName(Shop $shop): ?string
    {
        return $this->translationTitle($shop->translations()->get());
    }

    private function translationTitle($translations): ?string
    {
        $locale = request()->input('lang', app()->getLocale());
        $translation = $translations->firstWhere('locale', $locale)
            ?? $translations->first();

        return $translation?->title;
    }
}