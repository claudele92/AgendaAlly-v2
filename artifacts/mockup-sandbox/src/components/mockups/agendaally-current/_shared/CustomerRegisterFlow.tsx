import React, { useState } from "react";
import { useForm } from "react-hook-form";
import { Button, Input, InputTypeChanger, Link, PhoneInput } from "./AuthPrimitives";
import { useTranslation } from "./translation";

type RegisterValues = {
  email?: string;
  phone?: string;
  agreed?: boolean;
  firstname?: string;
  lastname?: string;
  referral?: string;
  password?: string;
  password_confirmation?: string;
};

type RegisterView = "SIGNUP" | "VERIFY" | "COMPLETE";

export function CustomerRegisterFlow() {
  const { t } = useTranslation();
  const [currentView, setCurrentView] = useState<RegisterView>("SIGNUP");
  const [currentType, setCurrentType] = useState<"email" | "phone">("email");
  const [credential, setCredential] = useState("");
  const [otp, setOtp] = useState("");
  const [previewMessage, setPreviewMessage] = useState("");
  const {
    register,
    handleSubmit,
    watch,
    setValue,
    setError,
    clearErrors,
    formState: { errors },
  } = useForm<RegisterValues>();

  const phoneRegistration = register("phone", {
    required: currentType === "phone" ? "Required" : false,
  });
  const agreed = watch("agreed");

  const handleCheckCredential = (data: RegisterValues) => {
    const selectedCredential = currentType === "email"
      ? data.email
      : data.phone?.startsWith("+") ? data.phone : `+${data.phone || ""}`;
    if (!selectedCredential) return;
    setCredential(selectedCredential);
    setPreviewMessage("Local preview only — no verification request was sent.");
    setCurrentView("VERIFY");
  };

  const handleVerifyPreview = () => {
    if (otp.length !== 6) return;
    setPreviewMessage("Local preview only — the verification code was not checked.");
    setCurrentView("COMPLETE");
  };

  const handleCompletePreview = handleSubmit((data) => {
    if (data.password !== data.password_confirmation) {
      setError("password_confirmation", {
        type: "validate",
        message: "Passwords do not match",
      });
      return;
    }
    setPreviewMessage("Local preview only — no account was created or submitted.");
  });

  if (currentView === "VERIFY") {
    return (
      <div className="flex flex-col gap-6">
        <h1 className="mb-2 text-start text-[30px] font-semibold">
          {t(credential.includes("@") ? "verify.email" : "verify.phone")}
        </h1>
        <p className="text-sm">
          {t("verify.text")} <i>{credential}</i>
        </p>
        <div className="flex justify-between gap-[10px]">
          {Array.from({ length: 6 }, (_, index) => (
            <input
              key={index}
              aria-label={`Verification digit ${index + 1}`}
              inputMode="numeric"
              maxLength={1}
              value={otp[index] || ""}
              onChange={(event) => {
                const digit = event.target.value.replace(/\D/g, "").slice(-1);
                const next = otp.split("");
                next[index] = digit;
                setOtp(next.join("").slice(0, 6));
              }}
              className="flex-1 appearance-none rounded-2xl border border-gray-inputBorder bg-transparent px-1 py-5 text-lg focus:outline-none focus:ring-0 focus-visible:border-primary"
            />
          ))}
        </div>
        <div className="flex items-center gap-2">
          <button
            type="button"
            onClick={() => setPreviewMessage("Local preview only — a new verification code was not sent.")}
            className="outline-none text-sm focus-ring"
          >
            {t("resend")}
          </button>
        </div>
        <Button fullWidth disabled={otp.length < 6} onClick={handleVerifyPreview}>
          {t("verify")}
        </Button>
        {previewMessage && <p role="status" className="text-sm text-gray-field">{previewMessage}</p>}
      </div>
    );
  }

  if (currentView === "COMPLETE") {
    return (
      <div className="flex flex-col gap-6">
        <h1 className="mb-2 text-start text-[30px] font-semibold">{t("complete.auth")}</h1>
        <form id="complete" onSubmit={handleCompletePreview}>
          <div className="mb-8 flex w-full flex-col gap-3">
            <Input
              fullWidth
              {...register("firstname", { required: "Required" })}
              label={t("firstname")}
              error={errors.firstname?.message}
            />
            <Input
              fullWidth
              {...register("lastname", { required: "Required" })}
              label={t("lastname")}
              error={errors.lastname?.message}
            />
            <Input
              fullWidth
              {...register("referral")}
              label={t("referral.(optional)")}
              error={errors.referral?.message}
            />
            <Input
              fullWidth
              {...register("password", { required: "Required" })}
              label={t("password")}
              type="password"
              error={errors.password?.message}
            />
            <Input
              fullWidth
              {...register("password_confirmation", { required: "Required" })}
              label={t("password.confirmation")}
              type="password"
              error={errors.password_confirmation?.message}
            />
          </div>
          <Button fullWidth type="submit" form="complete">
            {t("complete")}
          </Button>
        </form>
        {previewMessage && <p role="status" className="text-sm text-gray-field">{previewMessage}</p>}
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-6">
      <h1 className="mb-2 text-start text-[30px] font-semibold">{t("sign.up")}</h1>
      <InputTypeChanger value={currentType} onChange={setCurrentType} />
      <form id="signUp" onSubmit={handleSubmit(handleCheckCredential)}>
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
          <div className="mt-2.5 flex items-center">
            <input
              id="link-checkbox"
              type="checkbox"
              {...register("agreed", { required: true })}
              className="h-4 w-4 rounded-full border border-gray-inputBorder bg-gray-100 accent-primary focus:ring-2 focus:ring-primary"
            />
            <label htmlFor="link-checkbox" className="ml-2 text-sm font-medium">
              {t("i.agree.with")}{" "}
              <Link href="/terms" className="text-primary hover:underline">
                {t("terms.and.conditions")}
              </Link>
            </label>
          </div>
        </div>
      </form>
      <Button
        id="sign-in-button"
        form="signUp"
        type="submit"
        disabled={!agreed}
        fullWidth
      >
        {t("sign.up")}
      </Button>
      {previewMessage && <p role="status" className="text-sm text-gray-field">{previewMessage}</p>}
    </div>
  );
}