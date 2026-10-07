import { cookies } from "next/headers";
import { infoService } from "@/services/info";
import { Metadata } from "next";
import { TermsContent } from "./content";

export const generateMetadata = async (): Promise<Metadata> => {
  const lang = (await cookies()).get("lang")?.value || "en";
  const terms = await infoService.terms({ lang }).catch(() => undefined);
  return {
    title: terms?.data.translation?.title,
  };
};

const TermsPage = async () => {
  const lang = (await cookies()).get("lang")?.value || "en";
  const terms = await infoService.terms({ lang }).catch(() => undefined);
  return <TermsContent data={terms} initialLocale={lang} />;
};

export default TermsPage;
