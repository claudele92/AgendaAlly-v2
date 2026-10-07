const prefix = 'agendaally:manual-finance:intent:';

export function readManualFinanceIntent(workflow) {
  try {
    const raw = sessionStorage.getItem(`${prefix}${workflow}`);
    return raw ? JSON.parse(raw) : null;
  } catch {
    return null;
  }
}

export function saveManualFinanceIntent(workflow, payload) {
  const existing = readManualFinanceIntent(workflow);
  if (existing) return existing;
  const commandKey = globalThis.crypto.randomUUID();
  const intent = { command_key: commandKey, payload: { ...payload, command_key: commandKey } };
  sessionStorage.setItem(`${prefix}${workflow}`, JSON.stringify(intent));
  return intent;
}

export function clearManualFinanceIntent(workflow) {
  sessionStorage.removeItem(`${prefix}${workflow}`);
}
