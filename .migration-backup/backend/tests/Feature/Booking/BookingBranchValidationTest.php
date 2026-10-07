<?php

declare(strict_types=1);

namespace Tests\Feature\Booking;

use App\Helpers\ResponseError;
use App\Models\Category;
use App\Models\Invitation;
use App\Models\Region;
use App\Models\Service;
use App\Models\ServiceMaster;
use App\Models\Shop;
use App\Models\ShopLocation;
use App\Models\User;
use App\Services\BookingService\BookingService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage for the previously-missing branch validation at
 * booking-creation time: a raw API request could book any master at any
 * branch, or at a branch a multi-branch master wasn't assigned to, with
 * nothing rejecting it. BookingService::beforeSave()/resolveBookingLocation()
 * now makes shop_location_id a first-class, validated attribute of the
 * booking - while preserving the pre-existing "zero pivot rows = assigned
 * everywhere" semantics for masters (see User::bookingBranchScope()).
 *
 * Also covers the hotfix that followed: the storefront never actually
 * sends shop_location_id at all (no frontend work had shipped for it yet),
 * so requiring it unconditionally once a shop had any SERVICE location
 * blocked every booking to that shop - single-branch or multi-branch
 * alike. autoResolveBookingLocation() now resolves the unambiguous cases
 * (one location; or several, but the master is only assigned to one) when
 * shop_location_id is omitted, and only a genuinely ambiguous case (a
 * multi-branch shop with an unrestricted, or multiply-assigned, master)
 * still rejects - with a distinct LOCATION_AMBIGUOUS code rather than the
 * generic LOCATION_REQUIRED, so the frontend can tell the two apart. An
 * explicitly supplied shop_location_id is still fully validated exactly
 * as before either way.
 */
