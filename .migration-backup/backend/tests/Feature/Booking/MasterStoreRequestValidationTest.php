<?php

declare(strict_types=1);

namespace Tests\Feature\Booking;

use App\Http\Requests\Booking\MasterStoreRequest;
use App\Models\Category;
use App\Models\Invitation;
use App\Models\Region;
use App\Models\Service;
use App\Models\ServiceMaster;
use App\Models\Shop;
use App\Models\ShopLocation;
use App\Models\User;
use App\Services\BookingService\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * MasterStoreRequest (the master/staff calendar's own booking-creation
 * request - a separate class from the customer-facing StoreRequest) never
 * declared a shop_location_id rule at all. Laravel's $request->validated()
 * returns only declared fields, so BookingController(Master)::store() was
 * silently stripping shop_location_id before it ever reached
 * BookingService::create() - a master creating a booking at a multi-branch
 * shop would always hit the same LOCATION_AMBIGUOUS/LOCATION_REQUIRED
 * failure the customer storefront did, with no way to fix it from this
 * request type since the field didn't exist as far as validation was
 * concerned.
 */
class MasterStoreRequestValidationTest extends TestCase
{
    use RefreshDatabase;

    private function makeShop(): Shop
    {
        $seller = User::factory()->create();

        return Shop::factory()->create(['user_id' => $seller->id]);
    }

    private function makeLocation(Shop $shop): ShopLocation
    {
        $region = Region::query()->create(['active' => true]);

        return ShopLocation::query()->create([
            'shop_id' => $shop->id,
            'region_id' => $region->id,
            'type' => ShopLocation::SERVICE,
        ]);
    }

    private function makeServiceMaster(Shop $shop, User $master): ServiceMaster
    {
        $service = Service::query()->create([
            'category_id' => Category::factory()->create()->id,
            'shop_id' => $shop->id,
        ]);

        return ServiceMaster::query()->create([
            'service_id' => $service->id,
            'master_id' => $master->id,
            'shop_id' => $shop->id,
            'active' => true,
        ]);
    }

    public function test_shop_location_id_survives_validated_instead_of_being_silently_stripped(): void
    {
        $shop = $this->makeShop();
        $location = $this->makeLocation($shop);
        $master = User::factory()->create();
        $serviceMaster = $this->makeServiceMaster($shop, $master);

        $this->actingAs($master, 'sanctum');

        $request = MasterStoreRequest::create('/api/v1/dashboard/master/bookings', 'POST', [
            'user_id' => User::factory()->create()->id,
            'start_date' => now()->addDay()->format('Y-m-d H:i'),
            'data' => [
                [
                    'service_master_id' => $serviceMaster->id,
                    'shop_location_id' => $location->id,
                ],
            ],
        ]);

        $validated = Validator::make($request->all(), $request->rules())->validated();

        // Before the fix, 'shop_location_id' had no rule at all, so
        // Validator::validated() dropped it here exactly as
        // $request->validated() would have in the real controller.
        $this->assertSame($location->id, $validated['data'][0]['shop_location_id']);
    }

    public function test_validated_shop_location_id_is_honored_by_bookings_service_resolution(): void
    {
        $shop = $this->makeShop();
        $locationA = $this->makeLocation($shop);
        $this->makeLocation($shop);
        $master = User::factory()->create();
        $serviceMaster = $this->makeServiceMaster($shop, $master);

        $invitation = Invitation::query()->create([
            'shop_id' => $shop->id,
            'user_id' => $master->id,
            'status' => Invitation::ACCEPTED,
        ]);
        $invitation->shopLocations()->sync([$locationA->id]);

        $this->actingAs($master, 'sanctum');

        $request = MasterStoreRequest::create('/api/v1/dashboard/master/bookings', 'POST', [
            'user_id' => User::factory()->create()->id,
            'start_date' => now()->addDay()->format('Y-m-d H:i'),
            'data' => [
                [
                    'service_master_id' => $serviceMaster->id,
                    'shop_location_id' => $locationA->id,
                ],
            ],
        ]);

        $validated = Validator::make($request->all(), $request->rules())->validated();

        // Same resolution BookingService::create() runs per item via
        // beforeSave() - proves the value that now survives validation
        // is the exact one the service acts on, not just present in an
        // array nothing reads.
        $shopId = null;
        $result = (new BookingService)->beforeSave($validated['data'][0], 1, $shopId);

        $this->assertSame($locationA->id, $result['shop_location_id']);
    }
}
