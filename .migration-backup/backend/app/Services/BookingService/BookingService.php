<?php
declare(strict_types=1);

namespace App\Services\BookingService;

use DB;
use DateTime;
use Exception;
use Throwable;
use DateInterval;
use App\Models\Shop;
use App\Models\User;
use App\Models\Point;
use App\Models\Payment;
use App\Models\Booking;
use App\Helpers\Utility;
use App\Models\Settings;
use App\Models\Invitation;
use App\Models\MemberShip;
use App\Models\ShopLocation;
use App\Models\PlatformFeeLedgerEntry;
use App\Models\Transaction;
use App\Helpers\OrderHelper;
use App\Traits\Notification;
use App\Models\UserGiftCart;
use App\Models\PointHistory;
use App\Models\ServiceExtra;
use App\Services\CoreService;
use App\Models\BookingCoupon;
use App\Models\ServiceMaster;
use App\Models\UserMemberShip;
use App\Helpers\ResponseError;
use App\Models\BookingExtraTime;
use App\Models\ShopSubscription;
use App\Services\PaymentService\BaseService;
use App\Http\Resources\ServiceMasterResource;
use App\Services\TransactionService\TransactionService;
use App\Repositories\BookingRepository\BookingRepository;
use App\Exceptions\LocationAmbiguousException;
use Illuminate\Support\Collection;

class BookingService extends CoreService
{
    use Notification;
    private bool $capacityGroupOwned = false;

    protected function getModelClass(): string
    {
        return Booking::class;
    }

    /**
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        $calculate = [];
        // IDs are authorized preview exclusions, never creation authority.
        unset($data['ids']);

        try {
            if (!empty($data['local_client_id'])) {
                if (!request()->is('api/v1/dashboard/seller/bookings*')) {
                    throw new Exception('Local booking clients are supported only by the authorized Vendor calendar workflow.');
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
                $data['data'] = array_map(function (array $item): array {
                    unset($item['user_member_ship_id'], $item['user_gift_cart_id']);
                    return $item;
                }, $data['data'] ?? []);
            }

            $models = BookingCapacity::transaction(function () use ($data, $calculate) {
                BookingCapacity::lock(array_column($data['data'] ?? [], 'service_master_id'));

                $calculate = (new BookingRepository)->calculate($data);

                if (!data_get($calculate, 'status')) {
                    throw new Exception(
                        data_get(
                            $calculate,
                            'message',
                            __('errors.' . ResponseError::ERROR_400, locale: $this->language)
                        )
                    );
                }

                $pending = [];
                $shops = [];
                foreach ($calculate['items'] as $item) {
                    $masterId = (int) $item['service_master']->master_id;
                    $shops[] = (int) $item['service_master']->shop_id;
                    foreach ($pending as $previous) {
                        if ($previous['master'] === $masterId
                            && $item['start_date'] < $previous['end']
                            && $item['end_date'] > $previous['start']) {
                            throw new \DomainException('Services in this request overlap for the same specialist.');
                        }
                    }
                    $pending[] = ['master' => $masterId, 'start' => $item['start_date'], 'end' => $item['end_date']];
                }
                if (count(array_unique($shops)) !== 1) {
                    throw new \DomainException('Multi-Shop booking checkout is not supported.');
                }

                // Resolved from the shop's own country by BookingRepository::calculate()
                // for customer-facing requests — never from the request/platform default.
                $rate       = $calculate['rate'] ?: 1;
                $currencyId = $calculate['currency_id'] ?? null;

                $models    = [];
                $parentId  = null;
                $shopId    = $calculate['shop_id'] ?? null;
                $coupon    = null;

                $calculate['total_price'] = $calculate['total_price'] / $calculate['rate'];

                if (isset($data['coupon'])) {
                    $coupon = OrderHelper::couponPrice($data, $data['coupon'], $calculate['total_price'], $calculate['rate'], $shopId);
                }

                if (isset($calculate['total_gift_cart_price']) && isset($data['user_gift_cart_id'])) {
                    $this->updateGiftCartPrice($calculate);
                }

                $items = collect($calculate['items']);

                if (isset($data['from_wallet_price'])) {
                    $data['from_wallet_price'] /= count($data['data']);
                }

                $accountingCheckout = (string) \Illuminate\Support\Str::uuid();
                foreach ($data['data'] as $itemIndex => $item) {
                    if (\App\Services\PaymentAccounting\NativePaymentAccounting::installed()
                        && (!empty($item['user_member_ship_id']) || !empty($data['user_gift_cart_id']))) {
                        throw new \DomainException('Gift/membership sponsorship requires a proven original economic contract.');
                    }

                    /** @var ServiceMaster|null $serviceMaster */
                    $calculateValue = $items->get($itemIndex);
                    $serviceMaster  = @$calculateValue['service_master'];

                    if (empty($calculateValue)) {
                        throw new Exception(__('errors.' . ResponseError::ERROR_400, locale: $this->language));
                    }

