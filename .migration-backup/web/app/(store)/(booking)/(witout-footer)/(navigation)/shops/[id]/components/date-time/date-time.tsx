"use client";

import { Translate } from "@/components/translate";
import React, { useEffect, useState } from "react";
import clsx from "clsx";
import { IconButton } from "@/components/icon-button";
import AnchorLeftIcon from "@/assets/icons/anchor-left";
import { useBooking } from "@/context/booking";
import dayjs from "dayjs";
import { Types } from "@/context/booking/booking.reducer";
import { CaptionProps, DayPicker, useNavigation } from "react-day-picker";
import CalendarCheckLineIcon from "remixicon-react/CalendarTodoLineIcon";
import dynamic from "next/dynamic";
import { useTranslation } from "react-i18next";
import CrossIcon from "@/assets/icons/cross";
import { useRouter, useSearchParams } from "next/navigation";
import customParseFormat from "dayjs/plugin/customParseFormat";
import { Button } from "@/components/button";
import { BookingDate } from "@/types/booking";
import { Master } from "@/types/master";
import { ImageWithFallBack } from "@/components/image";
import { useHourFormat } from "@/hook/use-hour-format";

dayjs.extend(customParseFormat);

const ErrorFallback = dynamic(() => import("@/components/error-fallback"));

interface BookingDateTimeProps {
  withBorder?: boolean;
  serviceMasterId: number;
  shopSlug?: string;
  data?: BookingDate[];
  isLoading: boolean;
  isError: boolean;
  master?: Master;
}

const CustomCaption = ({ displayMonth }: CaptionProps) => {
  const { goToMonth, nextMonth, previousMonth } = useNavigation();
  return (
    <div className="flex items-center justify-evenly">
      <IconButton
        disabled={!previousMonth}
        onClick={() => previousMonth && goToMonth(previousMonth)}
      >
        <span className="text-gray-field">
          <AnchorLeftIcon size={16} />
        </span>
      </IconButton>
      <span className="text-sm font-semibold">{dayjs(displayMonth).format("MMMM YYYY")}</span>
      <IconButton disabled={!nextMonth} onClick={() => nextMonth && goToMonth(nextMonth)}>
        <span className="text-gray-field">
          <AnchorLeftIcon size={16} style={{ rotate: "180deg" }} />
        </span>
      </IconButton>
    </div>
  );
};