class BookingBranchValidationTest extends TestCase
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

    private function bookingItem(ServiceMaster $serviceMaster, ?int $shopLocationId): array
    {
        return array_filter([
            'service_master_id' => $serviceMaster->id,
            'shop_location_id' => $shopLocationId,
        ], fn ($v) => $v !== null);
    }

    public function test_single_branch_shop_with_no_locations_configured_needs_no_location(): void
    {
        $shop = $this->makeShop();
        $master = User::factory()->create();
        $serviceMaster = $this->makeServiceMaster($shop, $master);

        $shopId = null;
        $result = (new BookingService)->beforeSave(
            $this->bookingItem($serviceMaster, null),
            1,
            $shopId
        );

        $this->assertNull($result['shop_location_id']);
    }

    public function test_multi_branch_shop_accepts_booking_at_either_location_for_unrestricted_master(): void
    {
        $shop = $this->makeShop();
        $locationA = $this->makeLocation($shop);
        $locationB = $this->makeLocation($shop);
        $master = User::factory()->create();
        $serviceMaster = $this->makeServiceMaster($shop, $master);

        $shopId = null;
        $resultA = (new BookingService)->beforeSave(
            $this->bookingItem($serviceMaster, $locationA->id),
            1,
            $shopId
        );
        $shopId = null;
        $resultB = (new BookingService)->beforeSave(
            $this->bookingItem($serviceMaster, $locationB->id),
            1,
            $shopId
        );

        $this->assertSame($locationA->id, $resultA['shop_location_id']);
        $this->assertSame($locationB->id, $resultB['shop_location_id']);
    }

    public function test_specialist_assigned_to_multiple_branches_can_book_either_assigned_branch(): void
    {
        $shop = $this->makeShop();
        $locationA = $this->makeLocation($shop);
        $locationB = $this->makeLocation($shop);
        $locationC = $this->makeLocation($shop);
        $master = User::factory()->create();
        $serviceMaster = $this->makeServiceMaster($shop, $master);

        $invitation = Invitation::query()->create([
            'shop_id' => $shop->id,
            'user_id' => $master->id,
            'status' => Invitation::ACCEPTED,
        ]);
        $invitation->shopLocations()->sync([$locationA->id, $locationB->id]);

        $shopId = null;
        $resultA = (new BookingService)->beforeSave(
            $this->bookingItem($serviceMaster, $locationA->id),
            1,
            $shopId
        );
        $this->assertSame($locationA->id, $resultA['shop_location_id']);

        $shopId = null;
        $resultB = (new BookingService)->beforeSave(
            $this->bookingItem($serviceMaster, $locationB->id),
            1,
            $shopId
        );
        $this->assertSame($locationB->id, $resultB['shop_location_id']);

        $this->expectException(Exception::class);
        $shopId = null;
        (new BookingService)->beforeSave(
            $this->bookingItem($serviceMaster, $locationC->id),
            1,
            $shopId
        );
    }

    public function test_manipulated_cross_branch_booking_is_rejected(): void
    {
        $shop = $this->makeShop();
        $locationA = $this->makeLocation($shop);
        $locationB = $this->makeLocation($shop);
        $master = User::factory()->create();
        $serviceMaster = $this->makeServiceMaster($shop, $master);

        $invitation = Invitation::query()->create([
            'shop_id' => $shop->id,
            'user_id' => $master->id,
            'status' => Invitation::ACCEPTED,
        ]);
        $invitation->shopLocations()->sync([$locationA->id]);

        try {
            $shopId = null;
            (new BookingService)->beforeSave(
                $this->bookingItem($serviceMaster, $locationB->id),
                1,
                $shopId
            );
            $this->fail('Expected booking at an unassigned branch to be rejected.');
        } catch (Exception $e) {
            $this->assertStringContainsString(
                __('errors.' . ResponseError::MASTER_NOT_IN_LOCATION),
                $e->getMessage()
            );
        }
    }

    public function test_location_belonging_to_a_different_shop_is_rejected(): void
    {
        $shop = $this->makeShop();
        $this->makeLocation($shop);
        $master = User::factory()->create();
        $serviceMaster = $this->makeServiceMaster($shop, $master);

        $otherShop = $this->makeShop();
        $otherShopLocation = $this->makeLocation($otherShop);

        try {
            $shopId = null;
            (new BookingService)->beforeSave(
                $this->bookingItem($serviceMaster, $otherShopLocation->id),
                1,
                $shopId
            );
            $this->fail('Expected a location belonging to a different shop to be rejected.');
        } catch (Exception $e) {
            $this->assertStringContainsString(
                __('errors.' . ResponseError::OTHER_LOCATION),
                $e->getMessage()
            );
        }
    }

    public function test_single_location_shop_auto_resolves_when_shop_location_id_omitted(): void
    {
        $shop = $this->makeShop();
        $location = $this->makeLocation($shop);
        $master = User::factory()->create();
        $serviceMaster = $this->makeServiceMaster($shop, $master);

        $shopId = null;
        $result = (new BookingService)->beforeSave(
            $this->bookingItem($serviceMaster, null),
            1,
            $shopId
        );

        $this->assertSame($location->id, $result['shop_location_id']);
    }

    public function test_master_assigned_to_exactly_one_of_several_locations_auto_resolves(): void
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

        $shopId = null;
        $result = (new BookingService)->beforeSave(
            $this->bookingItem($serviceMaster, null),
            1,
            $shopId
        );

        $this->assertSame($locationA->id, $result['shop_location_id']);
    }

    public function test_ambiguous_case_is_rejected_for_unrestricted_master_at_multi_branch_shop(): void
    {
        $shop = $this->makeShop();
        $this->makeLocation($shop);
        $this->makeLocation($shop);
        $master = User::factory()->create();
        $serviceMaster = $this->makeServiceMaster($shop, $master);

        try {
            $shopId = null;
            (new BookingService)->beforeSave(
                $this->bookingItem($serviceMaster, null),
                1,
                $shopId
            );
            $this->fail('Expected an unrestricted master at a multi-branch shop with no shop_location_id to be ambiguous.');
        } catch (Exception $e) {
            $this->assertStringContainsString(
                __('errors.' . ResponseError::LOCATION_AMBIGUOUS),
                $e->getMessage()
            );
        }
    }

    public function test_ambiguous_case_is_rejected_for_master_assigned_to_multiple_locations(): void
    {
        $shop = $this->makeShop();
        $locationA = $this->makeLocation($shop);
        $locationB = $this->makeLocation($shop);
        $master = User::factory()->create();
        $serviceMaster = $this->makeServiceMaster($shop, $master);

        $invitation = Invitation::query()->create([
            'shop_id' => $shop->id,
            'user_id' => $master->id,
            'status' => Invitation::ACCEPTED,
        ]);
        $invitation->shopLocations()->sync([$locationA->id, $locationB->id]);

        try {
            $shopId = null;
            (new BookingService)->beforeSave(
                $this->bookingItem($serviceMaster, null),
                1,
                $shopId
            );
            $this->fail('Expected a master assigned to multiple locations with no shop_location_id to be ambiguous.');
        } catch (Exception $e) {
            $this->assertStringContainsString(
                __('errors.' . ResponseError::LOCATION_AMBIGUOUS),
                $e->getMessage()
            );
        }
    }
}
