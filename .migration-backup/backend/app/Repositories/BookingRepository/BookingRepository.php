<?php
declare(strict_types=1);

namespace App\Repositories\BookingRepository;

use App\Helpers\OrderHelper;
use App\Helpers\ResponseError;
use App\Helpers\Utility;
use App\Http\Resources\CurrencyResource;
use App\Http\Resources\ServiceExtraResource;
use App\Http\Resources\ServiceMasterResource;
use App\Http\Resources\ShopResource;
use App\Http\Resources\UserResource;
use App\Models\Booking;
use App\Models\Currency;
use App\Models\Language;
use App\Models\MemberShip;
use App\Models\ServiceExtra;
use App\Models\ServiceMaster;
use App\Models\ServiceMasterPrice;
use App\Models\Settings;
use App\Models\Shop;
use App\Models\ShopLocation;
use App\Models\SellerBookingClient;
use App\Models\User;
use App\Models\UserGiftCart;
use App\Models\UserMemberShip;
use App\Repositories\CoreRepository;
use App\Repositories\UserRepository\MasterRepository;
use DateInterval;
use DateTime;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Schema;
use Throwable;

class BookingRepository extends CoreRepository
{
    protected function getModelClass(): string
    {
        return Booking::class;
    }

    public function getWith(): array
    {
        return [
            'master:id,uuid,firstname,lastname,img,email,phone,r_count,r_avg,r_sum,o_count,o_sum,b_count,b_sum',
            'user:id,uuid,firstname,lastname,img,email,phone',
            'localClient:id,shop_id,shop_location_id,name,phone,email',
            'serviceMaster.service.translation' => fn($query) => $query
                ->where('locale', $this->language),
            'shop:id,uuid,slug,logo_img,user_id,latitude,longitude,o_count,b_count,verify',
            'shop.translation' => fn($query) => $query
                ->where('locale', $this->language),
            'shopLocation.city.translation' => fn($query) => $query
                ->where('locale', $this->language),
            'shopLocation.country.translation' => fn($query) => $query
                ->where('locale', $this->language),
            'userMemberShip',
            'extras.translation' => fn($query) => $query
                ->where('locale', $this->language),
            'currency',
            'activities.user:id,img,firstname,lastname',
            'extraTimes',
            'children:id,parent_id,discount,commission_fee,price,total_price,service_fee,rate,status',
            'children.review',
            'children.activities',
            'children.extraTimes',
            // BookingResource exposes both the singular 'transaction' (a
            // MorphOne) and the plural 'transactions' (a MorphMany) -
            // only the plural's paymentSystem was eager-loaded here, so
            // any consumer reading booking.transaction.payment_system.tag
            // (e.g. the "Pay" button and payment-method-aware labels in
            // booking-detail.tsx) was silently falling back to a lazy
            // per-request load instead of using eager-loaded data.
            'transaction.paymentSystem',
            'transactions.paymentSystem',
            'transactions.children',
            'children.transactions.paymentSystem',
            'children.transactions.children',
        ];
    }

    /**
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function paginate(array $filter = []): LengthAwarePaginator
    {
        $column = $filter['column'] ?? 'id';

        if ($column !== 'id') {
            $column = Schema::hasColumn('bookings', $column) ? $column : 'id';
        }

        return Booking::filter($filter)
            ->with($this->getWith())
            ->orderBy($column, $filter['sort'] ?? 'desc')
            ->paginate($filter['perPage'] ?? 10);
    }

    /**
     * @param Booking $booking
     * @return Booking
     */
    public function show(Booking $booking): Booking
    {
        return $booking->fresh($this->getWith());
    }

