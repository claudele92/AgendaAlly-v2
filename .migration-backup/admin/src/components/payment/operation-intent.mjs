export function operationRequestKey(registry, { allocationId, kind, amount, contextId }, createKey = () => crypto.randomUUID()) {
  const signature = `${allocationId}:${kind}:${String(amount)}:${contextId || ''}`;
  if (!registry[signature]) registry[signature] = createKey();
  return registry[signature];
}