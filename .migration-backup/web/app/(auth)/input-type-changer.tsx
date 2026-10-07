import clsx from "clsx";
import PhoneFillIcon from "remixicon-react/PhoneFillIcon";
import MailFillIcon from "remixicon-react/MailFillIcon";
import React from "react";
import { Translate } from "@/components/translate";
import { useTranslation } from "react-i18next";

interface InputTypeChangerProps {
  value: "email" | "phone";
  onChange: (type: "email" | "phone") => void;
}

export const InputTypeChanger = ({ value, onChange }: InputTypeChangerProps) => {
  const { t } = useTranslation();

  return (
    <div
      className="aa-contact-switch grid grid-cols-2 border border-gray-inputBorder rounded-2xl p-1 relative"
      role="group"
      aria-label={t("choose.contact.method", { defaultValue: "Choose contact method" })}
    >
      <div
        className={clsx(
          "w-1/2 h-[calc(100%-8px)] absolute top-1 bg-primary rounded-[14px] transition-transform duration-200",
          value === "email" ? "translate-x-1" : "translate-x-[calc(100%-4px)]"
        )}
      />
      <button
        type="button"
        aria-pressed={value === "email"}
        className={clsx(
          "flex justify-center items-center gap-x-2.5 py-3 px-4 z-[1] transition-colors duration-200",
          value === "email" && "text-white"
        )}
        onClick={() => onChange("email")}
      >
        <MailFillIcon size={20} />
        <span className="sm:font-medium font-normal sm:text-lg text-base leading-base tracking-[-2%] line-clamp-1">
          <Translate value="email" />
        </span>
      </button>
      <button
        type="button"
        aria-pressed={value === "phone"}
        className={clsx(
          "flex justify-center items-center gap-x-2.5 py-3 px-4 z-[1] transition-colors duration-200",
          value === "phone" && "text-white"
        )}
        onClick={() => onChange("phone")}
      >
        <PhoneFillIcon size={20} />
        <span className="sm:font-medium font-normal sm:text-lg text-base leading-base tracking-[-2%] line-clamp-1">
          <Translate value="phone" />
        </span>
      </button>
    </div>
  );
};
