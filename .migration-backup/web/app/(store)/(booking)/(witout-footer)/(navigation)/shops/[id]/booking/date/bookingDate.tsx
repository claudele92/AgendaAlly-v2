"use client";

import { useBooking } from "@/context/booking";
import { useQuery } from "@tanstack/react-query";
import { masterService } from "@/services/master";
import dayjs from "dayjs";
import { BookingDateTime } from "../../components/date-time";
import Link from "next/link";
import { assignmentIds } from "@/context/booking/booking-draft.cjs";

export const BookingDate = ({ shopSlug }: { shopSlug?: string }) => {
  const { state } = useBooking();
  const ids = assignmentIds(state.services);
  const hasAssignments = ids.length > 0 && ids.length === state.services.length;
  const { data, isLoading, isError, refetch } = useQuery(
    [
      "times",
      ids,
      state.master?.service_master?.id,
    ],
    () =>
      masterService.getTimes({
        service_master_ids: ids,
        start_date: dayjs().format("YYYY-MM-DD HH:mm"),
      }),
    { enabled: hasAssignments }
  );
  if (!hasAssignments) {
    return <div role="status" className="space-y-3">
      <p>Select a service and specialist before choosing an appointment time.</p>
      <Link className="underline" href={`/shops/${shopSlug}/booking`}>Choose service and specialist</Link>
    </div>;
  }
  if (isError) {
    return <div role="alert" className="space-y-3">
      <p>Appointment times could not be loaded. Your selections have been retained.</p>
      <button type="button" className="underline" onClick={() => refetch()}>Try again</button>
    </div>;
  }
  if (isLoading) return <p role="status">Loading available appointment times…</p>;
  return (
    <div className="space-y-3">
      {data?.data?.map((item) => (
        <BookingDateTime
          key={item.service_master.id}
          data={item.times}
          shopSlug={shopSlug}
          serviceMasterId={item.service_master.id}
          isLoading={isLoading}
          isError={isError}
          master={item.service_master.master}
        />
      ))}
    </div>
  );
};
