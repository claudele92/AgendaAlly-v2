<?php
declare(strict_types=1);
namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Models\Invitation;
use App\Models\ServiceMaster;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Builder;

/** Operational lookup, not the public marketplace directory or a staff directory. */
final class BookingMasterController extends SellerBaseController
{
    private function assignments(Request $request): Builder
    {
        $this->assertRequestedShop($request);
        abort_unless($this->shop && auth('sanctum')->user()
            ?->hasShopPermission((int)$this->shop->id, 'bookings.view'), 403);
        $shopId=(int)$this->shop->id;
        $query=ServiceMaster::query()->where('shop_id',$shopId)->where('active',true)
            ->whereHas('service',fn($q)=>$q->where('shop_id',$shopId))
            ->whereHas('master',fn($q)=>$q->where('active',true)->whereHas('invitations',fn($i)=>
                $i->where('shop_id',$shopId)->where('role','master')->where('status',Invitation::ACCEPTED)))
            ->when($request->filled('service_id'),fn($q)=>$q->where('service_id',$request->integer('service_id')));
        $ids=$this->readableShopMasterIds();
        if($ids!==null)$query->whereIn('master_id',$ids);
        if($request->filled('shop_location_id')) {
            $location=$request->integer('shop_location_id');
            $scope=$this->shopLocationBranchScope();
            abort_unless(\App\Models\ShopLocation::query()->whereKey($location)->where('shop_id',$shopId)
                ->where('type',\App\Models\ShopLocation::SERVICE)->exists()
                &&($scope['unrestricted']||in_array($location,$scope['location_ids'],true)),404);
            $query->whereIn('master_id',User::query()->availableAtShopLocations($shopId,[$location])->select('users.id'));
        }
        return $query;
    }

    private function projection(User $master, $assignments): array
    {
        return [
            'id'=>$master->id,'firstname'=>$master->firstname,'lastname'=>$master->lastname,'img'=>$master->img,
            // Only this Shop's assignments. No contact details, wallets,
            // invitations, private profile data or another Shop's assignments.
            'service_masters'=>$assignments->map(fn($a)=>$a->only([
                'id','shop_id','service_id','master_id','active','interval','price','type','currency_id'
            ]))->values()->all(),
        ];
    }

    public function index(Request $request): JsonResponse
    {
        $assignments=$this->assignments($request)->get()->groupBy('master_id');
        $masters=User::query()->whereIn('id',$assignments->keys())
            ->when($request->filled('search'),fn($q)=>$q->where(fn($s)=>$s
                ->where('firstname','like','%'.mb_substr($request->string('search')->toString(),0,100).'%')
                ->orWhere('lastname','like','%'.mb_substr($request->string('search')->toString(),0,100).'%')))
            ->orderBy('firstname')->paginate(max(1,min($request->integer('perPage',10),100)));
        return response()->json(['data'=>$masters->getCollection()->map(fn($m)=>$this->projection($m,$assignments[$m->id])),
            'meta'=>['current_page'=>$masters->currentPage(),'per_page'=>$masters->perPage(),'total'=>$masters->total()]]);
    }

    public function show(int $master, Request $request): JsonResponse
    {
        $assignments=$this->assignments($request)->where('master_id',$master)->get();
        abort_if($assignments->isEmpty(),404);
        return response()->json(['data'=>$this->projection(User::findOrFail($master),$assignments)]);
    }
}