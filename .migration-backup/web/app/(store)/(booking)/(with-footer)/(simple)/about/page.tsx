import { cookies } from "next/headers";
import { infoService } from "@/services/info";
import { AboutPageContent } from "./content";

const AboutPage = async () => {
  const lang = (await cookies()).get("lang")?.value || "en";
  const data = await infoService.getPages({ type: "all_about", lang }).catch(() => undefined);

  return <AboutPageContent initialData={data} initialLocale={lang} />;
};

export default AboutPage;
