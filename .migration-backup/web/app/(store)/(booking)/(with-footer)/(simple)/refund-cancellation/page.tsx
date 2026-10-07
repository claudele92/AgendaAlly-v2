import { cookies } from "next/headers";
import { infoService } from "@/services/info";
import { RefundPolicyContent } from "./content";
import type { Metadata } from "next";

export const dynamic = "force-dynamic";
export const metadata: Metadata = { title: "Refund & Cancellation Policy" };

export default async function RefundCancellationPage() {
  const lang = (await cookies()).get("lang")?.value || "en";
  const policy = await infoService.refundPolicy({ lang }).catch(() => undefined);
  return <RefundPolicyContent data={policy} initialLocale={lang} />;
}
