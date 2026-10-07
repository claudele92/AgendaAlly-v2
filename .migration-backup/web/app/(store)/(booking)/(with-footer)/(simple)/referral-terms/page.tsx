import { cookies } from "next/headers";
import { infoService } from "@/services/info";
import { ReferralContent } from "./content";

const ReferralTerms = async () => {
  const lang = (await cookies()).get("lang")?.value || "en";
  const data = await infoService.referrals({ lang }).catch(() => undefined);
  return <ReferralContent data={data} />;
};

export default ReferralTerms;
