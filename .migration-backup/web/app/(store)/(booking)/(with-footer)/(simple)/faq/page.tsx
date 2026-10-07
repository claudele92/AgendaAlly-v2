import React from "react";
import { cookies } from "next/headers";
import { infoService } from "@/services/info";
import { GeneralChat } from "./components/general-chat";
import { HelpContent } from "./content";

const Help = async () => {
  const lang = (await cookies()).get("lang")?.value || "en";
  const faqs = await infoService.faq({ lang }).catch(() => undefined);
  return (
    <>
      <HelpContent data={faqs} initialLocale={lang} />
      <GeneralChat />
    </>
  );
};

export default Help;
