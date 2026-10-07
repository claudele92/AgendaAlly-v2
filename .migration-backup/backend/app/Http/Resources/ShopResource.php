<?php
declare(strict_types=1);

namespace App\Http\Resources;

use Cache;
use App\Models\Shop;
use App\Models\ShopLocation;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\Bonus\BonusResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Shop|JsonResource $this */
        /** @var User $user */
        $user           = auth('sanctum')->user();
        $isSeller       = auth('sanctum')->check() && $user?->hasRole('seller');
        $isRecommended  = in_array($this->id, array_keys(Cache::get('shop-recommended-ids', [])));
        $locales        = $this->relationLoaded('translations')
            ? $this->translations->pluck('locale')->toArray()
            : null;

        // A shop with branches in more than one region/country/city (see
        // Shop::scopeFilter()'s whereHas('locations', ...)) correctly
        // matches a region/country/city-filtered search via ANY of its
        // locations, but 'translation.address' is a single flat string set
        // once for the whole shop - it can describe a different branch
        // than the one that actually matched, making a correct result look
        // like a leak (e.g. a Yaoundé-filtered search returning a shop
        // whose card still shows its Douala street address). When the
        // request carries the same region/country/city/area params the
        // filter used, resolve and expose the specific location that
        // matched, so the frontend can show that branch instead.
        //
        // location_type must be read the same way Shop::scopeFilter()
        // reads it for the outer whereHas('locations', ...) that decided
        // this shop qualifies at all - a shop with separate PRODUCT and
        // SERVICE ShopLocation rows for the same city (see DemoAfricaSeeder)
        // can otherwise resolve to the wrong row here: one that shares the
        // matched geography but not the type the search actually asked
        // for, silently showing that row's (possibly empty) address/
        // coordinates instead of the branch that actually matched. Most
        // callers (the storefront's own listings) already send
        // location_type=2, but default to SERVICE rather than leaving this
        // type-agnostic when a caller omits it - this is a booking
        // marketplace, so an empty PRODUCT placeholder is never the
        // branch a customer meant to match.
        $filterParams = array_filter($request->only(['region_id', 'country_id', 'city_id', 'area_id']));
        if ($filterParams) {
            $filterParams['type'] = (int) ($request->input('location_type') ?: ShopLocation::SERVICE);
        }
        $matchedLocation = null;
        if ($filterParams) {
            $locationType = $filterParams['type'];
            $isSellerDashboard = $request->is('api/v1/dashboard/seller/*');
            $branchLocationIds = null;

            if ($isSellerDashboard) {
                $branchScope = $user?->shopLocationBranchScope((int) $this->id)
                    ?? ['unrestricted' => false, 'location_ids' => []];
                $branchLocationIds = $branchScope['unrestricted'] ? null : $branchScope['location_ids'];
            }

            if ($isSellerDashboard && $this->relationLoaded('locations')) {
                $matchedLocation = $this->locations
                    ->filter(function (ShopLocation $location) use ($filterParams, $locationType, $branchLocationIds): bool {
                        if (
                            $branchLocationIds !== null
                            && !in_array((int) $location->id, $branchLocationIds, true)
                        ) {
                            return false;
                        }

                        if ((int) $location->type !== $locationType) {
                            return false;
                        }

                        foreach (['region_id', 'country_id', 'city_id', 'area_id'] as $column) {
                            if (
                                isset($filterParams[$column])
                                && (int) $location->{$column} !== (int) $filterParams[$column]
                            ) {
                                return false;
                            }
                        }

                        return true;
                    })
                    ->sortBy('id')
                    ->first();
            } else {
                $matchedLocationQuery = ShopLocation::with([
                    'region.translation',
                    'country.translation',
                    'city.translation',
                    'area.translation',
                ])
                    ->where('shop_id', $this->id)
                    ->filter($filterParams);

                if ($branchLocationIds !== null) {
                    $matchedLocationQuery->whereIn('shop_locations.id', $branchLocationIds);
                }

                $matchedLocation = $matchedLocationQuery->orderBy('id')->first();
            }
        }

        // Service hours have no native timezone column. Resolve only countries
        // with one IANA zone, using the selected branch or all known branches.
        // Product Pickup's separately configured timezone is not Service hours.
        $hoursLocations = $matchedLocation ? collect([$matchedLocation])
            : ($this->relationLoaded('locations') ? $this->locations : collect());
        $hoursCountryCodes = $hoursLocations->map(fn ($location) =>
            $location->relationLoaded('country') ? $location->country?->code : null
        )->all();

        return [
            'hours_timezone' => \App\Support\ShopHoursTimezone::forCountries($hoursCountryCodes),
            'product_fulfillment_methods' => $this->when(
                array_key_exists('product_fulfillment_methods', $this->resource->getAttributes()),
                fn () => $this->resource->productFulfillmentMethods()
            ),
            'id'                => $this->when($this->id, $this->id),
            'slug'              => $this->when($this->slug, $this->slug),
            'uuid'              => $this->when($this->uuid, $this->uuid),
            'discounts_count'   => $this->whenLoaded('discounts', $this->discounts_count),
            'user_id'           => $this->when($this->user_id, $this->user_id),
            'tax'               => $this->when($this->tax, $this->tax),
            'percentage'        => $this->when($this->percentage, $this->percentage),
            'phone'             => $this->when($this->phone, $this->phone),
            'open'              => (bool)$this->open,
            'visibility'        => (bool)$this->visibility,
            'verify'            => (bool)$this->verify,
            'collect_via_platform' => (bool)$this->collect_via_platform,
            'delivery_type'     => $this->when($this->delivery_type, $this->delivery_type),
            'background_img'    => $this->when($this->background_img, $this->background_img),
            'logo_img'          => $this->when($this->logo_img, $this->logo_img),
            'min_amount'        => $this->when($this->min_amount, (int)$this->min_amount),
            'is_recommended'    => $this->when($isRecommended, $isRecommended),
            'status'            => $this->when($this->status, $this->status),
            'status_note'       => $this->when($this->status_note, $this->status_note),
            'delivery_time'     => $this->when($this->delivery_time, $this->delivery_time),
            'invite_link'       => $this->when($isSeller, "/shop/invitation/$this->uuid/link"),
            'rating_avg'        => $this->when($this->reviews_avg_rating, $this->reviews_avg_rating),
            'reviews_count'     => $this->when($this->reviews_count,      $this->reviews_count),
            'orders_count'      => $this->when($this->orders_count,       $this->orders_count),
            'lat_long'          => $this->when($this->lat_long,           $this->lat_long),
            'locations_count'   => $this->when($this->locations_count,    $this->locations_count),
            'r_count'           => $this->when($this->r_count,            $this->r_count),
            'r_avg'             => $this->when($this->r_avg,              $this->r_avg),
            'r_sum'             => $this->when($this->r_sum,              $this->r_sum),
            'o_count'           => $this->when($this->o_count,            $this->o_count),
            'od_count'          => $this->when($this->od_count,           $this->od_count),
            'b_count'           => $this->when($this->b_count,            $this->b_count),
            'b_sum'             => $this->when($this->b_sum,              $this->b_sum),
            'min_price'         => $this->when($this->min_price,          $this->min_price),
            'max_price'         => $this->when($this->max_price,          $this->max_price),
            'service_min_price' => $this->when($this->service_min_price,  $this->service_min_price),
            'service_max_price' => $this->when($this->service_max_price,  $this->service_max_price),
            // Null (not 0) when no distance was actually computed - see
            // ByLocation::distanceSelectRaw(), only added to the query
            // when a real customer position is known. A shop's own,
            // possibly-stale coordinates aside, "no known position"
            // must never render as if the customer were standing at the
            // shop (see shop-card.tsx / shop-card-ui-2.tsx, which now
            // hide the distance line entirely when this is null).
            'distance'          => $this->distance,
            'ai_token_limit'    => $this->when($this->ai_token_limit, $this->ai_token_limit ?? 0),
            'ai_access'         => $this->when($this->ai_access, $this->ai_access ?? false),
            'email_statuses'    => $this->when($this->email_statuses,     $this->email_statuses),
            'created_at'        => $this->when($this->created_at, $this->created_at?->format('Y-m-d H:i:s') . 'Z'),
            'updated_at'        => $this->when($this->updated_at, $this->updated_at?->format('Y-m-d H:i:s') . 'Z'),
            'products_count'    => $this->whenLoaded('products', $this->products_count, 0),

            'translation'       => TranslationResource::make($this->whenLoaded('translation')),
            'services'          => ServiceResource::collection($this->whenLoaded('services')),
            'serviceExtras'     => ServiceExtraResource::collection($this->whenLoaded('serviceExtras')),
            'member_ships'      => MemberShipResource::collection($this->whenLoaded('memberShips')),
            'tags'              => ShopTagResource::collection($this->whenLoaded('tags')),
            'translations'      => TranslationResource::collection($this->whenLoaded('translations')),
            'locales'           => $this->when($locales, $locales),
            'seller'            => UserResource::make($this->whenLoaded('seller')),
            'documents'         => GalleryResource::collection($this->whenLoaded('documents')),
            'subscription'      => ShopSubscriptionResource::make($this->whenLoaded('subscription')),
            'categories'        => CategoryResource::collection($this->whenLoaded('categories')),
            'bonus'             => BonusResource::make($this->whenLoaded('bonus')),
            'discounts'         => SimpleDiscountResource::collection($this->whenLoaded('discounts')),
            'shop_payments'     => ShopPaymentResource::collection($this->whenLoaded('shopPayments')),
            'socials'           => ShopSocialResource::collection($this->whenLoaded('socials')),
            'shop_working_days' => ShopWorkingDayResource::collection($this->whenLoaded('workingDays')),
            'shop_closed_date'  => ShopClosedDateResource::collection($this->whenLoaded('closedDates')),
            'location'          => ShopLocationResource::make($this->whenLoaded('location')),
            'locations'         => ShopLocationResource::collection($this->whenLoaded('locations')),
            'matched_location'  => $this->when($matchedLocation, ShopLocationResource::make($matchedLocation)),
        ];
    }
}
