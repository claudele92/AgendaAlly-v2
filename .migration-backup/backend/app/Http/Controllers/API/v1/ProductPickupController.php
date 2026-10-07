<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\ShopPickupPolicy;
use App\Services\ProductPickupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductPickupController extends Controller
{
    private function authorized(int $shopId, bool $write = false): Shop
    {
        $shop = Shop::findOrFail($shopId);
        $actor = auth('sanctum')->user();
        abort_unless($actor && ($actor->isSuperAdmin() || $actor->hasShopPermission(
            $shopId, $write ? 'shop_settings.manage' : 'shop_settings.view'
        )), 403);
        return $shop;
    }

    public function available(Request $request, int $shop)
    {
        $input = $request->validate(['shop_location_id' => 'nullable|integer|min:1', 'date' => 'nullable|date_format:Y-m-d']);
        $model = Shop::where('status', 'approved')->where('visibility', true)->findOrFail($shop);
        return response()->json(['status' => true, 'data' => (new ProductPickupService)->availability(
            $model, isset($input['shop_location_id']) ? (int) $input['shop_location_id'] : null, $input['date'] ?? null
        )]);
    }

    public function show(int $shop, int $location)
    {
        $this->authorized($shop);
        $service = new ProductPickupService;
        $service->location($shop, $location);
        return response()->json(['status' => true, 'data' => $service->policy($shop, $location)]);
    }

    public function save(Request $request, int $shop, int $location)
    {
        $model = $this->authorized($shop, true);
        abort_unless(in_array(\App\Models\Order::PICKUP, $model->productFulfillmentMethods(), true), 422, 'Enable Shop Pickup before configuring its schedule.');
        $service = new ProductPickupService;
        $values = $service->validatePolicy($request->all());
        DB::transaction(function () use ($shop, $location, $values, $service, $model) {
            $branch = $service->location($shop, $location, true);
            if ($values['timing_mode'] === ProductPickupService::SCHEDULED) {
                $count = \App\Models\ShopLocation::where('shop_id', $shop)
                    ->where('type', \App\Models\ShopLocation::PRODUCT)->count();
                if (!$service->pickupAddress($model, $branch, $count)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'shop_location_id' => 'Set an authoritative address for this Product location before scheduling pickup.',
                    ]);
                }
            }
            return ShopPickupPolicy::updateOrCreate(['shop_id' => $shop, 'shop_location_id' => $location], $values);
        });
        return response()->json(['status' => true, 'data' => $service->policy($shop, $location)]);
    }

    public function destroy(int $shop, int $location)
    {
        $this->authorized($shop, true);
        $service = new ProductPickupService;
        DB::transaction(function () use ($service, $shop, $location) {
            $service->location($shop, $location, true);
            ShopPickupPolicy::where('shop_id', $shop)->where('shop_location_id', $location)->delete();
        });
        return response()->json(['status' => true, 'data' => $service->defaultPolicy()]);
    }
}