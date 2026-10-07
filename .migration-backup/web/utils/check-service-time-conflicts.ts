import { InitialStateType } from "@/context/booking/booking.reducer";
import dayjs from "dayjs";

interface BookingInfo {
  serviceMasterId: number;
  masterId: number;
  masterName: string;
  serviceTitle: string;
  startTime: dayjs.Dayjs;
  endTime: dayjs.Dayjs;
  startTimeString: string;
  dateString: string;
  interval: number;
}

interface ServiceData {
  serviceMasterId: number;
  serviceName: string;
  startTime: string;
  endTime: string;
  duration: number;
  date: string;
}

interface MasterConflict {
  masterId: number;
  masterName: string;
  totalConflictedServices: number;
  conflictedServices: ServiceData[];
}

const checkTimeConflicts = (state: InitialStateType) => {
  // Group dateAndTimes by masterId for easier comparison
  const masterBookings: Record<number, BookingInfo[]> = {};
  const masterConflicts: Record<number, MasterConflict> = {};

  state.dateAndTimes.forEach((booking) => {
    // Find the corresponding service to get the master info
    const service = state.services.find(
      (s) => s.master?.service_master?.id === booking.serviceMasterId
    );
    if (!service || !service.master || !service.master.service_master) return;

    const masterId = service.master.id;
    const { interval } = service.master.service_master; // duration in minutes

    if (!interval) return;

    if (!masterBookings[masterId]) {
      masterBookings[masterId] = [];
    }

    // Parse the booking date and time using dayjs
    const bookingDateTime = dayjs(booking.date);
    const [hours, minutes] = booking.time.split(":").map(Number);
    const startTime = bookingDateTime.hour(hours).minute(minutes).second(0).millisecond(0);
    const endTime = startTime.add(interval, "minute");

    masterBookings[masterId].push({
      serviceMasterId: booking.serviceMasterId,
      masterId,
      masterName: service.master.full_name || "Unknown Master",
      serviceTitle: service.translation?.title || "Unknown Service",
      startTime,
      endTime,
      startTimeString: booking.time,
      dateString: booking.date,
      interval,
    });
  });

  // Check for conflicts within each master's bookings
  Object.keys(masterBookings).forEach((masterIdStr) => {
    const masterId = parseInt(masterIdStr, 10);
    const bookings = masterBookings[masterId];

    // Sort bookings by start time to make conflict detection more efficient
    bookings.sort(
      (a: BookingInfo, b: BookingInfo) => a.startTime.valueOf() - b.startTime.valueOf()
    );

    const conflictedServices: ServiceData[] = [];
    const processedPairs = new Set();

    // Check all pairs of bookings for overlaps
    for (let i = 0; i < bookings.length; i += 1) {
      for (let j = i + 1; j < bookings.length; j += 1) {
        const booking1 = bookings[i];
        const booking2 = bookings[j];

        // Check if bookings overlap using dayjs
        const overlap =
          booking1.startTime.isBefore(booking2.endTime) &&
          booking2.startTime.isBefore(booking1.endTime);

        if (overlap) {
          const pairKey = `${Math.min(
            booking1.serviceMasterId,
            booking2.serviceMasterId
          )}-${Math.max(booking1.serviceMasterId, booking2.serviceMasterId)}`;

          if (!processedPairs.has(pairKey)) {
            processedPairs.add(pairKey);

            // Add both services to conflicted services if not already added
            const service1Data = {
              serviceMasterId: booking1.serviceMasterId,
              serviceName: booking1.serviceTitle,
              startTime: booking1.startTimeString,
              endTime: booking1.endTime.format("HH:mm"),
              duration: booking1.interval,
              date: booking1.dateString,
            };

            const service2Data = {
              serviceMasterId: booking2.serviceMasterId,
              serviceName: booking2.serviceTitle,
              startTime: booking2.startTimeString,
              endTime: booking2.endTime.format("HH:mm"),
              duration: booking2.interval,
              date: booking2.dateString,
            };

            // Check if services are already in the conflicted array
            const service1Exists = conflictedServices.some(
              (s) => s.serviceMasterId === booking1.serviceMasterId
            );
            const service2Exists = conflictedServices.some(
              (s) => s.serviceMasterId === booking2.serviceMasterId
            );

            if (!service1Exists) {
              conflictedServices.push(service1Data);
            }
            if (!service2Exists) {
              conflictedServices.push(service2Data);
            }
          }
        }
      }
    }

    // Only add to masterConflicts if there are conflicts
    if (conflictedServices.length > 0) {
      masterConflicts[masterId] = {
        masterId,
        masterName: bookings[0].masterName,
        totalConflictedServices: conflictedServices.length,
        conflictedServices: conflictedServices.sort((a, b) =>
          a.startTime.localeCompare(b.startTime)
        ),
      };
    }
  });

  const conflictsByMaster = Object.values(masterConflicts);
  const totalConflicts = conflictsByMaster.reduce(
    (sum, master: MasterConflict) => sum + master.totalConflictedServices,
    0
  );

  return {
    hasConflicts: conflictsByMaster.length > 0,
    totalMastersWithConflicts: conflictsByMaster.length,
    totalConflictedServices: totalConflicts,
    conflictsByMaster,
    summary:
      conflictsByMaster.length > 0
        ? `Found conflicts for ${conflictsByMaster.length} master(s) with ${totalConflicts} total conflicted services`
        : "No time conflicts found",
    masterSummary: Object.keys(masterBookings).map((masterIdStr) => {
      const masterId = parseInt(masterIdStr, 10);
      return {
        masterId,
        masterName: masterBookings[masterId][0]?.masterName || "Unknown",
        totalBookings: masterBookings[masterId].length,
        services: masterBookings[masterId].map((b: BookingInfo) => ({
          serviceName: b.serviceTitle,
          startTime: b.startTimeString,
          endTime: b.endTime.format("HH:mm"),
          duration: b.interval,
        })),
      };
    }),
  };
};

export default checkTimeConflicts;
