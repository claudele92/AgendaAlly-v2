<?php

declare(strict_types=1);

namespace Tests\Feature\Booking;

use App\Models\Booking;
use App\Models\Currency;
use App\Models\Payment;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A Booking row is created by BookingService::create() at the final
 * checkout submission - not earlier, contrary to this test's own previous
 * assumption - and starts at the DB default status 'new' regardless of
 * payment method (see create_bookings_table migration); the seller/staff
 * move it to 'booked' afterward. So 'new' is the ordinary status of every
 * fresh booking, cash included, and blindly excluding it (the original
 * fix here) hid real cash bookings a customer had just placed, waiting on
 * the seller to confirm them.
 *
 * The customer's own "My Appointments" list
 * (Dashboard/User/BookingController::index()) now keys off whether a
 * Transaction row exists instead: BookingService::create() writes one via
 * $model->createTransaction() the moment a payment_id is supplied,
 * whatever its tag - a cash booking included, since the frontend always
 * sends payment_id once a payment method is chosen. Only a 'new' booking
 * with no Transaction at all - the create request never carried a
 * payment_id in the first place - is a genuinely abandoned checkout.
 * Every other status stays visible regardless, canceled included.
 */
class CustomerAppointmentsListTest extends TestCase
{
    use RefreshDatabase;

    private function makeBooking(User $customer, string $status): Booking
    {
        $shop     = Shop::factory()->create(['user_id' => User::factory()->create()->id]);
        $master   = User::factory()->create();
        $currency = Currency::factory()->create();

        return Booking::create([
            'service_master_id' => null,
            'master_id'         => $master->id,
            'user_id'           => $customer->id,
            'shop_id'           => $shop->id,
            'currency_id'       => $currency->id,
            'start_date'        => now()->addDay(),
            'end_date'          => now()->addDay()->addHour(),
            'price'             => 5000,
            'total_price'       => 5000,
            'service_fee'       => 0,
            'status'            => $status,
        ]);
    }

    public function test_appointments_list_excludes_abandoned_new_bookings_but_keeps_every_other_status(): void
    {
        $customer = User::factory()->create();

        // No Transaction row - the create request never carried a
        // payment_id, so checkout never actually completed.
        $abandoned = $this->makeBooking($customer, Booking::STATUS_NEW);
        $booked    = $this->makeBooking($customer, Booking::STATUS_BOOKED);
        $progress  = $this->makeBooking($customer, Booking::STATUS_PROGRESS);
        $ended     = $this->makeBooking($customer, Booking::STATUS_ENDED);
        $canceled  = $this->makeBooking($customer, Booking::STATUS_CANCELED);

        $response = $this->actingAs($customer, 'sanctum')
            ->getJson('api/v1/dashboard/user/bookings?parent=1')
            ->assertOk();

        $ids = collect($response->json('data'))->pluck('id')->all();

        $this->assertNotContains($abandoned->id, $ids, 'a booking with no Transaction at all must not appear in My Appointments');
        $this->assertContains($booked->id, $ids);
        $this->assertContains($progress->id, $ids);
        $this->assertContains($ended->id, $ids);
        $this->assertContains($canceled->id, $ids, 'canceled bookings must stay visible');
    }

    public function test_a_completed_cash_booking_stays_visible_while_awaiting_seller_confirmation(): void
    {
        $customer = User::factory()->create();
        $cash = Payment::factory()->create(['active' => true, 'tag' => Payment::TAG_CASH]);

        $new = $this->makeBooking($customer, Booking::STATUS_NEW);
        $new->createTransaction([
            'price'          => $new->total_price,
            'user_id'        => $customer->id,
            'payment_sys_id' => $cash->id,
        ]);

        $response = $this->actingAs($customer, 'sanctum')
            ->getJson('api/v1/dashboard/user/bookings?parent=1')
            ->assertOk();

        $ids = collect($response->json('data'))->pluck('id')->all();

        $this->assertContains(
            $new->id,
            $ids,
            'a cash booking that completed checkout must stay visible even while still new'
        );
    }

    public function test_an_explicit_status_filter_is_not_overridden_by_the_default_exclusion(): void
    {
        $customer = User::factory()->create();

        $new = $this->makeBooking($customer, Booking::STATUS_NEW);
        $this->makeBooking($customer, Booking::STATUS_BOOKED);

        $response = $this->actingAs($customer, 'sanctum')
            ->getJson('api/v1/dashboard/user/bookings?parent=1&status=' . Booking::STATUS_NEW)
            ->assertOk();

        $ids = collect($response->json('data'))->pluck('id')->all();

        $this->assertContains(
            $new->id,
            $ids,
            'an explicit status filter should still be able to ask for new bookings directly'
        );
    }
}
