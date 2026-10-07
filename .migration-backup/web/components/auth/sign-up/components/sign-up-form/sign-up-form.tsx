import { Input } from "@/components/input";
import Link from "next/link";
import { Button } from "@/components/button";
import React, { useState } from "react";
import { useTranslation } from "react-i18next";
import * as yup from "yup";
import { useForm } from "react-hook-form";
import { yupResolver } from "@hookform/resolvers/yup";
import { SignUpViews } from "@/components/auth/types";
import { ConfirmationResult } from "@firebase/auth";
import { error } from "@/components/alert";
import { useAuth } from "@/hook/use-auth";
import { authService } from "@/services/auth";
import { InputTypeChanger } from "@/app/(auth)/input-type-changer";
import { PhoneInput } from "@/components/phone-input";
import useAddressStore from "@/global-store/address";
import { LegalPolicyLinks } from "@/components/legal-policy-links";

interface SignUpFormProps {
  onChangeView: (view: SignUpViews) => void;
  onSuccess: (data: { credential: string; callback?: ConfirmationResult }) => void;
}

const SignUpForm = ({ onChangeView, onSuccess }: SignUpFormProps) => {
  const { t } = useTranslation();
  const country = useAddressStore((state) => state.country);
  const { phoneNumberSignIn } = useAuth();
  const [currentType, setCurrentType] = useState<"email" | "phone">("email");
  const [isSubmitting, setIsSubmitting] = useState(false);
  const schema = yup.object({
    email: currentType === "email" ? yup.string().email().required() : yup.string().optional(),
    phone: currentType === "phone" ? yup.string().required(t("required")) : yup.string().optional(),
    agreed: yup.boolean().required(),
  });
  type FormData = yup.InferType<typeof schema>;
  const {
    register,
    formState: { errors },
    handleSubmit,
    watch,
    setValue,
  } = useForm<FormData>({
    resolver: yupResolver(schema),
  });

  const handleCheckCredential = (data: FormData) => {
    if (currentType === "email") {
      authService
        .signUp({ email: data.email as string })
        .then(() => {
          onSuccess({
            credential: data.email as string,
          });
          onChangeView("VERIFY");
        })
        .catch((err) => error(err.message))
        .finally(() => setIsSubmitting(false));
    } else {
      const phoneNumber = data.phone?.startsWith("+") ? data.phone : `+${data.phone}`;
      phoneNumberSignIn(phoneNumber as string)
        .then((value) => {
          onSuccess({
            credential: phoneNumber as string,
            callback: value,
          });
          onChangeView("VERIFY");
        })
        .catch(() => {
          error(t("sms.not.sent"));
        })
        .finally(() => {
          setIsSubmitting(false);
        });
    }
  };

  return (
    <div className="flex flex-col gap-6 ">
      <h1 className="font-semibold text-[30px] mb-2 text-start">{t("sign.up")}</h1>
      <InputTypeChanger value={currentType} onChange={setCurrentType} />
      <form id="signUp" onSubmit={handleSubmit(handleCheckCredential)}>
        <div className="flex flex-col gap-3 mb-3 w-full">
          {currentType === "phone" ? (
            <PhoneInput
              country={country?.code}
              value={watch("phone")}
              error={errors.phone?.message}
              onChange={(value) => setValue("phone", value)}
            />
          ) : (
            <Input
              {...register("email")}
              type="email"
              error={errors.email?.message}
              fullWidth
              label={t("email")}
            />
          )}
          <div className="flex items-center mt-2.5">
            <input
              id="link-checkbox"
              type="checkbox"
              {...register("agreed")}
              className="w-4 h-4 accent-primary bg-gray-100 border-gray-inputBorder rounded-full focus:ring-primary focus:ring-2"
            />
            <label htmlFor="link-checkbox" className="ml-2 text-sm font-medium">
              {t("i.agree.with")}{" "}
              <Link href="/terms" className="text-primary hover:underline">
                {t("terms.and.conditions")}
              </Link>
            </label>
          </div>
          <LegalPolicyLinks current="terms" />
        </div>
      </form>
      <Button
        id="sign-in-button"
        loading={isSubmitting}
        form="signUp"
        type="submit"
        disabled={!watch("agreed")}
        fullWidth
      >
        {t("sign.up")}
      </Button>
    </div>
  );
};

export default SignUpForm;
