"use client";

import { Listbox } from "@headlessui/react";
import type { ServiceExtras as ServiceExtrasType } from "@/types/service";
import { useTranslation } from "react-i18next";
import { RadioFillIcon } from "@/assets/icons/radio-fill";
import EmptyCheckIcon from "@/assets/icons/empty-check";
import { Price } from "@/components/price";

interface ServiceExtrasProps {
  extras?: ServiceExtrasType[];
  setSelectedExtras: (value: ServiceExtrasType[]) => void;
  selectedExtras: ServiceExtrasType[];
}

const ServiceExtras = ({ extras, setSelectedExtras, selectedExtras }: ServiceExtrasProps) => {
  const { t } = useTranslation();

  const selectedExtraIds = selectedExtras.map((extra) => extra.id);
  const handleChange = (value: number[]) => {
    const newValues = extras?.filter((extra) => value.includes(extra.id)) || [];
    setSelectedExtras(newValues);
  };

  return (
    <div className="block my-10">
      <Listbox value={selectedExtraIds} onChange={handleChange} multiple>
        <Listbox.Label className="block mb-5 font-semibold text-xl">
          {t("select.extra")}
        </Listbox.Label>
        <div className="space-y-2">
          {extras?.map((extra) => (
            <Listbox.Option
              key={extra?.id}
              value={extra.id}
              className={({ selected }) =>
                `${
                  selected ? "border-dark" : "border-gray-link"
                } relative flex cursor-pointer rounded-lg px-5 py-3 focus:outline-none border`
              }
            >
              {({ selected }) => (
                <div className="flex w-full items-center justify-between">
                  <div className="flex items-center gap-3">
                    {selected ? (
                      <RadioFillIcon size={18} />
                    ) : (
                      <span className="text-gray-link">
                        <EmptyCheckIcon size={18} />
                      </span>
                    )}
                    <div className="text-md flex flex-col gap-y-1">
                      <Listbox.Label as="p" className="font-medium">
                        {extra?.translation?.title}
                      </Listbox.Label>
                      <Listbox.Label as="p" className="font-medium text-md">
                        <Price number={extra?.price || 0} />
                      </Listbox.Label>
                    </div>
                  </div>
                </div>
              )}
            </Listbox.Option>
          ))}
        </div>
      </Listbox>
    </div>
  );
};

export default ServiceExtras;
