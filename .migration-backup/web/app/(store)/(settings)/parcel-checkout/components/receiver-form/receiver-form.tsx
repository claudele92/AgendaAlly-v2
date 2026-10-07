import { Input } from "@/components/input";
import { useTranslation } from "react-i18next";
import { PhoneInput } from "@/components/phone-input";
import { TextArea } from "@/components/text-area";
import { Switch } from "@/components/switch";
import { Button } from "@/components/button";
import { useFormContext } from "react-hook-form";
import { ParcelFormValues } from "@/types/parcel";
import { useQuery } from "@tanstack/react-query";
import { parcelService } from "@/services/parcel";
import { Price } from "@/components/price";
import { useSettings } from "@/hook/use-settings";
import useAddressStore from "@/global-store/address";
import clsx from "clsx";

export const ReceiverForm = ({ isSubmitting }: { isSubmitting: boolean }) => {
  const { t } = useTranslation();
  const { currency } = useSettings();
  const country = useAddressStore((state) => state.country);
  const {
    setValue,
    watch,
    register,
    formState: { errors },
  } = useFormContext<ParcelFormValues>();
  const locationFrom = watch("location_from");
  const locationTo = watch("location_to");
  const type = watch("type");
  const handleClickOption = (text?: string) => {
    if (!text) return;
    if (watch("description")?.length) {
      if (watch("description")?.includes(text)) {
        setValue("description", watch("description")?.replace(text, ""));
        return;
      }
      setValue("description", `${watch("description")} ${text}`);
      return;
    }
    setValue("description", text);
  };
  const { data: price, isError } = useQuery(
    ["calculateParcel", locationFrom, locationTo, type, currency],
    () =>
      parcelService.calculate({
        "address_from[latitude]": locationFrom.lat,
        "address_from[longitude]": locationFrom.lng,
        "address_to[latitude]": locationTo.lat,
        "address_to[longitude]": locationTo.lng,
        type_id: type.id,
        currency_id: currency?.id,
      }),
    {
      enabled: Boolean(type),
      select: (data) => data.data.price,
      retry: false,
      meta: {
        showErrorMessageFromServer: true,
      },
    }
  );
  return (
    <div className="lg:col-span-1 col-span-2">
      <h2 className="text-lg font-medium mb-7">{t("receiver.details")}</h2>
      <div className="grid sm:grid-cols-2 grid-cols-1 gap-4">
        <PhoneInput
          country={country?.code || process.env.NEXT_PUBLIC_DEFAULT_COUNTRY_CODE}
          value={watch("phone_to")}
          onChange={(value) => setValue("phone_to", value)}
          error={errors.phone_to?.message}
        />
        <Input
          fullWidth
          label={t("username")}
          {...register("username_to")}
          error={errors.username_to?.message}
          required
        />
        <Input
          fullWidth
          label={t("house")}
          {...register("house_to")}
          error={errors.house_to?.message}
          required
        />
        <Input
          fullWidth
          label={t("stage")}
          {...register("stage_to")}
          required
          error={errors.stage_to?.message}
        />
        <Input
          fullWidth
          label={t("room")}
          {...register("room_to")}
          error={errors.room_to?.message}
          required
        />
        <div className="sm:col-span-2">
          <Input
            fullWidth
            label={t("instructions")}
            {...register("instructions")}
            error={errors.instructions?.message}
          />
        </div>
        <div className="sm:col-span-2 mt-7">
          <h3 className="text-lg font-medium mb-2">{t("item.description")}</h3>
          <TextArea
            rows={3}
            placeholder={`${t("description")}*`}
            {...register("description")}
            error={errors.description?.message}
          />
        </div>
        {!!type?.options?.length && (
          <div className="sm:col-span-2 mt-1 flex flex-wrap gap-x-2 gap-y-1.5">
            {type?.options?.map((option) => (
              <button
                type="button"
                key={option?.id}
                onClick={() => handleClickOption(option?.translation?.title)}
                className={clsx(
                  "font-normal text-sm border border-gray-inputBorder rounded-[8px] px-3 py-2 active:translate-y-1 transition-all duration-200",
                  option?.translation?.title &&
                    watch("description")?.includes(option?.translation?.title)
                    ? "bg-primary text-white border-transparent"
                    : "bg-white"
                )}
              >
                {option?.translation?.title}
              </button>
            ))}
          </div>
        )}
        <div className="sm:col-span-2">
          <TextArea
            rows={3}
            placeholder={`${t("item.value.qr")}*`}
            {...register("qr_value")}
            error={errors.qr_value?.message}
          />
        </div>
        <div className="sm:col-span-2 rounded-2xl bg-gray-segment dark:bg-gray-darkSegment flex items-center justify-between py-3 px-4">
          <div className="flex flex-col">
            <strong>{t("remain.anonymous")}</strong>
            <span>{t("dont.notify.a.receipt")}</span>
          </div>
          <Switch value={watch("notify")} onChange={(value) => setValue("notify", value)} />
        </div>
        <Button
          loading={isSubmitting}
          fullWidth
          className="sm:col-span-2 mt-4"
          disabled={isError}
          type="submit"
        >
          {t("pay")} {!!price && <Price number={price} />}
        </Button>
      </div>
    </div>
  );
};
