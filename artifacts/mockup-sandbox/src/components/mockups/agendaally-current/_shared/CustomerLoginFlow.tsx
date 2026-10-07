import React, { useState } from "react";
import { useForm } from "react-hook-form";
import { Button, Input, InputTypeChanger, PhoneInput } from "./AuthPrimitives";
import { useTranslation } from "./translation";

type LoginValues = {
  email?: string;
  phone?: string;
  password?: string;
};

export function CustomerLoginFlow() {
  const { t } = useTranslation();
  const [currentType, setCurrentType] = useState<"email" | "phone">("email");
  const [previewMessage, setPreviewMessage] = useState("");
  const {
    register,
    handleSubmit,
    watch,
    setValue,
    formState: { errors },
  } = useForm<LoginValues>();
  const phoneRegistration = register("phone", {
    required: currentType === "phone" ? "Required" : false,
  });
  const handleLogin = handleSubmit(() => {
    setPreviewMessage("Local preview only — no sign-in request was sent.");
  });

  return (
    <div className="flex flex-col gap-6">
      <h1 className="mb-2 text-start text-[30px] font-semibold">{t("login")}</h1>
      <div />
      <InputTypeChanger value={currentType} onChange={setCurrentType} />
      <form onSubmit={handleLogin}>
        <div className="mb-3 flex w-full flex-col gap-3">
          {currentType === "phone" ? (
            <PhoneInput
              {...phoneRegistration}
              value={watch("phone")}
              error={errors.phone?.message}
              onChange={(value) => setValue("phone", value, { shouldValidate: true })}
            />
          ) : (
            <Input
              {...register("email", {
                required: currentType === "email" ? "Required" : false,
                pattern: currentType === "email"
                  ? { value: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, message: "Enter a valid email address" }
                  : undefined,
              })}
              error={errors.email?.message}
              fullWidth
              label={t("email")}
            />
          )}
          <Input
            {...register("password", { required: "Required" })}
            error={errors.password?.message}
            fullWidth
            label={t("password")}
            type="password"
          />
        </div>
        <div className="mb-10 flex items-center justify-end">
          <button
            type="button"
            onClick={() => setPreviewMessage("Password recovery is not connected in this local preview.")}
            className="text-end font-medium"
          >
            {t("forgot.password")}
          </button>
        </div>
        <Button type="submit" fullWidth>
          {t("sign.in")}
        </Button>
        {previewMessage && <p role="status" className="mt-4 text-sm text-gray-field">{previewMessage}</p>}
      </form>
    </div>
  );
}