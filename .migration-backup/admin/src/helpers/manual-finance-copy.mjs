export const manualFinanceStateLabels = {
  REQUESTED: 'Requested',
  APPROVED: 'Approved — not yet paid/refunded',
  REQUIRES_REVIEW: 'Requires review — do not pay again',
  COMPLETED: 'Completed',
  REJECTED: 'Rejected',
  CANCELLED: 'Cancelled',
};

export const vendorPayoutStateLabels = {
  REQUESTED: 'Payout requested',
  APPROVED: 'Payout approved — not yet paid',
  COMPLETED: 'Payout completed',
  REJECTED: 'Payout rejected',
  CANCELLED: 'Payout cancelled',
  REQUIRES_REVIEW: 'Payout under review',
};

export function formatManualFinanceAmount(units, currency, scale = 0) {
  const raw = String(units || '0');
  const negative = raw.startsWith('-');
  const digits = (negative ? raw.slice(1) : raw).padStart(Number(scale) + 1, '0');
  return `${negative ? '-' : ''}${Number(scale) ? digits.slice(0, -Number(scale)) : digits}${Number(scale) ? `.${digits.slice(-Number(scale))}` : ''} ${currency}`;
}
