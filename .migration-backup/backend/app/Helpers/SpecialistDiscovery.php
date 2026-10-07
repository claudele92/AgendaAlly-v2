<?php
declare(strict_types=1);

namespace App\Helpers;

use App\Models\Invitation;
use App\Models\Service;
use App\Models\ServiceMaster;
use App\Models\Shop;
use App\Models\ShopLocation;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class SpecialistDiscovery
{
    /** One authority for displayed professions, modes and starting prices. */
    public static function eligibleAssignments(Builder|\Illuminate\Database\Eloquent\Relations\Relation $query, array $filter = []): Builder|\Illuminate\Database\Eloquent\Relations\Relation
    {
        return $query->where('active', true)
            ->when(!empty($filter['service_id']), fn ($q) => $q->whereIn('service_id', (array) $filter['service_id']))
            ->whereHas('master', fn ($master) => $master->where('active', true))
            ->whereHas('service', fn ($service) => $service
                ->where('status', Service::STATUS_ACCEPTED)
                ->whereColumn('services.shop_id', 'service_masters.shop_id'))
            ->whereHas('shop', fn ($shop) => $shop->where('status', Shop::APPROVED)
                ->when(!empty($filter['shop_id']), fn ($q) => $q->whereKey($filter['shop_id']))
                ->when(!empty($filter['country_id']) || !empty($filter['city_id']) || !empty($filter['shop_location_id']),
                    fn ($q) => $q->whereHas('locations', fn ($location) => $location
                        ->where('type', ShopLocation::SERVICE)
                        ->when(!empty($filter['country_id']), fn ($q) => $q->where('country_id', $filter['country_id']))
                        ->when(!empty($filter['city_id']), fn ($q) => $q->where('city_id', $filter['city_id']))
                        ->when(!empty($filter['shop_location_id']), fn ($q) => $q->whereKey($filter['shop_location_id'])))))
            ->whereHas('master.invitations', fn ($invite) => $invite
                ->where('status', Invitation::ACCEPTED)
                ->where('role', 'master')
                ->when(!empty($filter['shop_location_id']), fn ($q) => $q->where(fn ($assignment) => $assignment
                    ->whereHas('shopLocations', fn ($location) => $location->whereKey($filter['shop_location_id']))
                    ->orWhereDoesntHave('shopLocations')))
                ->whereColumn('invitations.shop_id', 'service_masters.shop_id'));
    }

    public static function isPublic(User $user): bool
    {
        return $user->hasRole('master') &&
            self::eligibleAssignments(ServiceMaster::query())->where('master_id', $user->id)->exists();
    }

    public static function fields(User $user): array
    {
        if (!$user->relationLoaded('serviceMasters') || !$user->relationLoaded('roles')) return [];
        $assignments = $user->serviceMasters;
        if ($assignments->isEmpty()) return [];
        $domains = [];
        $modes = [];
        $prices = [];
        foreach ($assignments as $assignment) {
            $category = $assignment->service?->category;
            // A parent SERVICE domain is a profession, not a generic service
            // category. All labels come from existing translated taxonomy.
            $domain = $category?->parent?->translation?->title ?? $category?->translation?->title;
            if ($domain) $domains[] = $domain;
            $mode = $assignment->type ?: $assignment->service?->type;
            if (in_array($mode, Service::TYPES, true)) $modes[] = $mode;
            $prices[] = max(0, (float) $assignment->rate_total_price);
        }
        return [
            'profile_visibility' => $user->hasRole('master') ? 'public' : 'private',
            'professional_domains' => array_values(array_unique($domains)),
            'service_modes' => array_values(array_unique($modes)),
            'starting_price' => $prices ? min($prices) : null,
        ];
    }

    public static function relations(string $language, array $filter = []): array
    {
        return [
            'roles',
            'serviceMasters' => fn ($q) => self::eligibleAssignments($q, $filter),
            'serviceMasters.service.category.translation' => fn ($q) => $q->where('locale', $language),
            'serviceMasters.service.category.parent.translation' => fn ($q) => $q->where('locale', $language),
            'serviceMasters.service.translation' => fn ($q) => $q->where('locale', $language),
        ];
    }
}