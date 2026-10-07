import fetcher from "@/lib/fetcher";

export type FinanceKind = "refund" | "payout";
export type FinanceSource = {
  allocation_id: string;
  context_id?: string;
  kind: FinanceKind;
  source_type: string;
  source_id: string;
  shop_id: string | number;
  beneficiary_id: string | number;
  amount_units: string;
  currency_code: string;
  money_scale: number;
};
export type FinanceRow = {
  id: string;
  kind: FinanceKind;
  state: string;
  version: number;
  actions?: string[];
  amount_units: string;
  currency_code: string;
  money_scale: number;
  requested_at: string;
  completed_at?: string | null;
  external_reference?: string | null;
  method?: string | null;
  destination_mask?: string | null;
};
export type FinanceRequest = {
  command_key: string;
  kind: FinanceKind;
  allocation_id: string;
  context_id?: string;
  amount_units: string;
  currency_code: string;
  method: "bank_transfer" | "mobile_money";
  institution: string;
  destination_mask: string;
};
export type FinanceCapabilities = {
  finance: boolean;
  can_request_refund: boolean;
  can_request_payout: boolean;
  specialist_payout_enabled: false;
};

export function formatFinanceAmount(units: string, currency: string, scale: number) {
  const raw = String(units || "0");
  const negative = raw.startsWith("-");
  const digits = (negative ? raw.slice(1) : raw).padStart(scale + 1, "0");
  const whole = scale ? digits.slice(0, -scale) : digits;
  const fraction = scale ? `.${digits.slice(-scale)}` : "";
  return `${negative ? "-" : ""}${whole}${fraction} ${currency}`;
}

const root = "v1/dashboard/manual-finance";
const manualFinanceService = {
  capabilities: () => fetcher<{ data: FinanceCapabilities }>(`${root}/capabilities`),
  sources: (kind: FinanceKind) =>
    fetcher<{ data: FinanceSource[] }>(`${root}/sources?kind=${kind}`),
  requests: (kind: FinanceKind) =>
    fetcher<{ data: FinanceRow[] }>(`${root}?kind=${kind}`),
  create: (body: FinanceRequest) =>
    fetcher.post<{ data: FinanceRow }>(root, { body }),
  action: (id: string, action: string, body: Record<string, unknown>) =>
    fetcher.post<{ data: FinanceRow }>(`${root}/${id}/${action}`, { body }),
};

export default manualFinanceService;