                    if (
                        $calculateValue['end_date'] < $calculateValue['start_date']
                        || $calculateValue['start_date'] < now()->format('Y-m-d H:i')
                    ) {
                        throw new Exception(__('errors.' . ResponseError::ERROR_509, locale: $this->language));
                    }

                    $item = $this->beforeSave($item, $rate, $shopId);

                    $couponPrice = $coupon > 0 ? ($coupon / count($data['data']) / $rate) : 0;

                    $item['parent_id']         = $parentId;
                    $item['user_id']           = !empty($data['local_client_id']) ? null : $data['user_id'];
                    $item['local_client_id']   = $data['local_client_id'] ?? null;
                    $item['currency_id']       = $currencyId;
                    $item['user_gift_cart_id'] = $data['user_gift_cart_id'] ?? null;
                    $item['start_date']        = $calculateValue['start_date'];
                    $item['end_date']          = $calculateValue['end_date'];
                    $item['coupon_price']      = $couponPrice;
                    $item['extra_price']       = @($calculateValue['extra_price'] / $rate) ?? 0;
                    $item['gift_cart_price']   = @($calculateValue['gift_cart_price'] / $rate) ?? 0;
                    $item['total_price']       = @((($calculateValue['total_price'] + $calculateValue['service_fee'])) / $rate) - $couponPrice ?? 0;
                    $item                      = $this->updateMemberShip($serviceMaster, $data, $item);

                    /** @var Booking $model */
                    $model = $this->model()->create($item);

                    if (\App\Services\PaymentAccounting\NativePaymentAccounting::installed()) {
                        (new \App\Services\PaymentAccounting\NativeQuoteFactory)->commitNew($model, $accountingCheckout);
                    }

                    if ($couponPrice) {
                        BookingCoupon::create([
                            'name'       => $data['coupon'],
                            'booking_id' => $model->id,
                            'user_id'    => $model->user_id,
                            'price'      => $couponPrice,
                        ]);
                    }

                    if (empty($parentId)) {
                        $parentId = $model->id;
                    }

                    foreach ($calculateValue['extras'] ?? [] as $extra) {
                        if (\App\Services\PaymentAccounting\NativePaymentAccounting::installed()
                            && ((int) $extra->service_id !== (int) $model->service_id
                                || ($extra->shop_id !== null && (int) $extra->shop_id !== (int) $model->shop_id))) {
                            throw new \DomainException('Service extra must belong to the quoted native Service/Shop.');
                        }
                        $model->extras()->create(['price' => $extra->price, 'service_extra_id' => $extra->id]);
                    }

                    if (data_get($data, 'payment_id') && !data_get($data, 'trx_status')) {

                        /** @var User $user */
                        $user = User::with('wallet')->find($model->user_id);

                        $totalPrice  = $model->total_price;
                        $totalPrice -= (new BaseService)->walletPriceWithdraw($model, $data, $user);

                        $payment = Payment::find($data['payment_id']);
                        if ($payment?->tag === 'wallet' && \App\Services\PaymentAccounting\NativePaymentAccounting::installed()) {
                            (new \App\Services\PaymentAccounting\NativePaymentAccounting)->prepare($model, $payment, (string) $totalPrice);
                            $user->load('wallet');
                        }

                        $transaction = $model->createTransaction([
                            'price'              => $totalPrice,
                            'user_id'            => $model->user_id,
                            'payment_sys_id'     => $data['payment_id'],
                            'payment_trx_id'     => null,
                            'note'               => $model->id,
                            'perform_time'       => now(),
                            'status_description' => "Transaction for booking #$model->id",
                            'request'            => null,
                        ]);

                        if ($payment->tag === 'wallet') {

                            if (\App\Services\PaymentAccounting\NativePaymentAccounting::installed()) {
                                (new \App\Services\PaymentAccounting\NativePaymentAccounting)->prepare($model, $payment, (string) $totalPrice);
                            }
                            if (!$user->wallet) throw new \DomainException('Owned Wallet is unavailable.', 109);
                            \App\Services\WalletHistoryService\WalletDebit::debit(
                                $user->wallet, $totalPrice, (int) $user->id
                            );

                            (new TransactionService)->walletHistoryAdd($model->user, $transaction, $model, 'Booking', 'withdraw');
                        }

                    }

                    $model = $model->fresh((new BookingRepository)->getWith());

                    $models[] = $model;

                    $isSubscribe = (int)Settings::where('key', 'by_subscription')->first()?->value;

