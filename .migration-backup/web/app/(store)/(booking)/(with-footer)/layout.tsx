import { globalService } from "@/services/global";
import { parseSettings } from "@/utils/parse-settings";
import React from "react";
import { Footer } from "@/components/footer";

const WithFooterPagesLayout = async ({ children }: { children: React.ReactNode }) => {
  // This layout wraps almost every browsable page (home, products, blogs,
  // about, faq, terms, ...), so an unguarded throw here took down the
  // entire site on any backend hiccup - matches the fail-open pattern
  // already used for the same call in app/layout.tsx.
  const settings = await globalService.settings().catch((e) => console.log("settings error", e));
  const parsedSettings = parseSettings(settings?.data);
  return (
    <>
      {children}
      <Footer settings={parsedSettings} />
    </>
  );
};

export default WithFooterPagesLayout;
