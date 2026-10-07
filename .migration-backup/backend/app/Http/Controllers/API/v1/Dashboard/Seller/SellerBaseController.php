<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\User;
use App\Traits\ApiResponse;

abstract class SellerBaseController extends Controller
{
    use ApiResponse;

    protected Shop|null $shop;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('check.shop')
            ->except('shopCreate', 'shopShow', 'shopUpdate');

        /** @var User $user */
        $user = auth('sanctum')->user();
        
        $this->shop = $user?->shop ?? $user?->moderatorShop;
    }

    /**
     * Seller-side reads of branch-specific records are limited to the
     * authenticated user's accepted branch assignments. Shop owners retain
     * their existing all-branch access.
     *
     * @return array{unrestricted: bool, location_ids: int[]}
     */
    protected function shopLocationBranchScope(): array
    {
        $user = auth('sanctum')->user();

        return $user && $this->shop
            ? $user->shopLocationBranchScope((int) $this->shop->id)
            : ['unrestricted' => false, 'location_ids' => []];
    }

    /**
     * Master records without explicit branch assignments are shop-wide;
     * otherwise only masters sharing one of the seller's assigned branches
     * are visible.
     *
     * @return int[]|null Null means the shop owner is unrestricted.
     */
    protected function readableShopMasterIds(): ?array
    {
        $scope = $this->shopLocationBranchScope();

        if ($scope['unrestricted']) {
            return null;
        }

        if (!$this->shop || $scope['location_ids'] === []) {
            return [];
        }

        return User::query()
            ->availableAtShopLocations((int) $this->shop->id, $scope['location_ids'])
            ->pluck('users.id')
            ->map(static fn ($id): int => (int) $id)
            ->all();
    }

    protected function canReadShopMaster(int $userId): bool
    {
        $readableMasterIds = $this->readableShopMasterIds();

        return $readableMasterIds === null || in_array($userId, $readableMasterIds, true);
    }

    protected function assertRequestedShop(\Illuminate\Http\Request $request): void
    {
        if ($request->has('shop_id') && (!is_scalar($request->input('shop_id'))
            || !ctype_digit((string)$request->input('shop_id'))
            || (int)$request->input('shop_id') !== (int)$this->shop?->id)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'shop_id'=>['The requested business is not your authorized Shop context.'],
            ]);
        }
    }

}
