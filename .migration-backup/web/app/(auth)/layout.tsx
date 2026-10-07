import fetcher from "@/lib/fetcher";
import { DefaultResponse, Setting } from "@/types/global";
import { parseSettings } from "@/utils/parse-settings";
import React from "react";
import { Translate } from "@/components/translate";
import AuthHeader from "./header";
import "./stage1.css";

export default async ({ children }: { children: React.ReactNode }) => {
  const settings = await fetcher<DefaultResponse<Setting[]>>("v1/rest/settings", {
    next: { revalidate: Number(process.env.NEXT_PUBLIC_CACHE_TIME) },
  }).catch((e) => console.log("settings error", e));
  const parsedSettings = parseSettings(settings?.data);
  return (
    <div className="aa-stage1 aa-auth-page" id="top">
      <AuthHeader settings={parsedSettings} />
      <main className="aa-auth-main">
        <aside
          className="aa-auth-art"
          aria-label="AgendaAlly customer welcome"
          style={{ backgroundImage: "url('/img/auth/customer-welcome.jpg')" }}
        >
          <div className="aa-art-top">
            <span aria-hidden="true">✦</span>
            <Translate value="online.booking" />
          </div>
          <div className="aa-art-copy">
            <h1>
              <Translate value="online.booking" />
            </h1>
            <p>
              <Translate value="project.description" />
            </p>
          </div>
          <div className="aa-art-foot">
            <Translate value="project.description" />
          </div>
        </aside>
        <section className="aa-auth-panel" aria-label="Customer account">
          <div className="aa-auth-form">
            <div className="aa-mobile-note">
              <span aria-hidden="true">✦</span>
              <Translate value="project.description" />
            </div>
            {children}
          </div>
        </section>
      </main>
    </div>
  );
};
