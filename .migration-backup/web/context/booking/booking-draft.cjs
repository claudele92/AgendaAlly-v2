// Non-authoritative UI selections only. Availability and money still come from the server.
const keyFor = (shop, actor) => `booking-draft:${shop}:${actor || "guest"}`;
function readDraft(storage, key) {
  try {
    const state = JSON.parse(storage.getItem(key) || "null");
    if (!state || !Array.isArray(state.services) || !Array.isArray(state.dateAndTimes)) return null;
    if (!state.services.every(service => Number.isInteger(service.id) && service.id > 0)) return null;
    return state;
  } catch { return null; }
}
function writeDraft(storage, key, state) {
  try {
    if (!state.services.length) storage.removeItem(key);
    else storage.setItem(key, JSON.stringify(state));
  } catch { /* Storage can be unavailable; the live booking context remains usable. */ }
}
function assignmentIds(services) {
  return services.map(service => service.master?.service_master?.id)
    .filter(id => Number.isInteger(id) && id > 0);
}
module.exports = { keyFor, readDraft, writeDraft, assignmentIds };