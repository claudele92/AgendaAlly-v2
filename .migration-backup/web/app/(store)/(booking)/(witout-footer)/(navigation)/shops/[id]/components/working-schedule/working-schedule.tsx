"use client";

import { WorkingDay } from "@/types/shop";
import { Translate } from "@/components/translate";
import { useHourFormat } from "@/hook/use-hour-format";
import dayjs from "dayjs";

interface WorkingScheduleProps {
  data?: WorkingDay[];
}

export const WorkingSchedule = ({ data }: WorkingScheduleProps) => {
  const { hourFormat } = useHourFormat();
  return (
    <div>
      <div className="rounded-button py-5 px-5 border border-gray-link">
        <h2 className="text-xl font-semibold">
          <Translate value="business.hours" />
        </h2>
        {data?.length ? (
          <>
            <p className="aa-s2-profile-hours-note">
              Shop-level hours are shown; branch-specific hours are not provided here.
            </p>
            <ul>
              {data.map((schedule) => (
                <li key={schedule.day} className="text-lg flex items-center justify-between mt-5">
                  <span>
                    <Translate value={schedule.day} />
                  </span>
                  {schedule?.disabled ? (
                    <span>
                      <Translate value="closed" />
                    </span>
                  ) : (
                    <span>
                      {dayjs(schedule.from, "HH:mm").format(hourFormat)} -{" "}
                      {dayjs(schedule.to, "HH:mm").format(hourFormat)}
                    </span>
                  )}
                </li>
              ))}
            </ul>
          </>
        ) : (
          <p className="aa-s2-profile-empty">
            Business hours have not been provided.
          </p>
        )}
      </div>
    </div>
  );
};
