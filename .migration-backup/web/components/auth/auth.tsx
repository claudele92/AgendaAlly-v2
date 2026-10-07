"use client";

import React, { useCallback, useState } from "react";
import dynamic from "next/dynamic";
import { LoadingCard } from "@/components/loading";
import { AuthViews } from "./types";
import { useTranslation } from "react-i18next";

const SignUp = dynamic(() => import("./sign-up"), {
  loading: () => <LoadingCard />,
});
const Login = dynamic(() => import("./login"), {
  loading: () => <LoadingCard />,
});
const ForgotPassword = dynamic(() => import("./forgot-password"), {
  loading: () => <LoadingCard />,
});

interface AuthProps {
  defaultView?: AuthViews;
  redirectOnSuccess?: boolean;
}

export default ({ defaultView = "SIGNUP", redirectOnSuccess = true }: AuthProps) => {
  const [currentView, setCurrentView] = useState<AuthViews>(defaultView);
  const { t } = useTranslation();
  const handleChangeView = useCallback((view: AuthViews) => setCurrentView(view), []);
  const renderView = () => {
    switch (currentView) {
      case "SIGNUP":
        return <SignUp />;
      case "LOGIN":
        return <Login onViewChange={handleChangeView} redirectOnSuccess={redirectOnSuccess} />;
      case "FORGOT_PASSWORD":
        return (
          <>
            <ForgotPassword />
            <p className="aa-auth-footnote">
              <button
                type="button"
                className="aa-text-link"
                onClick={() => handleChangeView("LOGIN")}
              >
                {t("back.to.sign.in", { defaultValue: "Back to sign in" })}
              </button>
            </p>
          </>
        );

      default:
        return <SignUp />;
    }
  };
  return renderView();
};
