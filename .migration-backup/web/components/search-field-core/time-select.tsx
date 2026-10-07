import React from "react";
import DatePicker from "react-datepicker";

import "react-datepicker/dist/react-datepicker.css";
import clsx from "clsx";
import dayjs from "dayjs";
import { useTranslation } from "react-i18next";
import AnchorDownIcon from "@/assets/icons/anchor-down";
import { useHourFormat } from "@/hook/use-hour-format";

interface DateSelectProps {
  label?: string;
  value?: Date;
  onChange: (value: Date) => void;
  showPastTime?: boolean;
}

const filterPassedTime = (time: Date) => {
  const currentDate = new Date();
  const selectedDate = new Date(time);

  return currentDate.getTime() < selectedDate.getTime();
};

export const TimeSelect = ({ label, showPastTime, value, onChange }: DateSelectProps) => {
  const { t } = useTranslation();
  const { hourFormat } = useHourFormat();
  return (
    <div className="flex-1">
      <DatePicker
        selected={value}
        customInput={
          <button
            type="button"
            aria-label={value ? dayjs(value).format(hourFormat) : t(label || "select.time")}
            className={clsx(
              "min-h-11 py-3 px-3 rounded-button text-base border border-gray-link flex items-center justify-between min-w-0 w-full focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary",
              !value && "text-gray-field"
            )}
          >
            {value ? dayjs(value).format(hourFormat) : t(label || "")}
            <AnchorDownIcon />
          </button>
        }
        showPopperArrow={false}
        closeOnScroll
        showTimeSelect
        showTimeSelectOnly
        filterTime={
          showPastTime || typeof showPastTime === "undefined" ? filterPassedTime : undefined
        }
        timeCaption="Time"
        timeFormat={hourFormat}
        dateFormat={hourFormat}
        // eslint-disable-next-line react/no-unstable-nested-components
        calendarContainer={({ children }) => (
          <div
            className={clsx(
              "border-none relative bg-white rounded-button p-2.5 inline-block shadow-storeCard w-full"
            )}
          >
            {children}
          </div>
        )}
        onChange={(date) => {
          if (date) {
            onChange(date);
          }
        }}
        onSelect={(date) => onChange(date)}
      />
    </div>
  );
};
