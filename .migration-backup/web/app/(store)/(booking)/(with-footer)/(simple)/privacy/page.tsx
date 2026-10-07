import { cookies } from "next/headers";
import { infoService } from "@/services/info";
import { PrivacyContent } from "./content";

export const dynamic = "force-dynamic";

const PrivacyPolicy = async () => {
  const lang = (await cookies()).get("lang")?.value || "en";
  const terms = await infoService.privacy({ lang }).catch(() => undefined);
  return <PrivacyContent data={terms} initialLocale={lang} />;
};

export default PrivacyPolicy;
