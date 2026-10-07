"use client";

import React, { useCallback, useState } from "react";
import dynamic from "next/dynamic";
import { ConfirmationResult } from "@firebase/auth";
import { LoadingCard } from "@/components/loading";
import { SignUpViews } from "../types";
import Link from "next/link";
import { useTranslation } from "react-i18next";

const SignUpForm = dynamic(() => import("./components/sign-up-form"), {
  loading: () => <LoadingCard />,
});
const OtpVerify = dynamic(() => import("./components/confirmation"), {
  loading: () => <LoadingCard />,
});
const AuthComplete = dynamic(() => import("./components/complete"), {
  loading: () => <LoadingCard />,
});

const SignUp = () => {
  const { t } = useTranslation();
  const [currentView, setCurrentView] = useState<SignUpViews>("SIGNUP");
  const [confirmationResult, setConfirmationResult] = useState<ConfirmationResult | undefined>();
  const [currentCredential, setCredential] = useState<string | undefined>();
  const handleChangeView = useCallback((view: SignUpViews) => setCurrentView(view), []);
  const [idToken, setIdToken] = useState<string | undefined>();
  const renderView = () => {
    switch (currentView) {
      case "SIGNUP":
        return (
          <SignUpForm
            onChangeView={handleChangeView}
            onSuccess={({ credential, callback }) => {
              setCredential(credential);
              setConfirmationResult(callback);
            }}
          />
        );
      case "VERIFY":
        return (
          <OtpVerify
            confirmationResult={confirmationResult}
            credential={currentCredential}
            onChangeView={handleChangeView}
            onSuccess={(value) => setIdToken(value)}
            onBack={() => handleChangeView("SIGNUP")}
          />
        );
      case "COMPLETE":
        return (
          <AuthComplete
            idToken={idToken}
            credential={currentCredential}
            onBackToVerify={() => handleChangeView("VERIFY")}
          />
        );

      default:
        return (
          <SignUpForm
            onChangeView={handleChangeView}
            onSuccess={({ credential, callback }) => {
              setCredential(credential);
              setConfirmationResult(callback);
            }}
          />
        );
    }
  };
  const activeStep = currentView === "SIGNUP" ? 1 : currentView === "VERIFY" ? 2 : 3;
  const steps = [t("sign.up"), t("verify"), t("complete")];
  return (
    <div className="aa-signup-flow">
      <ol className="aa-stepper" aria-label={t("sign.up")}>
        {steps.map((label, index) => (
          <li
            className={`aa-step-dot ${index + 1 === activeStep ? "is-active" : index + 1 < activeStep ? "is-done" : ""}`}
            data-step={index + 1}
            aria-current={index + 1 === activeStep ? "step" : undefined}
            key={`${label}-${index}`}
          >
            <span className="aa-step-number" aria-hidden="true">
              {index + 1}
            </span>
            {label}
          </li>
        ))}
      </ol>
      {renderView()}
      <p className="aa-auth-footnote">
        {t("already.have.an.account", { defaultValue: "Already have an account?" })}{" "}
        <Link className="aa-text-link" href="/login">
          {t("login")}
        </Link>
      </p>
    </div>
  );
};

export default SignUp;