    /**
     * @param int $id
     * @param int|null $userId
     * @param int|null $shopId
     * @param int|null $masterId
     * @return Collection|null
     */
    public function bookingsByParentId(
        int $id,
        ?int $userId   = null,
        ?int $shopId   = null,
        ?int $masterId = null
    ): ?Collection
    {
        return $this->model()
            ->with($this->getWith())
            ->when($userId,   fn($q) => $q->where('user_id',   $userId))
            ->when($shopId,   fn($q) => $q->where('shop_id',   $shopId))
            ->when($masterId, fn($q) => $q->where('master_id', $masterId))
            ->where(fn($q) => $q->where('id', $id)->orWhere('parent_id', $id))
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * @param array $data
     * @return array
     */
    public function calculate(array $data = []): array
    {
        try {
            return $this->prepareCalculate($data);
        } catch (Throwable $e) {
            return [
                'status'  => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * @param array $data
     * @return array
     * @throws Throwable
     */
    private function permittedPreviewIds(array $ids): array
    {
        if (!$ids || !auth('sanctum')->check()) {
            return [];
        }
        $user = auth('sanctum')->user();
        $query = Booking::filter([])->whereIn('id', $ids);
        if (request()->is('api/v1/dashboard/admin/*') && $user->hasRole('admin')) {
            return $query->pluck('id')->all();
        }
        if (request()->is('api/v1/dashboard/seller/*') && $user->hasRole('seller')) {
            return $query->whereHas('shop', fn($q) => $q->where('user_id', $user->id))->pluck('id')->all();
        }
        if (request()->is('api/v1/dashboard/master/*') && $user->hasRole('master')) {
            return $query->where('master_id', $user->id)->pluck('id')->all();
        }
        if (request()->is('api/v1/dashboard/user/*')) {
            return $query->where('user_id', $user->id)->pluck('id')->all();
        }
        return [];
    }

    private function prepareCalculate(array $data = []): array
    {
        if (!empty($data['local_client_id'])) {
            if (!request()->is('api/v1/dashboard/seller/bookings*')) {
                throw new Exception('Local booking clients are supported only by the authorized Vendor calendar workflow.');
            }

            $localClient = SellerBookingClient::query()
                ->where('id', $data['local_client_id'])
                ->where('shop_id', $data['shop_id'] ?? null)
                ->first();
            if (!$localClient) {
                throw new Exception(__('errors.' . ResponseError::ERROR_400, locale: $this->language));
            }
            $data['user_id'] = null;
            unset(
                $data['payment_id'],
                $data['user_member_ship_id'],
                $data['user_gift_cart_id'],
                $data['coupon'],
                $data['from_wallet_price'],
                $data['transaction_status'],
                $data['trx_status']
            );
        } elseif (!isset($data['user_id'])) {
            $data['user_id'] = auth('sanctum')->id();
            $localClient = null;
        } else {
            $localClient = null;
        }

        $defaultCurrency = Currency::currenciesList()->where('active', 1)->where('default', 1)->first();
        $rate            = $defaultCurrency?->rate ?: 1;
        $currencyId      = $defaultCurrency?->id;
        $currency        = $defaultCurrency;

        if (request()->is('api/v1/dashboard/user/*') || request()->is('api/v1/rest/*')) {
            // Customer-facing checkout: currency/rate come from the shop's
            // own country, never from the request or platform default.
            $firstServiceMasterId = data_get($data, 'data.0.service_master_id');
            $shopIdForCurrency    = ServiceMaster::find($firstServiceMasterId)?->shop_id;
            $country              = $shopIdForCurrency
                ? Shop::find($shopIdForCurrency)?->checkoutCountry(ShopLocation::SERVICE)
                : null;

            if (!$country) {
                throw new Exception(__('errors.' . ResponseError::ERROR_400, locale: $this->language));
            }

            $rate       = $country->currency->rate ?: 1;
            $currencyId = $country->currency_id;
            $currency   = $country->currency;
        }

        $user       = User::select(['id', 'firstname', 'lastname'])->find($data['user_id']);

        $items = [];

        $giftCartPrice = 0;

        if (isset($data['user_gift_cart_id'])) {

            $giftCartPrice = UserGiftCart::where('user_id', $data['user_id'])
                ->where('id', $data['user_gift_cart_id'])
                ->where('expired_at', '>=', date('Y-m-d H:i:s'))
                ->first()
                ?->price * $rate;

            if (empty($giftCartPrice)) {
                throw new Exception(__('errors.' . ResponseError::ERROR_511, locale: $this->language));
            }

        }

        foreach ($data['data'] as $key => $value) {

            $startDate = new DateTime($value['start_date']);
            $startDateFormat = $startDate->format('Y-m-d');
            $endDate = new DateTime($value['start_date']);

            if (request()->is('api/v1/dashboard/user/*') || request()->is('api/v1/rest/*')) {
                if (!isset($data['ids']) && $startDate < now() || $endDate < $startDate) {
                    throw new Exception(__('errors.' . ResponseError::ERROR_509, locale: $this->language));
                }
            }

            $serviceMaster = ServiceMaster::with([
                'master:id,firstname,lastname',
                'shop:id,uuid,slug,logo_img',
                'shop.translation' => fn($query) => $query
                    ->where('locale', $this->language),
                'service:id,slug',
                'service.translation' => fn($query) => $query
                    ->where('locale', $this->language),
            ])->find($value['service_master_id']);

            if (empty($serviceMaster)) {
                continue;
            }

            /** @var ServiceMaster $serviceMaster */
            $time = $serviceMaster->interval + $serviceMaster->pause;

            if ($key > 0) {

//                try {
//                    $startDate = new DateTime($items[$key - 1]['end_date']);
//                } catch (Exception $e) {
//                    return [
//                        'status'  => false,
//                        'message' => $e->getMessage()
//                    ]; //  $e->getMessage()
//                }

                $endDate = clone $startDate;
            }

            $endDate = $endDate->add(new DateInterval("PT{$time}M"));

            $value['end_date'] = $endDate->format('Y-m-d');

            $extras = collect();

            if (data_get($value, 'service_extras.0')) {
                $extras = ServiceExtra::with([
                    'translation' => fn($query) => $query
                        ->where('locale', $this->language),
                ])->find($value['service_extras']);
            }

            $extraPrice = $extras->sum('price') * $rate;

            $shop   = $serviceMaster->shop;
            $master = $serviceMaster->master;

            $items[$key]['service_master'] = ServiceMasterResource::make($serviceMaster);
            $items[$key]['master'] = $master ? UserResource::make($master) : null;
            $items[$key]['shop'] = $shop ? ShopResource::make($shop) : null;
            $items[$key]['user'] = $user ? UserResource::make($user) : null;
            $items[$key]['local_client'] = $localClient
                ? \App\Http\Resources\SellerBookingClientResource::make($localClient)
                : null;

            $totalPrice = $serviceMaster->total_price * $rate;

            $from = $startDate->format('H:i');
            $to   = $endDate->format('H:i');

            if (isset($value['price_id'])) {

                $serviceMasterPrice = ServiceMasterPrice::where('service_master_id', $serviceMaster->id)
                    ->find($value['price_id']);

                $totalPrice = $serviceMasterPrice?->price;

                $smart = collect($serviceMasterPrice?->smart)
                    ->filter(fn($item) => $from >= $item['from'] && $to <= $item['to'])
                    ->first();

                if (!empty($smart)) {

                    $smartValue = (float)$smart['value'];

                    if ($smart['value_type'] === ServiceMasterPrice::PERCENT) {
                        $smartValue = max($totalPrice / 100 * $smartValue, 0);
                    }

                    $totalPrice = $smart['type'] === ServiceMasterPrice::SMART_TYPE_UP
                        ? $totalPrice + $smartValue
                        : $totalPrice - $smartValue;

                }

                $totalPrice = max($totalPrice, 0) * $rate;

            }

            $data['shop_id'] = $serviceMaster->shop_id;

            // Computed per item, against that item's own (already
            // rate-converted) pre-extras price - the same base
            // commission_fee already uses - not once for the whole
            // request, so a multi-item booking charges this fee per
            // booked service, matching how each item becomes its own
            // Booking row (and its own frozen service_fee) in
            // BookingService::beforeSave().
            $serviceFee = Utility::resolveServiceFee('booking_service_fee', $totalPrice, $rate);

            $totalPrice += $extraPrice;

            $items[$key]['extras']              = ServiceExtraResource::collection($extras);
            $items[$key]['service_master_id']   = $serviceMaster->id;
            $items[$key]['start_date']          = $startDate->format('Y-m-d H:i');
            $items[$key]['end_date']            = $endDate->format('Y-m-d H:i');
            $items[$key]['price']               = $serviceMaster->total_price * $rate;
            $items[$key]['discount']            = $serviceMaster->discount * $rate;
            $items[$key]['service_fee']         = $serviceFee;
            $items[$key]['commission_fee']      = $serviceMaster->commission_fee * $rate;
            $items[$key]['extra_price']         = $extraPrice;
            $items[$key]['note']                = $value['note'] ?? '';
            $items[$key]['data']                = $value['data'] ?? [];
            $items[$key]['gender']              = $value['gender'] ?? '';
            $items[$key]['notes']               = $value['notes'] ?? [];
            $items[$key]['user_member_ship_id'] = $value['user_member_ship_id'] ?? null;
            $items[$key]['total_price']         = $totalPrice;

            $this->userMemberShipCalculate($serviceMaster, $data, $key, $items);

            try {

                \App\Services\BookingService\BookingCapacity::assertWindow(
                    $serviceMaster->id,
                    $items[$key]['start_date'],
                    $items[$key]['end_date'],
                    $this->permittedPreviewIds($data['ids'] ?? [])
                );

            } catch (Throwable $e) {
                $items[$key]['errors'][] = $e->getMessage();
            }

        }

        $status        = true;
        $price         = 0;
        $discount      = 0;
        $totalPrice    = collect($items)->sum('total_price');
        $extraPrice    = collect($items)->sum('extra_price');
        $serviceFee    = 0;
        $couponPrice   = 0;
        $commissionFee = 0;
        $giftPrice     = 0;
        $decrementGiftPrice = 0;
        $message       = '';

        if ($giftCartPrice > 0 && $totalPrice > 0) {
            $giftPrice = $giftCartPrice / count($items);
        }

        foreach (collect($items)->sortBy('total_price')->toArray() as $key => $item) {

            $price         += $item['price']          ?? 0;
            $discount      += $item['discount']       ?? 0;
            $serviceFee    += $item['service_fee']    ?? 0;
            $commissionFee += $item['commission_fee'] ?? 0;

            if (isset($item['errors'])) {
                $status = false;
                $message = @value($item['errors'])[0];
            }

            if ($items[$key]['total_price'] == 0) {
                continue;
            }

            if ($giftCartPrice >= $items[$key]['total_price']) {

                $decrementGiftPrice += $items[$key]['total_price'];

                $items[$key]['gift_cart_price'] = $items[$key]['total_price'];
                $items[$key]['total_price'] = 0;

            } elseif ($giftPrice > 0) {

                $decrementPrice = $giftPrice;

                if ($giftPrice > $item['total_price']) {
                    $giftPrice      = ($giftPrice - $item['total_price']);
                    $decrementPrice = $item['total_price'];
                }

                $items[$key]['gift_cart_price'] = $decrementPrice;
                $items[$key]['total_price']     = $item['total_price'] - $decrementPrice;
                $decrementGiftPrice += $decrementPrice;

            }

        }

        if (isset($data['coupon']) && $totalPrice > 0) {
            $couponPrice = OrderHelper::couponPrice($data, $data['coupon'], $totalPrice, $rate, $data['shop_id']);
            $totalPrice -= $couponPrice;
        }

        $totalPrice -= $decrementGiftPrice;

        return [
            'status'                => $status,
            'message'               => $message,
            'user_id'               => $data['user_id'],
            'local_client_id'       => $data['local_client_id'] ?? null,
            'shop_id'               => $data['shop_id'],
            'user_gift_cart_id'     => $data['user_gift_cart_id'] ?? 0,
            'rate'                  => $rate,
            'currency_id'           => $currencyId,
            'currency'              => CurrencyResource::make($currency),
            'price'                 => $price + $discount,
            'total_price'           => max($totalPrice + $serviceFee, 0),
            'total_extra_price'     => max($extraPrice, 0),
            'coupon_price'          => $couponPrice,
            'total_discount'        => $discount,
            'total_service_fee'     => $serviceFee,
            'total_commission_fee'  => $commissionFee,
            'total_gift_cart_price' => $decrementGiftPrice,
            'items'                 => $items,
        ];
    }

    /**
     * @param ServiceMaster|ServiceMasterResource $serviceMaster
     * @param array $data
     * @param int $key
     * @param array $items
     * @return UserMemberShip|null
     */
    public function userMemberShipCalculate(
        ServiceMaster|ServiceMasterResource $serviceMaster,
        array $data,
        int $key,
        array &$items,
    ): ?UserMemberShip
    {
        $userMemberShip = UserMemberShip::with(['memberShipServices'])
            ->where([
                ['id',         '=', $items[$key]['user_member_ship_id'] ?? null],
                ['user_id',    '=', $data['user_id'] ?? auth('sanctum')->id()],
                ['expired_at', '>', date('Y-m-d H:i:s')],
            ])
            ->first();

        if (empty($userMemberShip)) {
            return null;
        }

        /** @var UserMemberShip $userMemberShip */
        $memberService = $userMemberShip->memberShipServices
            ?->where('service_id', $serviceMaster->service_id)
            ?->first();

        $isLimited = $userMemberShip->sessions === MemberShip::LIMITED && $userMemberShip->remainder > 0;

        if (!empty($memberService) && ($isLimited || $userMemberShip->sessions === MemberShip::UNLIMITED)) {

            $items[$key]['user_member_ship_id'] = $userMemberShip->id;
            $items[$key]['price']               = 0;
            $items[$key]['discount']            = 0;
            $items[$key]['service_fee']         = 0;
            $items[$key]['commission_fee']      = 0;
            $items[$key]['extra_price']         = 0;
            $items[$key]['total_price']         = 0;

        }

        return $userMemberShip;
    }
}
