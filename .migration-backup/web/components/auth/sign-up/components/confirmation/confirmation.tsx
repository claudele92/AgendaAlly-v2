import { setCookie } from "cookies-next";
import { useAuth } from "@/hook/use-auth";
import { useTranslation } from "react-i18next";
import { ConfirmationResult } from "@firebase/auth";
import { useCountDown } from "@/hook/use-countdown";
import { error, success } from "@/components/alert";
import React, { useEffect, useState } from "react";
import { SignUpViews } from "@/components/auth/types";
import OTPInput from "react-otp-input";
import clsx from "clsx";
import { Button } from "@/components/button";
import { useSettings } from "@/hook/use-settings";
import { authService } from "@/services/auth";
import { SignInResponse } from "@/types/user";
import { userService } from "@/services/user";
import { useFcmToken } from "@/hook/use-fcm-token";
import { useSyncServer } from "@/hook/use-sync-server";

interface OtpConfirmationProps {
  credential?: string;
  confirmationResult?: ConfirmationResult;
  onSuccess: (value: string) => void;
  onChangeView: (view: SignUpViews) => void;
  onBack?: () => void;
}

const OtpConfirmation = ({
  confirmationResult,
  credential,
  onSuccess,
  onChangeView,
  onBack,
}: OtpConfirmationProps) => {
  const { t } = useTranslation();
  const { fcmToken } = useFcmToken();
  const { handleSync } = useSyncServer();
  const [otp, setOtp] = useState("");
  const [isSubmitting, setIsSubmitting] = useState(false);
  const { settings } = useSettings();
  const waitTime = settings?.otp_expire_time ? Number(settings.otp_expire_time) * 60 : 60;
  const { counter: time, start: timerStart, reset: timerReset } = useCountDown(waitTime);
  const { phoneNumberSignIn } = useAuth();

  const handleSuccessSignUp = (res: SignInResponse) => {
    setCookie("token", `${res.token_type} ${res.access_token}`);
    if (fcmToken) {
      userService.updateFirebaseToken({ firebase_token: fcmToken });
    }
    handleSync();
  };

  const handleVerify = () => {
    setIsSubmitting(true);
    if (credential?.includes("@")) {
      authService
        .signUpVerifyEmail({ email: credential, otp })
        .then((res) => {
          onSuccess(res.data.token);
          handleSuccessSignUp(res.data);
          onChangeView("COMPLETE");
        })
        .catch((err) => error(err.message))
        .finally(() => setIsSubmitting(false));
    } else {
      confirmationResult
        ?.confirm(otp)
        .then(async (firebaseRes) => {
          const idToken = await firebaseRes.user.getIdToken();
          onSuccess(idToken);
          onChangeView("COMPLETE");
        })
        .catch(() => error(t("verify.error")))
        .finally(() => setIsSubmitting(false));
    }
  };

  const handleResendCode = () => {
    if (credential) {
      if (credential.includes("@")) {
        authService
          .signUp({ email: credential })
          .then(() => {
            timerReset();
            timerStart();
            success(t("verify.send"));
          })
          .catch((err) => error(err.message))
          .finally(() => setIsSubmitting(false));
      } else {
        phoneNumberSignIn(credential)
          .then(() => {
            timerReset();
            timerStart();
            success(t("verify.send"));
          })
          .catch(() => error(t("sms.not.sent")));
      }
    }
  };

  useEffect(() => {
    timerStart();
  }, []);

  return (
    <div className="flex flex-col gap-6 ">
      <h1 className="font-semibold text-[30px] mb-2 text-start">
        {credential?.includes("@") ? t("verify.email") : t("verify.phone")}
      </h1>
      <p className="text-sm">
        {t("verify.text")} <i>{credential}</i>
      </p>
      <OTPInput
        value={otp}
        onChange={setOtp}
        containerStyle={{ justifyContent: "space-between", gap: "10px" }}
        numInputs={6}
        renderInput={({ className, ...otherProps }) => (
          <input
            className={clsx(
              "flex-1 focus-visible:border-primary px-1 text-lg border-gray-inputBorder bg-transparent rounded-2xl border appearance-none focus:outline-none py-5 focus:ring-0",
              className
            )}
            {...otherProps}
          />
        )}
      />
      {credential?.includes("@") && (
        <div className="flex items-center gap-2">
          {time > 0 && <p className="text-sm text-gray-field">{time}s</p>}
          <button
            onClick={handleResendCode}
            className={clsx(
              "outline-none focus-ring max-w-max text-sm",
              time > 0 && "text-gray-field"
            )}
          >
            {t("resend")}
          </button>
        </div>
      )}
      <Button loading={isSubmitting} onClick={handleVerify} fullWidth disabled={otp.length < 6}>
        {t("verify")}
      </Button>
      {onBack && (
        <button type="button" className="aa-auth-back" onClick={onBack}>
          <span aria-hidden="true">←</span>
          {t("back", { defaultValue: "Back to contact details" })}
        </button>
      )}
    </div>
  );
};

export default OtpConfirmation;