                    if ($isSubscribe) {

                        /** @var ShopSubscription $subscription */
                        $subscription = ShopSubscription::with(['subscription', 'shop'])
                            ->where('shop_id', $model->shop_id)
                            ->where('expired_at', '>=', now())
                            ->where('active', true)
                            ->first();

                        if (empty($subscription)) {
                            Shop::firstWhere('id', $model->shop_id)?->update([
                                'visibility' => 0
                            ]);
                        } else {
                            $shopDemandCount = $model->shop?->b_count;

                            if ($subscription->subscription?->booking_limit <= $shopDemandCount) {
                                $subscription->shop?->update([
                                    'visibility' => 0
                                ]);
                            }
                        }

                    }

                }

                $this->sendAllBooking($models);

                return $models;
            });

            return [
                'status'  => true,
                'message' => ResponseError::NO_ERROR,
                'data'    => $models,
            ];
        } catch (LocationAmbiguousException $e) {

            // Not an unexpected failure like the generic catch below -
            // the frontend acts on this one specifically (a "choose a
            // branch" prompt), so its own code must survive the
            // response rather than collapsing to ERROR_501 like every
            // other exception here.
            return [
                'status'  => false,
                'message' => $e->getMessage(),
                'code'    => ResponseError::LOCATION_AMBIGUOUS,
                'data'    => $calculate
            ];
        } catch (Throwable $e) {

            $this->error($e);

            return [
                'status'  => false,
                'message' => $e->getMessage(),// . $e->getFile() . $e->getLine()
                'code'    => ResponseError::ERROR_501,
                'data'    => $calculate
            ];
        }
    }

    public function update(Booking $model, array $data): array
    {
        try {
            $model = BookingCapacity::transaction(function () use ($model, $data) {

                BookingCapacity::lockBooking($model->id, $data['service_master_id'] ?? null);
                $model = Booking::query()->lockForUpdate()->findOrFail($model->id);
                $this->checkAssignedBeforeUpdate($model);
                $this->validateSchedulingMutation($model, $data);
                if (isset($data['status']) && $data['status'] !== $model->status) {
                    (new BookingCancellationSettlement)->assertCanTransition($model);
                }
                $model->load(['transaction']);

                (new BookingActivityService)->create($model, 'update', $this->language, $data);

                $data['notes'] = array_merge($model->notes ?? [], $data['notes'] ?? []);

                if (isset($data['service_master_id'])) {

                    /** @var ServiceMaster $serviceMaster */
                    $serviceMaster = ServiceMaster::select(['master_id'])->find($data['service_master_id']);

                    $data['master_id'] = $serviceMaster->master_id;

                }

                if (
                    @$data['status'] === Booking::STATUS_ENDED
                    && $model->transaction?->status === Transaction::STATUS_PROGRESS
                    && \App\Services\TransactionService\BookingPaymentAuthority::offline($model->transaction->paymentSystem)
                ) {
                    $model->transaction?->update(['status' => Transaction::STATUS_PAID]);
                }

                $extras = collect();

                if (data_get($data, 'service_extras.0')) {
                    $extras = ServiceExtra::find($data['service_extras']);
                }

                if ($extras->count() > 0) {

                    $extraPrice = $extras->sum('price');

                    $model->extras()->delete();

                    $oldExtraPrice = $model->extras->sum('price');

                    foreach ($extras as $extra) {
                        $model->extras()->create(['price' => $extra->price, 'service_extra_id' => $extra->id]);
                    }

                    $data['extra_price'] = $extraPrice;
                    $data['total_price'] = $model->extra_price - $oldExtraPrice + $extraPrice;

                }

                $model->update($data);

                $userMemberShip = UserMemberShip::with(['memberShipServices'])
                    ->where([
                        ['user_id',    '=', $data['user_id'] ?? auth('sanctum')->id()],
                        ['id',         '=', $data['user_member_ship_id'] ?? null],
                        ['expired_at', '>', date('Y-m-d H:i:s')],
                    ])
                    ->first();

                /** @var UserMemberShip $userMemberShip */
                if ($userMemberShip?->sessions === MemberShip::LIMITED && isset($data['user_member_ship_id'])) {
                    $userMemberShip->decrement('remainder');
                }

                if (!empty($model->user_member_ship_id)) {
                    UserMemberShip::where('sessions', MemberShip::LIMITED)
                        ->find($model->user_member_ship_id)
                        ?->increment('remainder');
                }

                $moveTheNeXT = Settings::where('key', 'can_move_the_reservation_time')->first()?->value;

                if (@$data['next_times_update'] && $moveTheNeXT) {
                    $this->nextTimeBookingsUpdate($model, ResponseError::BOOKING_ACTIVITY_RESCHEDULE, $data);
                }

                $this->sendAllUpdateBooking($model, ResponseError::BOOKING_UPDATED, $data);

                return $model;
            });

            return [
                'status'  => true,
                'message' => ResponseError::NO_ERROR,
                'data'    => $model->fresh((new BookingRepository)->getWith()),
            ];
        } catch (Throwable $e) {

            $this->error($e);

            return ['status' => false, 'message' => $e->getMessage(), 'code' => ResponseError::ERROR_502];
        }
    }

    /**
     * @param int $id
     * @param array $filter
     * @return Booking
     * @throws Throwable
     */
    public function statusUpdate(int $id, array $filter): Booking
    {
        $status = $filter['status'];
        if (!in_array($status, Booking::STATUSES, true)) {
            throw new Exception('Invalid Booking status');
        }
        $transition = function () use ($id, $filter, $status) {
            BookingCapacity::lockBooking($id);
            $model = Booking::with(['shop', 'master', 'user', 'localClient'])->lockForUpdate()
                ->has('master')->has('shop')->find($id);
            if (empty($model)) {
                throw new Exception(__('errors.' . ResponseError::ERROR_404, locale: $this->language));
            }
            if ($model->status === $status) {
                throw new Exception(__('errors.' . ResponseError::ERROR_252, locale: $this->language));
            }
            $this->checkAssignedBeforeUpdate($model);
            $this->validateSchedulingMutation($model, ['status' => $status]);
            $settlement = new BookingCancellationSettlement;
            $settlement->assertCanTransition($model);
            $locale = auth('sanctum')->user()->lang ?? $this->language;
            if ($status === Booking::STATUS_CANCELED) {
                $settlement->settle($model);
            }
            (new BookingActivityService)->create($model, $status, $locale);

            //IF before update status was ended we cancel added statistic
            if ($model->status === Booking::STATUS_ENDED && $status === Booking::STATUS_CANCELED) {
                $this->updateStat($model, false);
            }

            if ($status === Booking::STATUS_ENDED) {
                $this->updateStat($model);

                $point = Point::getBookingActualPoint($model->total_price);

                if ($model->user) {
                    Utility::topUpCashBack($point, $model, $this->language);
                }

            }

            if ($status === Booking::STATUS_CANCELED) {

                if ($model->pointHistories?->count() > 0) {
                    foreach ($model->pointHistories as $pointHistory) {
                        /** @var PointHistory $pointHistory */
                        if ($model->user?->wallet) {
                            \App\Services\WalletHistoryService\WalletDebit::debit(
                                $model->user->wallet, $pointHistory->price, (int) $model->user_id
                            );
                        }
                        $pointHistory->delete();
                    }
                }
            }


            $model->update([
                'status' => $status,
                'canceled_note' => $filter['canceled_note'] ?? $model->canceled_note
            ]);

            if ($status === Booking::STATUS_CANCELED) {
                $this->reversePayableForCanceledBooking($model);
            }

            return $model;
        };
        return $this->capacityGroupOwned ? $transition() : BookingCapacity::transaction($transition);
    }

    /**
     * @param int $id
     * @param array $data
     * @return mixed
     * @throws Throwable
     */
    public function canceledByParent(int $id, array $data): mixed
    {
        if (DB::transactionLevel() !== 0) {
            throw new \DomainException('Booking cancellation requires its own fresh transaction.');
        }
        // Discovery is outside the owned transaction. Lock every resource before
        // its first consistent read, then revalidate complete group membership.
        $model = Booking::with('children')->where('user_id', auth('sanctum')->id())->findOrFail($id);
        $group = $model->children->concat([$model]);
        $ids = $group->pluck('id')->sort()->values()->all();
        return BookingCapacity::transaction(function () use ($id, $data, $group, $ids) {
            BookingCapacity::lock($group->pluck('service_master_id')->all(), $group->pluck('master_id')->all());
            $model = Booking::where('user_id', auth('sanctum')->id())->findOrFail($id);
            $current = $model->children()->pluck('id')->prepend($id)->sort()->values()->all();
            if ($ids !== $current) {
                throw new \DomainException('Booking group changed; reload before canceling.');
            }
            $this->capacityGroupOwned = true;
            try {
                foreach ($ids as $bookingId) {
                    $this->statusUpdate((int) $bookingId, [
                        'status' => Booking::STATUS_CANCELED,
                        'canceled_note' => $data['canceled_note'] ?? $model->canceled_note,
                    ]);
                }
            } finally {
                $this->capacityGroupOwned = false;
            }
            return $model->fresh();
        });
    }

    /**
     * @throws Exception
     */
    public function beforeSave(array $data, null|float|int $rate = null, ?int &$shopId = null): array
    {
        $serviceMaster = ServiceMaster::with([
            'service:id,category_id',
        ])
            ->where(['active' => true, 'id' => $data['service_master_id']])
            ->first();

        /** @var ServiceMaster $serviceMaster */
        if (!$serviceMaster?->master_id) {
            throw new Exception(__('errors.' . ResponseError::NO_AVAILABLE_MASTERS, locale: $this->language));
        }

        if (empty($shopId)) {
            $shopId = $serviceMaster->shop_id;
        }

        if ($serviceMaster->shop_id !== $shopId) {
            throw new Exception(__('errors.' . ResponseError::OTHER_SHOP, locale: $this->language));
        }

        $data['category_id']         = $serviceMaster->service?->category_id;
        $data['service_id']          = $serviceMaster->service_id;
        $data['master_id']           = $serviceMaster->master_id;
        $data['type']                = $serviceMaster->type;
        $data['discount']            = max((int)$serviceMaster->discount, 0);
        $data['commission_fee']      = max($serviceMaster->commission_fee, 0);
        $data['price']               = max($serviceMaster->price, 0);
        $data['service_fee']         = Utility::resolveServiceFee('booking_service_fee', $data['price']);
        $data['rate']                = $rate;
        $data['shop_id']             = $shopId;
        $data['shop_location_id']    = $this->resolveBookingLocation($data, $shopId, $serviceMaster->master_id);
        // Frozen here, alongside service_fee/commission_fee above, so a
        // shop flipping this setting after the booking exists can never
        // change what an already-created booking settles as - see
        // TransactionObserver, which reads this column, never the shop's
        // live setting.
        $data['collect_via_platform'] = (bool)Shop::find($shopId)?->collect_via_platform;

        return $data;
    }

    /**
     * Resolves and validates the branch a booking is made at, making
     * shop_location_id a first-class, authoritative attribute of the
     * booking record rather than something inferred after the fact.
     *
     * A shop that has never set up any SERVICE ShopLocation (branches are
     * opt-in - see ShopLocationController) keeps today's location-less
     * behavior exactly: no location is required or stored. Once a shop has
     * at least one SERVICE location, every booking must resolve to one -
     * explicitly supplied or auto-resolved, see autoResolveBookingLocation()
     * - and it must belong to this shop, and - mirroring the exact opt-in
     * semantics already used for read-side branch scoping in
     * User::bookingBranchScope()/MasterRepository::index() - the selected
     * master must actually be assigned to it, unless that master has no
     * branch assignments at all (unrestricted, bookable everywhere). This
     * final check runs identically whether shop_location_id came from the
     * request or from auto-resolution - a manipulated/cross-branch id is
     * rejected exactly the same either way.
     *
     * @throws Exception
     */
    private function resolveBookingLocation(array $data, int $shopId, int $masterId): ?int
    {
        $serviceLocationIds = ShopLocation::where('shop_id', $shopId)
            ->where('type', ShopLocation::SERVICE)
            ->pluck('id');

        if ($serviceLocationIds->isEmpty()) {
            return null;
        }

        $shopLocationId = data_get($data, 'shop_location_id');

        if (empty($shopLocationId)) {
            $shopLocationId = $this->autoResolveBookingLocation($shopId, $masterId, $serviceLocationIds);
        }

        $location = ShopLocation::where('id', $shopLocationId)
            ->where('shop_id', $shopId)
            ->where('type', ShopLocation::SERVICE)
            ->first();

        if (!$location) {
            throw new Exception(__('errors.' . ResponseError::OTHER_LOCATION, locale: $this->language));
        }

        $invitation = Invitation::where('user_id', $masterId)
            ->where('shop_id', $shopId)
            ->where('status', Invitation::ACCEPTED)
            ->with('shopLocations:id')
            ->first();

        $assignedLocationIds = $invitation?->shopLocations->pluck('id')->all() ?? [];

        if (!empty($assignedLocationIds) && !in_array((int)$shopLocationId, $assignedLocationIds, true)) {
            throw new Exception(__('errors.' . ResponseError::MASTER_NOT_IN_LOCATION, locale: $this->language));
        }

        return (int)$shopLocationId;
    }

    /**
     * Called only when the request omitted shop_location_id - the
     * frontend has no way to send it yet in every case (a single-branch
     * shop never needed a branch picker; a multi-branch shop's picker is
     * still being built - see the Prompt 2 branch-validation follow-up).
     * Auto-resolves only where there is no genuine ambiguity to protect
     * against:
     *
     * 1. The shop itself has exactly one SERVICE location - the same
     *    location every booking to it would have to name anyway.
     * 2. The shop has several, but the selected master is (via the
     *    invitation_shop_locations pivot) assigned to exactly one of
     *    them - naming any other location would fail the master-
     *    assignment check right below this call regardless, so this one
     *    is the only value that could ever succeed.
     *
     * Anything else - a multi-branch shop whose master is unassigned
     * (bookable everywhere) or assigned to more than one of its
     * locations - is genuinely ambiguous: no signal available server-side
     * says which branch the customer meant, so this throws a distinct
     * LOCATION_AMBIGUOUS (not the generic LOCATION_REQUIRED) for the
     * frontend to act on - e.g. by prompting a branch picker - rather
     * than silently guessing one and risking a booking at the wrong
     * branch, which is exactly what this whole validation exists to
     * prevent.
     *
     * @throws LocationAmbiguousException
     */
    private function autoResolveBookingLocation(int $shopId, int $masterId, Collection $serviceLocationIds): int
    {
        if ($serviceLocationIds->count() === 1) {
            return (int) $serviceLocationIds->first();
        }

        $invitation = Invitation::where('user_id', $masterId)
            ->where('shop_id', $shopId)
            ->where('status', Invitation::ACCEPTED)
            ->with('shopLocations:id')
            ->first();

        $assignedLocationIds = ($invitation?->shopLocations->pluck('id') ?? collect())
            ->intersect($serviceLocationIds)
            ->values();

        if ($assignedLocationIds->count() === 1) {
            return (int) $assignedLocationIds->first();
        }

        throw new LocationAmbiguousException(__('errors.' . ResponseError::LOCATION_AMBIGUOUS, locale: $this->language));
    }

    /**
     * A canceled booking's original 'payable' ledger row (see
     * TransactionObserver) must not be edited in place - that would erase
     * the record of what actually happened at settlement. Instead, this
     * writes a signed 'payable_adjustment' row that fully reverses it: a
     * canceled booking delivered no service, so the platform no longer owes
     * the shop that share. firstOrCreate keyed on the same transaction
     * makes this idempotent if cancellation is somehow triggered twice.
     */
    private function reversePayableForCanceledBooking(Booking $booking): void
    {
        $payableEntries = PlatformFeeLedgerEntry::query()
            ->where('payable_type', Booking::class)
            ->where('payable_id', $booking->id)
            ->where('entry_type', PlatformFeeLedgerEntry::ENTRY_TYPE_PAYABLE)
            ->get();

        foreach ($payableEntries as $entry) {
            PlatformFeeLedgerEntry::query()->firstOrCreate(
                [
                    'transaction_id' => $entry->transaction_id,
                    'entry_type'     => PlatformFeeLedgerEntry::ENTRY_TYPE_PAYABLE_ADJUSTMENT,
                ],
                [
                    'payable_type' => Booking::class,
                    'payable_id'   => $booking->id,
                    'shop_id'      => $entry->shop_id,
                    'payment_id'   => $entry->payment_id,
                    'currency_id'  => $entry->currency_id,
                    'amount'       => -$entry->amount,
                    'status'       => PlatformFeeLedgerEntry::STATUS_PENDING,
                    'note'         => "Reversed: booking #{$booking->id} canceled",
                ]
            );
        }
    }

    public function delete(?array $ids = [], array $filter = []): void
    {
        $models = Booking::filter($filter)->find(is_array($ids) ? $ids : []);

        foreach ($models as $model) {
            $model->delete();
        }

        try {
            DB::table('push_notifications')
                ->where('model_type', Booking::class)
                ->whereIn('model_id', $models->pluck('id')->toArray())
                ->delete();
        } catch (Throwable $e) {
            $this->error($e);
        }
    }

    /**
     * @param ServiceMaster|ServiceMasterResource $serviceMaster
     * @param array $data
     * @param array $item
     * @return array
     */
    private function updateMemberShip(ServiceMaster|ServiceMasterResource $serviceMaster, array $data, array $item): array
    {
        $item = [$item];

        $userMemberShip = (new BookingRepository)->userMemberShipCalculate($serviceMaster, $data, 0, $item);

        if (empty($userMemberShip)) {
            return $item[0];
        }

        if ($userMemberShip->sessions === MemberShip::LIMITED && $userMemberShip->remainder > 0) {

            $userMemberShip->update(['remainder' => $userMemberShip->remainder - 1]);

            return $item[0];
        }

        if ($userMemberShip->sessions === MemberShip::LIMITED && $userMemberShip->remainder === 0) {
            $userMemberShip->delete();
        }

        return $item[0];
    }

    /**
     * @param int $id
     * @param array $data
     * @return Booking
     * @throws Throwable
     */
    public function notesUpdate(int $id, array $data): Booking
    {
        $model = Booking::with([
            'shop:id,user_id',
            'shop.seller:id,lang,firstname,lastname,firebase_token',
            'master:id,lang,firstname,lastname,firebase_token',
            'user:id,lang,firstname,lastname,firebase_token',
            'user.notifications',
        ])->find($id);

        if (empty($model)) {
            throw new Exception(__('errors.' . ResponseError::ERROR_404, locale: $this->language));
        }

        /** @var Booking $model */
        $this->checkAssignedBeforeUpdate($model);

        return DB::transaction(function () use ($model, $data) {

            (new BookingActivityService)->create($model, 'update', $this->language, ['notes' => $data['note']]);

            $notes   = $model->notes ?? [];
            $notes[] = $data['note'];

            $model->update(['notes' => $notes]);

            $this->sendAllUpdateBooking($model, ResponseError::BOOKING_NOTE_UPDATED, $data, $data['note']);

            return $model;
        });
    }

    /**
     * @param int $id
     * @param array $data
     * @return Booking
     * @throws Throwable
     */
    public function timesUpdate(int $id, array $data): Booking
    {
        $model = Booking::with([
            'shop:id,user_id',
            'shop.seller:id,lang,firstname,lastname,firebase_token',
            'master:id,lang,firstname,lastname,firebase_token',
            'user:id,lang,firstname,lastname,firebase_token',
            'user.notifications',
        ])->find($id);

        if (empty($model)) {
            throw new Exception(__('errors.' . ResponseError::ERROR_404, locale: $this->language));
        }

        /** @var Booking $model */
        $this->checkAssignedBeforeUpdate($model);

        return BookingCapacity::transaction(function () use ($model, $data) {
            BookingCapacity::lockBooking($model->id);
            $model = Booking::with(['shop', 'master', 'user'])->findOrFail($model->id);
            $this->checkAssignedBeforeUpdate($model);
            $this->validateSchedulingMutation($model, $data);

            (new BookingActivityService)->create($model, 'update', $this->language, $data);

            if ($data['start_date'] < date('Y-m-d H:i')) {
                throw new Exception(__('errors.' . ResponseError::ERROR_509, locale: $this->language));
            }

            $model->update($data);

            $key = ResponseError::BOOKING_ACTIVITY_RESCHEDULE;

            $this->sendAllUpdateBooking($model, $key, $data);

            $moveTheNeXT = Settings::where('key', 'can_move_the_reservation_time')->first()?->value;

            if ($data['next_times_update'] && $moveTheNeXT) {
                $this->nextTimeBookingsUpdate($model, $key, $data);
            }

            return $model;
        });
    }

    /**
     * @param int $id
     * @param array $data
     * @return Booking
     * @throws Throwable
     */
    public function extraTime(int $id, array $data): Booking
    {
        $model = Booking::with([
            'shop:id,user_id',
            'extraTimes',
            'user:id,lang,firstname,lastname,firebase_token',
            'user.notifications',
        ])->find($id);

        if (empty($model)) {
            throw new Exception(__('errors.' . ResponseError::ERROR_404, locale: $this->language));
        }

        /** @var Booking $model */
        $this->checkAssignedBeforeUpdate($model);

        return BookingCapacity::transaction(function () use ($model, $data) {
            BookingCapacity::lockBooking($model->id);
            $model = Booking::with(['shop', 'master', 'user'])->findOrFail($model->id);
            $this->checkAssignedBeforeUpdate($model);

            if (\App\Services\PaymentAccounting\NativePaymentAccounting::installed()
                && DB::table('commerce_payment_allocations')->where('payable_type','booking')
                    ->where('payable_id',$model->id)->exists()) {
                throw new \DomainException('Extra-time economic contract is unproven; the frozen base cannot be repriced.');
            }
            (new BookingActivityService)->create($model, 'extra_time', $this->language, $data);

//            if (isset($data['remove_ids'][0])) {
//                $model->extraTimes()->whereIn('id', $data['remove_ids'])->delete();
//            }
//
//            if (isset($data['id'])) {
//
//                unset($data['remove_ids']);
//
//                /** @var BookingExtraTime $extraTime */
//                $extraTime = $model->extraTimes()->where('id', $data['id'])->first();
//
//                $unit = "$extraTime->duration $extraTime->duration_type";
//
//                $model->update([
//                    'end_date' => $model->end_date->sub($unit)->format('Y-m-d H:i:s')
//                ]);
//
//                $extraTime->update($data);
//
//                $unit = "$extraTime->duration $extraTime->duration_type";
//
//                $model->update([
//                    'end_date' => $model->end_date->add($unit)->format('Y-m-d H:i:s')
//                ]);
//
//                return $model;
//            }

            /** @var BookingExtraTime $extraTime */
            $extraTime = $model->extraTimes()->create($data);

            $unit = "+$extraTime->duration $extraTime->duration_type";
            $end = date('Y-m-d H:i:s', strtotime("$model->end_date $unit"));
            $this->validateSchedulingMutation($model, ['end_date' => $end]);

            $model->update([
                'end_date' => $end
            ]);

            return $model->fresh(['extraTimes']);
        });
    }

    /**
     * @param Booking $model
     * @param string $key
     * @param array $data
     * @param DateTime|null $startDate
     * @param DateTime|null $endDate
     * @return array
     * @throws Exception
     */
    public function nextTimeBookingsUpdate(
        Booking $model,
        string $key,
        array $data,
        ?DateTime &$startDate = null,
        ?DateTime &$endDate = null
    ): array
    {

        if (DB::transactionLevel() === 0) {
            throw new \DomainException('Cascade rescheduling requires protected booking admission.');
        }
        if (!in_array($model->status, BookingCapacity::ACTIVE, true)) {
            return [];
        }
        $nextTimeBookings = Booking::with(['shop', 'master', 'user'])
            ->where('master_id', $model->master_id)
            ->where('id', '!=', $model->id)
            ->whereIn('status', BookingCapacity::ACTIVE)
            ->where('start_date', '>=', $model->start_date)
            ->orderBy('start_date')
            ->orderBy('id')
            ->get();
        $cursor = new \DateTimeImmutable($model->end_date);
        $plan = [];
        foreach ($nextTimeBookings as $booking) {
            if (new \DateTimeImmutable($booking->start_date) >= $cursor) {
                break;
            }
            if ((int) $booking->shop_id !== (int) $model->shop_id) {
                throw new \DomainException('Cascade rescheduling cannot modify another Shop.');
            }
            $this->checkAssignedBeforeUpdate($booking);
            $duration = strtotime($booking->end_date) - strtotime($booking->start_date);
            if ($duration <= 0) {
                throw new \DomainException('Existing appointment duration is invalid.');
            }
            $end = $cursor->modify("+$duration seconds");
            $plan[] = [$booking, $cursor->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s')];
            $cursor = $end;
        }
        $except = array_merge([$model->id], array_map(fn($p) => $p[0]->id, $plan));
        BookingCapacity::assertWindow($model->service_master_id, $model->start_date, $model->end_date, $except);
        foreach ($plan as [$booking, $start, $end]) {
            BookingCapacity::assertWindow($booking->service_master_id, $start, $end, $except);
        }
        $times = [];
        foreach ($plan as [$booking, $start, $end]) {
            (new BookingActivityService)->create($booking, 'update', $this->language, ['start_date' => $start, 'end_date' => $end]);
            // Persisted duration already includes processing; no second buffer.
            $booking->update(['start_date' => $start, 'end_date' => $end]);
            $this->sendAllUpdateBooking($booking, $key, $data);
            $times[] = [$booking->id, $start, $end, 0, (strtotime($end) - strtotime($start)) / 60];
        }

        return $times;
    }

    /**
     * @param Booking $booking
     * @param $data
     * @return array
     */
    public function addReview(Booking $booking, $data): array
    {
        $booking->addAssignReview($data, $booking->master);

        return [
            'status' => true,
            'code'   => ResponseError::NO_ERROR,
            'data'   => $booking
        ];
    }

    /**
     * @param Booking $model
     * @param bool $isIncrement
     * @return void
     */
    public function updateStat(Booking $model, bool $isIncrement = true): void
    {
        $serviceCount = $model->shop->b_count;
        $serviceSum   = $model->shop->b_sum;

        $model->shop->update([
            'b_count' => $isIncrement ? $serviceCount + 1 : $serviceCount - 1,
            'b_sum'   => $isIncrement ? $serviceSum + $model->total_price : $serviceSum - $model->total_price,
        ]);

        $masterCount = $model->master->b_count;
        $masterSum   = $model->master->b_sum;

        $model->master->update([
            'b_count' => $isIncrement ? $masterCount + 1 : $masterCount - 1,
            'b_sum'   => $isIncrement ? $masterSum + $model->total_price : $masterSum - $model->total_price
        ]);

        if ($model->user) {
            $userCount = $model->user->b_count;
            $userSum   = $model->user->b_sum;

            $model->user->update([
                'b_count' => $isIncrement ? $userCount + 1 : $userCount - 1,
                'b_sum'   => $isIncrement ? $userSum + $model->total_price : $userSum - $model->total_price
            ]);
        }
    }

    /**
     * @param array $data
     * @return float
     */
    private function updateGiftCartPrice(array $data): float
    {
        $giftCart = UserGiftCart::where('user_id', $data['user_id'])
            ->where('id', $data['user_gift_cart_id'])
            ->first();

        $data['total_gift_cart_price'] = $data['total_gift_cart_price'] / $data['rate'];

        $giftPrice = $giftCart->price - $data['total_gift_cart_price'];

        if ($giftPrice < 0) {
            $giftPrice = $data['total_gift_cart_price'] - $giftCart->price;
        }

        $giftCart?->update(['price' => $giftPrice]);

        if ($giftCart?->price <= 0) {
            $giftCart?->delete();
        }

        return (double)$giftPrice;
    }

    /**
     * @param Booking $model
     * @return void
     * @throws Exception
     */
    private function validateSchedulingMutation(Booking $model, array $data): void
    {
        $status = $data['status'] ?? $model->status;
        if (!in_array($status, BookingCapacity::ACTIVE, true)) {
            return;
        }
        if (!array_intersect(['start_date', 'end_date', 'service_master_id'], array_keys($data))
            && in_array($model->status, BookingCapacity::ACTIVE, true)) {
            return;
        }
        $assignmentId = (int) ($data['service_master_id'] ?? $model->service_master_id);
        $assignment = DB::table('service_masters')->where('id', $assignmentId)->first();
        if (!$assignment || (int) $assignment->shop_id !== (int) $model->shop_id) {
            throw new \DomainException('Rescheduling cannot change Shop ownership.');
        }
        $start = $data['start_date'] ?? $model->start_date;
        $end = $data['end_date'] ?? $model->end_date;
        $except = [$model->id];
        if (!empty($data['next_times_update'])
            && Settings::where('key', 'can_move_the_reservation_time')->value('value')) {
            $except = array_merge($except, Booking::where('master_id', $assignment->master_id)
                ->whereIn('status', BookingCapacity::ACTIVE)->where('start_date', '>=', $start)->pluck('id')->all());
        }
        BookingCapacity::assertWindow($assignmentId, $start, $end, $except);
    }

    public function checkAssignedBeforeUpdate(Booking $model): void
    {
        /** @var User $user */
        $user = auth('sanctum')->user();

        if ($user->hasRole('admin')) {
            return;
        }

        if ($user->hasRole('master') && $model->master_id !== $user->id && $model->user_id !== $user->id) {

            throw new Exception(__('errors.' . ResponseError::ERROR_404, locale: $this->language));

        } else if ($user->hasRole('seller') && $model->shop?->user_id !== $user->id && $model->user_id !== $user->id) {

            throw new Exception(__('errors.' . ResponseError::ERROR_404, locale: $this->language));

        } else if ($user->hasRole('user') && $model->user_id !== $user->id) {

            throw new Exception(__('errors.' . ResponseError::ERROR_404, locale: $this->language));
        }
    }
}
