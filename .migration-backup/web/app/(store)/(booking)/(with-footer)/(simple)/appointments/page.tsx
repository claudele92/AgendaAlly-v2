"use client";

import { useRef, useState } from "react";
import { Booking } from "@/types/booking";
import dynamic from "next/dynamic";
import { Modal } from "@/components/modal";
import { LoadingCard } from "@/components/loading";
import { useModal } from "@/hook/use-modal";
import { useMediaQuery } from "@/hook/use-media-query";
import { AppointmentsList } from "./components/list";

const AppointmentDetailPage = dynamic(
  () =>
    import("./components/detail").then((component) => ({
      default: component.AppointmentDetailPage,
    })),
  {
    loading: () => <LoadingCard />,
  }
);

interface AppointmentState {
  booking?: Booking;
  status: string;
}

const AppointmentsPage = () => {
  const [appointmentState, setAppointmentState] = useState<AppointmentState>({ status: "loading" });
  const [detailModalOpen, openDetailModal, closeDetailModal] = useModal();
  const isMobile = useMediaQuery("(max-width: 1280px)");
  const detailTriggerRef = useRef<HTMLButtonElement | null>(null);
  const handleChangeBooking = (value: Booking) => {
    setAppointmentState({ status: "success", booking: value });
  };
  const handleBookingError = () => {
    setAppointmentState({ status: "error", booking: undefined });
  };
  return (
    <section className="xl:container px-4">
      <div className="grid xl:grid-cols-5 grid-cols-3 gap-7 my-10">
        <div className="col-span-3">
          <AppointmentsList
            selectedBooking={appointmentState.booking}
            onSelectBooking={(value, trigger) => {
              detailTriggerRef.current = trigger || null;
              handleChangeBooking(value);
              openDetailModal();
            }}
            onError={handleBookingError}
          />
        </div>
        <div className="col-span-2 hidden xl:block">
          <div className="sticky top-7">
            <AppointmentDetailPage
              detail={appointmentState.booking}
              status={appointmentState.status}
            />
          </div>
        </div>
      </div>
      <Modal
        isOpen={detailModalOpen && isMobile}
        onClose={closeDetailModal}
        onAfterClose={() => {
          const trigger = detailTriggerRef.current;
          detailTriggerRef.current = null;
          if (trigger?.isConnected) trigger.focus();
        }}
        withCloseButton
      >
        <AppointmentDetailPage detail={appointmentState.booking} status={appointmentState.status} />
      </Modal>
    </section>
  );
};

export default AppointmentsPage;
