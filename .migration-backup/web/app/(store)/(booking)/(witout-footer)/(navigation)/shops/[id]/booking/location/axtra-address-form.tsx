"use client";

import { Input } from "@/components/input";
import { PhoneInput } from "@/components/phone-input";
import { Types } from "@/context/booking/booking.reducer";
import React from "react";
import { useTranslation } from "react-i18next";
import useAddressStore from "@/global-store/address";
import { useBooking } from "@/context/booking";
import useUserStore from "@/global-store/user";
import * as yup from "yup";
import { useForm } from "react-hook-form";
import { yupResolver } from "@hookform/resolvers/yup";
import { useRouter, useSearchParams } from "next/navigation";
import { useQuery } from "@tanstack/react-query";
import { userService } from "@/services/user";

const schema = yup
  .object({
    firstname: yup.string().required().max(255),
    lastname: yup.string().required().max(255),
    phone: yup.string().required().max(255),
    zipcode: yup.string().required().max(255),
    street_house_number: yup.string().required().max(255),
    additional_details: yup.string().max(255),
  })
  .required();
type FormData = yup.InferType<typeof schema>;

interface ExtraAddressFormProps {
  shopSlug: string;
}

export const ExtraAddressForm = ({ shopSlug }: ExtraAddressFormProps) => {
  const router = useRouter();
  const searchParams = useSearchParams();
  const { t } = useTranslation();
  const { state, dispatch } = useBooking();
  const country = useAddressStore((countryState) => countryState.country);
  const user = useUserStore((userState) => userState.user);
  const { data: profile } = useQuery({
    queryKey: ["profile"],
    queryFn: () => userService.profile(),
    enabled: !!user,
  });

  const initialData = state.extraAddress ?? {};

  const {
    register,
    handleSubmit,
    formState: { errors },
    setValue,
    watch,
  } = useForm<FormData>({
    resolver: yupResolver(schema),
    defaultValues: {
      firstname: initialData.firstname ?? profile?.data?.firstname,
      lastname: initialData.lastname ?? profile?.data?.lastname,
      zipcode: initialData.zipcode ?? "",
      additional_details: initialData.additional_details ?? "",
      phone: initialData.phone ?? profile?.data?.phone,
      street_house_number: initialData.street_house_number ?? "",
    },
  });

  const onSubmit = (values: FormData) => {
    dispatch({
      type: Types.SetExtraAddress,
      payload: values,
    });

    router.push(
      `/shops/${shopSlug}${"/booking/note"}${
        searchParams.toString() ? `?${searchParams.toString()}` : ""
      }`
    );
  };

  return (
    <form id="external-address-form" onSubmit={handleSubmit(onSubmit)}>
      <div className="grid grid-cols-4 gap-4 mt-3">
        <div className="col-span-2">
          <Input
            fullWidth
            label={t("first.name")}
            {...register("firstname")}
            error={errors.firstname?.message}
          />
        </div>
        <div className="col-span-2">
          <Input
            className="h-full"
            fullWidth
            label={t("last.name")}
            {...register("lastname")}
            error={errors.lastname?.message}
          />
        </div>
        <div className="col-span-2">
          <PhoneInput
            inputClass="!py-[22px] !rounded-xl"
            country={country?.code || process.env.NEXT_PUBLIC_DEFAULT_COUNTRY_CODE}
            onChange={(value) => setValue("phone", value)}
            value={watch("phone")}
            error={errors.phone?.message}
          />
        </div>
        <div className="col-span-2">
          <Input
            className="h-full"
            fullWidth
            label={t("zip.code")}
            type="number"
            {...register("zipcode")}
            error={errors.zipcode?.message}
          />
        </div>
        <div className="col-span-4">
          <Input
            fullWidth
            label={t("home.number")}
            required
            {...register("street_house_number")}
            error={errors.street_house_number?.message}
          />
        </div>
        <div className="col-span-4">
          <Input
            fullWidth
            label={t("detail")}
            {...register("additional_details")}
            error={errors.additional_details?.message}
          />
        </div>
      </div>
    </form>
  );
};
