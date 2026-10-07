export function createClientSaveIntentId(cryptoSource = globalThis.crypto) {
  if (cryptoSource?.randomUUID) return cryptoSource.randomUUID();
  throw new Error('Secure random UUID support is required to save a local client.');
}

export function clientSaveIntentStorage() {
  try {
    return typeof window === 'undefined' ? null : window.localStorage;
  } catch {
    return null;
  }
}

export function clientSaveIntentStorageKey(actorId, shopId) {
  if (actorId === null || actorId === undefined || shopId === null || shopId === undefined) {
    return null;
  }
  return `agendaally:client-save-intent:${String(actorId)}:${String(shopId)}`;
}

export function readClientSaveIntent(storage, key) {
  if (!storage || !key) return null;
  try {
    const value = storage.getItem(key);
    return typeof value === 'string' && /^[0-9a-f-]{36}$/i.test(value) ? value : null;
  } catch {
    return null;
  }
}

export function writeClientSaveIntent(storage, key, intentId) {
  if (!storage || !key || !intentId) return false;
  try {
    storage.setItem(key, intentId);
    return true;
  } catch {
    return false;
  }
}

export function clearClientSaveIntent(storage, key, expectedIntentId) {
  if (!storage || !key) return;
  try {
    if (!expectedIntentId || storage.getItem(key) === expectedIntentId) storage.removeItem(key);
  } catch {
    // Persistence is best-effort; the in-memory intent remains authoritative this session.
  }
}

export function isDefinitiveClientSaveRejection(error) {
  const status = Number(error?.response?.status);
  return status === 422 || status === 409;
}

export function clientSavePayload(values, branchId) {
  return {
    name: String(values?.name || '').trim(),
    phone: String(values?.phone || '').trim() || null,
    email: String(values?.email || '').trim() || null,
    shop_location_id: values?.shop_location_id || branchId || undefined,
  };
}