<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Dashboard\Master;

use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\ShopLocationResource;
use App\Models\Invitation;
use App\Models\User;
use App\Repositories\ShopLocationRepository\ShopLocationRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Read-only: a master needs to see their own shop's branches to pick one
 * when creating a booking at a multi-branch shop (BookingService::
 * resolveBookingLocation() requires shop_location_id once ambiguous), but
 * has no reason to manage locations themselves - that stays a seller/admin
 * capability (Seller\ShopLocationController / Admin\ShopLocationController).
 */
class ShopLocationController extends MasterBaseController
{
    private ?int $shopId;

    public function __construct(private ShopLocationRepository $repository)
    {
        parent::__construct();

        /** @var User $user */
        $user = auth('sanctum')->user();

        $this->shopId = $user
            ?->invitations
            ?->where('status', Invitation::ACCEPTED)
            ?->pluck('shop_id')
            ?->first();
    }

    /**
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $shopLocations = $this->repository->paginate(
            $request->merge(['shop_id' => $this->shopId])->all()
        );

        return ShopLocationResource::collection($shopLocations);
    }
}
