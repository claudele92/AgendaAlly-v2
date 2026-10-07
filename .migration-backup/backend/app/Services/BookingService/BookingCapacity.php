<?php
declare(strict_types=1);

namespace App\Services\BookingService;

use App\Models\Booking;
use App\Repositories\UserRepository\MasterRepository;
use Illuminate\Support\Facades\DB;
use DomainException;

/**
 * Existing specialist User PK is the mutex. No calendar/child-range locks.
 * Real entry points own a fresh RR transaction; nested stale snapshots fail closed.
 */
final class BookingCapacity
{
    public const ACTIVE = [Booking::STATUS_NEW, Booking::STATUS_BOOKED, Booking::STATUS_PROGRESS];

    public static function transaction(callable $callback): mixed
    {
        if (DB::transactionLevel() !== 0) {
            throw new DomainException('Booking admission requires its own fresh transaction.');
        }
        return DB::transaction($callback, 3);
    }

    /** Lock known assignment PKs and then distinct specialist PKs before any plain read. */
    public static function lock(array $assignmentIds, array $otherMasterIds = []): void
    {
        $assignmentIds = array_unique(array_map('intval', $assignmentIds));
        sort($assignmentIds, SORT_NUMERIC);
        $masters = $otherMasterIds;
        foreach ($assignmentIds as $id) {
            $assignment = DB::table('service_masters')->where('id', $id)->lockForUpdate()->first();
            if (!$assignment) {
                throw new DomainException('Service assignment is unavailable.');
            }
            $masters[] = (int) $assignment->master_id;
        }
        $masters = array_unique(array_map('intval', $masters));
        sort($masters, SORT_NUMERIC);
        foreach ($masters as $id) {
            if (!DB::table('users')->where('id', $id)->lockForUpdate()->first()) {
                throw new DomainException('Specialist is unavailable.');
            }
        }
    }

    public static function lockBooking(int $id, ?int $assignmentId = null): void
    {
        $booking = DB::table('bookings')->where('id', $id)->lockForUpdate()->first();
        if (!$booking) {
            throw new DomainException('Booking is unavailable.');
        }
        self::lock([$assignmentId ?? (int) $booking->service_master_id], [(int) $booking->master_id]);
    }

    /** Same rule used by discovery, preview, creation and protected mutation. */
    public static function assertWindow(int $assignmentId, string $start, string $end, array $except = []): void
    {
        $from = new \DateTimeImmutable($start);
        $to = new \DateTimeImmutable($end);
        if ($to <= $from) {
            throw new DomainException('Appointment duration must be positive.');
        }
        $masterId = DB::table('service_masters')->where('id', $assignmentId)->value('master_id');
        if (!$masterId) {
            throw new DomainException('Service assignment is unavailable.');
        }
        $days = (new MasterRepository)->times((int) $masterId, [
            'service_master_id' => $assignmentId,
            'start_date' => $from->format('Y-m-d'),
            'end_date' => $to->modify('-1 second')->format('Y-m-d'),
        ], false, true, $except);
        // Existing hours are same-date windows, not invented overnight shifts.
        $day = $days[$from->format('Y-m-d')] ?? null;
        if (!$day || $day['closed'] || !$day['_working']
            || $from < new \DateTimeImmutable($day['_working'][0])
            || $to > new \DateTimeImmutable($day['_working'][1])) {
            throw new DomainException('Appointment is outside specialist working hours or on a closed date.');
        }
        foreach ($day['_blocked'] as [$busyFrom, $busyTo]) {
            if ($from < new \DateTimeImmutable($busyTo) && $to > new \DateTimeImmutable($busyFrom)) {
                throw new DomainException('Appointment overlaps an occupied or disabled period.');
            }
        }
    }
}