import type { FinanceRequest } from "@/services/manual-finance";

export type DurableFinanceIntent = { command_key: string; payload: FinanceRequest };
export type DurableCommandIntent<T = Record<string, unknown>> = { command_key: string; payload: T & { command_key: string } };

export const financeIntentStorageKey = (workflow: string) =>
  `agendaally:manual-finance:intent:${workflow}`;

export function readFinanceIntent(workflow: string): DurableFinanceIntent | null {
  if (typeof window === "undefined") return null;
  try {
    const value = sessionStorage.getItem(financeIntentStorageKey(workflow));
    return value ? (JSON.parse(value) as DurableFinanceIntent) : null;
  } catch {
    return null;
  }
}

export function persistFinanceIntent(workflow: string, payload: Omit<FinanceRequest, "command_key">) {
  const previous = readFinanceIntent(workflow);
  if (previous) return previous;
  const commandKey = crypto.randomUUID();
  const intent: DurableFinanceIntent = {
    command_key: commandKey,
    payload: { ...payload, command_key: commandKey },
  };
  // Keep the key and complete submitted payload intact after ambiguous failures.
  sessionStorage.setItem(financeIntentStorageKey(workflow), JSON.stringify(intent));
  return intent;
}

export function clearFinanceIntent(workflow: string) {
  if (typeof window !== "undefined") sessionStorage.removeItem(financeIntentStorageKey(workflow));
}

export function readCommandIntent<T>(workflow: string): DurableCommandIntent<T> | null {
  if (typeof window === "undefined") return null;
  try {
    const value = sessionStorage.getItem(financeIntentStorageKey(workflow));
    return value ? (JSON.parse(value) as DurableCommandIntent<T>) : null;
  } catch {
    return null;
  }
}

export function persistCommandIntent<T extends Record<string, unknown>>(workflow: string, payload: T) {
  const existing = readCommandIntent<T>(workflow);
  if (existing) return existing;
  const commandKey = crypto.randomUUID();
  const intent = { command_key: commandKey, payload: { ...payload, command_key: commandKey } };
  sessionStorage.setItem(financeIntentStorageKey(workflow), JSON.stringify(intent));
  return intent;
}

export function clearCommandIntent(workflow: string) {
  if (typeof window !== "undefined") sessionStorage.removeItem(financeIntentStorageKey(workflow));
}
