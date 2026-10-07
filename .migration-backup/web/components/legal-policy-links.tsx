import Link from "next/link";
import { Translate } from "@/components/translate";

type LegalPolicy = "terms" | "privacy" | "refund";

/** Informational links only; does not change acceptance or payment behavior. */
export const LegalPolicyLinks = ({ current }: { current?: LegalPolicy }) => (
  <nav aria-label="Legal policies" className="flex flex-wrap items-center gap-x-4 gap-y-1">
    {current !== "terms" && (
      <Link href="/terms" className="inline-flex min-h-11 items-center text-sm text-primary underline underline-offset-4">
        <Translate value="terms" />
      </Link>
    )}
    {current !== "privacy" && (
      <Link href="/privacy" className="inline-flex min-h-11 items-center text-sm text-primary underline underline-offset-4">
        <Translate value="privacy.policy" />
      </Link>
    )}
    {current !== "refund" && (
      <Link href="/refund-cancellation" className="inline-flex min-h-11 items-center text-sm text-primary underline underline-offset-4">
        Refund &amp; Cancellation Policy
      </Link>
    )}
  </nav>
);