export const BookingDateTime = ({
  serviceMasterId,
  withBorder = true,
  shopSlug,
  data,
  isLoading,
  isError,
  master,
}: BookingDateTimeProps) => {
  const router = useRouter();
  const searchParams = useSearchParams();
  const { t } = useTranslation();
  const { hourFormat } = useHourFormat();
  const { state, dispatch } = useBooking();
  const [startDate, setStartDate] = useState<Date | undefined>(() => {
    const saved = state.dateAndTimes.find((item) => item.serviceMasterId === serviceMasterId)?.date;
    const date = saved ? new Date(saved) : new Date();
    return Number.isNaN(date.getTime()) ? new Date() : date;
  });
  const [monthChange, setMonthChange] = useState(startDate || new Date());
  const [handleGotoFlag, setHandleGotoFlag] = useState(false);
  const todayTimes = data?.find((item) => dayjs(item.date).isSame(startDate, "date"));
  const currentTimes = dayjs().isSame(todayTimes?.date, "date")
    ? todayTimes?.times.filter((time) => dayjs(time, "HH:mm").isAfter())
    : todayTimes?.times;
  const disabledDays =
    data?.filter((item) => item.closed).map((item) => dayjs(item.date).toDate()) || [];
  const handleClickTimeSlot = (slot: string) => {
    dispatch({
      type: Types.SetDateTime,
      payload: {
        date: startDate?.toString() || new Date().toString(),
        time: slot,
        serviceMasterId,
      },
    });
  };
  const handleChangeDate = (date?: Date) => {
    setStartDate(date);
    dispatch({ type: Types.ClearDateTime, payload: { serviceMasterId } });
  };

  const handleGoto = (date: Date) => {
    setStartDate(date);
    setMonthChange(date);
    setHandleGotoFlag((prev) => !prev);
  };

  const currentService = state?.services.find(
    (service) => service.master?.service_master?.id === serviceMasterId
  );

  const selectedTimeSlot = state.dateAndTimes?.find(
    (item) => item.serviceMasterId === serviceMasterId
  )?.time;
  const renderTimeSlot = () => {
    const currentTime = dayjs(startDate).isSame(new Date(), "day") ? new Date() : startDate;
    const nextAvailableSlot = data?.find((item) =>
      item.times.some(
        (time) => time && dayjs(`${item.date} ${time}`, "YYYY-MM-DD HH:mm").isAfter(currentTime)
      )
    );
    const nextAvailableDate = dayjs(nextAvailableSlot?.date).toDate();
    if (isLoading) {
      return (
        <div className="flex gap-2.5 flex-wrap overflow-y-auto md:max-h-[440px] animate-pulse">
          {Array.from(Array(20).keys()).map((item) => (
            <div key={item} className="rounded-button bg-gray-300 h-10 w-20" />
          ))}
        </div>
      );
    }
    if (isError)
      return (
        <div className="flex items-center h-full w-full justify-center">
          <ErrorFallback />
        </div>
      );
    if (currentTimes && currentTimes.length > 0) {
      return (
        <div className="flex gap-2.5 flex-wrap overflow-y-auto md:max-h-[440px]">
          {currentTimes.map((time) => (
            <button
                type="button"
                aria-pressed={selectedTimeSlot === time}
                aria-label={`${dayjs(startDate).format("dddd, MMMM D")}, ${dayjs(time, "HH:mm").format(hourFormat)}`}
              className={clsx(
                "text-sm min-h-11 py-2.5 px-5 rounded-button border border-dark focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary hover:text-white hover:bg-primary transition-all hover:border-transparent",
                selectedTimeSlot === time && "text-white bg-primary border-transparent"
              )}
              key={time}
              onClick={() => handleClickTimeSlot(time)}
            >
              {dayjs(time, "HH:mm").format(hourFormat)}
            </button>
          ))}
        </div>
      );
    }
    if (todayTimes?.closed) {
      return (
        <div className="flex items-center justify-center flex-col h-full gap-3">
          <CrossIcon size={40} />
          <span className="text-sm font-medium">{t("shop.is.closed")}</span>
          {nextAvailableSlot && (
            <div className="flex justify-center mt-7">
              <Button onClick={() => handleGoto(nextAvailableDate)} color="black" size="medium">
                {t("go.to")} {dayjs(nextAvailableDate).format("MMM DD")}
              </Button>
            </div>
          )}
        </div>
      );
    }
    return (
      <div className="flex items-center justify-center flex-col h-full gap-3">
        <CalendarCheckLineIcon size={40} />
        <span className="text-sm font-medium">{t("no.available.slots")}</span>
        {nextAvailableSlot && (
          <div className="flex justify-center mt-7">
            <Button onClick={() => handleGoto(nextAvailableDate)} color="black" size="medium">
              {t("go.to")} {dayjs(nextAvailableDate).format("MMM DD")}
            </Button>
          </div>
        )}
      </div>
    );
  };

  const serviceMasters = state.services.map((service) => service.master);

  useEffect(() => {
    if (serviceMasters.length === 0 && !serviceMasterId && shopSlug) {
      const query = searchParams.toString();
      router.replace(`/shops/${shopSlug}/booking${query ? `?${query}` : ""}`);
    }
  }, [serviceMasterId, serviceMasters.length, shopSlug]);

  return (
    <div
      className={clsx(
        " lg:col-span-2 ",
        withBorder && "lg:border border-gray-link rounded-button lg:py-6 lg:px-5"
      )}
    >
      <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-3 min-w-0">
        <h2 className="text-xl font-semibold">
          <Translate value="select.date.time" />
        </h2>
        <p className="font-semibold break-words min-w-0">{currentService?.translation?.title}</p>
        {master && (
          <div className="flex items-center gap-2 min-w-0">
            <div className="w-14 h-14 relative shrink-0">
              <ImageWithFallBack
                src={master?.img}
                alt={master?.firstname || "Specialist"}
                className="rounded-full object-cover"
                fill
              />
            </div>
            <div className="min-w-0">
              <p className="text-xl font-medium break-words">
                {master?.firstname} {master?.lastname}
              </p>
              <span className="text-gray-field text-base font-semibold">Specialist</span>
            </div>
          </div>
        )}
      </div>
      {selectedTimeSlot && (
        <div className="mt-4 rounded-button border border-gray-link bg-gray-50 px-4 py-3" role="status" aria-live="polite">
          <p className="text-xs uppercase tracking-wide text-gray-field">Selected appointment</p>
          <p className="mt-1 font-semibold">
            {dayjs(startDate).format("dddd, MMMM D")} · {dayjs(selectedTimeSlot, "HH:mm").format(hourFormat)}
          </p>
          <p className="text-sm text-gray-field">
            {currentService?.translation?.title || "Selected service"}
            {master ? ` · ${[master.firstname, master.lastname].filter(Boolean).join(" ")}` : ""}
          </p>
        </div>
      )}
      <div className="grid md:grid-cols-3 grid-cols-1 mt-6 lg:gap-x-7 gap-y-7">
        <div className="lg:border border-gray-link rounded-button col-span-1 md:col-span-2 min-w-0 lg:p-4">
          <DayPicker
            month={monthChange}
            onMonthChange={(month) => setMonthChange(month)}
            key={handleGotoFlag.toString()}
            mode="single"
            selected={startDate}
            onSelect={handleChangeDate}
            components={{ Caption: CustomCaption }}
            disabled={[...disabledDays, { before: new Date() }]}
            classNames={{
              root: "rdp mx-0 max-w-full",
              head: "text-gray-field text-xs font-medium tracking-widest",
              day: "text-base h-full rounded-full  aspect-square w-full",
              day_selected: "!bg-dark !text-white hover:!!bg-dark",
              table: "w-full max-w-full",
              month: "flex-1",
              head_cell: "py-7 font-medium",
              cell: "aspect-square h-full rounded-full ",
            }}
          />
        </div>
        <div className="border border-gray-link rounded-button py-6 px-5 ">{renderTimeSlot()}</div>
      </div>
    </div>
  );
};
