import { Input } from "@/components/input";
import { Button } from "@/components/button";
import React, { useState } from "react";
import { useTranslation } from "react-i18next";
import * as yup from "yup";
import { useForm } from "react-hook-form";
import { yupResolver } from "@hookform/resolvers/yup";
import { ConfirmationResult } from "@firebase/auth";
import { error } from "@/components/alert";
import { useAuth } from "@/hook/use-auth";
import { ForgotPasswordViews } from "@/components/auth/types";
import { authService } from "@/services/auth";
import useAddressStore from "@/global-store/address";
import { InputTypeChanger } from "@/app/(auth)/input-type-changer";
import { PhoneInput } from "@/components/phone-input";

interface ForgotPasswordFormProps {
  onChangeView: (view: ForgotPasswordViews) => void;
  onSuccess: (data: { credential: string; callback?: ConfirmationResult }) => void;
}

const Form = ({ onChangeView, onSuccess }: ForgotPasswordFormProps) => {
  const { t } = useTranslation();
  const country = useAddressStore((state) => state.country);
  const { phoneNumberSignIn } = useAuth();
  const [currentType, setCurrentType] = useState<"email" | "phone">("email");
  const [isSubmitting, setIsSubmitting] = useState(false);
  const schema = yup.object({
    email:
      currentType === "email" ? yup.string().email().required() : yup.string().email().optional(),
    phone: currentType === "phone" ? yup.string().required(t("required")) : yup.string().optional(),
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
    setIsSubmitting(true);
    if (currentType === "email") {
      authService
        .forgotPasswordEmail({ email: data.email as string })
        .then(() => {
          onSuccess({
            credential: data.email as string,
          });
          onChangeView("VERIFY");
        })
        .catch((err) => {
          error(err.message);
        })
        .finally(() => {
          setIsSubmitting(false);
        });
    } else {
      phoneNumberSignIn(data.phone as string)
        .then((value) => {
          onSuccess({
            credential: data.phone as string,
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
      <h1 className="font-semibold text-[30px] mb-2 text-start">{t("forgot.password")}</h1>
      <InputTypeChanger value={currentType} onChange={setCurrentType} />
      <form id="forgot" onSubmit={handleSubmit(handleCheckCredential)}>
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
              error={errors.email?.message}
              fullWidth
              label={t("email")}
            />
          )}
        </div>
      </form>
      <Button id="sign-in-button" loading={isSubmitting} form="forgot" type="submit" fullWidth>
        {t("submit")}
      </Button>
      {currentType === "email" && (
        <Button
          type="button"
          color="transparent"
          fullWidth
          disabled={isSubmitting}
          onClick={handleSubmit((data) => {
            if (!data.email) return;
            // Resume normal code entry only: no request, resend or bypass.
            // The server still validates the code against this email.
            onSuccess({ credential: data.email });
            onChangeView("VERIFY");
          })}
        >
          {t("reset.already.have.code", { defaultValue: "I already have a reset code" })}
        </Button>
      )}
    </div>
  );
};

export default Form;
