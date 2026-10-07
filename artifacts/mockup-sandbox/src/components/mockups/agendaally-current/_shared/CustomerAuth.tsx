import React from "react";
import { Button, Link } from "./AuthPrimitives";
import { Translate, useTranslation } from "./translation";

export type AuthMode = "login" | "sign-up";

export function AuthHeader({
  mode,
  onModeChange,
  onBusiness,
}: {
  mode: AuthMode;
  onModeChange: (mode: AuthMode) => void;
  onBusiness?: () => void;
}) {
  const { t } = useTranslation();
  return (
    <header className="flex items-center justify-between px-4 pb-5 pt-6">
      <Link className="text-xl font-semibold" href="/" onClick={(event) => event.preventDefault()}>
        AgendaAlly
      </Link>
      <div className="flex items-center gap-5">
        <Button
          as={Link}
          href="/for-business"
          size="small"
          color="blackOutlined"
          onClick={(event: React.MouseEvent<HTMLAnchorElement>) => {
            event.preventDefault();
            onBusiness?.();
          }}
        >
          {t("for.business")}
        </Button>
        <Button
          as={Link}
          href={mode === "login" ? "/sign-up" : "/login"}
          size="small"
          color="blackOutlined"
          onClick={(event: React.MouseEvent<HTMLAnchorElement>) => {
            event.preventDefault();
            onModeChange(mode === "login" ? "sign-up" : "login");
          }}
        >
          {mode === "login" ? t("sign.up") : t("login")}
        </Button>
      </div>
    </header>
  );
}

export function CustomerAuthLayout({
  mode,
  onModeChange,
  onBusiness,
  children,
}: {
  mode: AuthMode;
  onModeChange: (mode: AuthMode) => void;
  onBusiness?: () => void;
  children: React.ReactNode;
}) {
  return (
    <div className="agendaally-current min-h-screen bg-white">
      <AuthHeader mode={mode} onModeChange={onModeChange} onBusiness={onBusiness} />
      <main className="auth-body flex items-center gap-10 px-4 xl:container">
        <div className="auth-body hidden h-full flex-1 flex-col justify-end bg-auth-pattern bg-cover bg-no-repeat px-10 pb-12 text-white lg:flex">
          <h1 className="mb-2 text-6xl font-semibold">
            <Translate value="online.booking" />
          </h1>
          <p className="text-lg">
            <Translate value="project.description" />
          </p>
        </div>
        <div className="w-full sm:min-w-[450px] lg:max-w-[450px]">{children}</div>
      </main>
    </div>
  );
}