// Pure Shop-local calculation. The caller must supply the SAME timestamp
// for SSR and first hydration; the helper never reads the runtime clock.
const days = ["sunday", "monday", "tuesday", "wednesday", "thursday", "friday", "saturday"];
const minutes = value => {
  const match = /^([01]\d|2[0-3])[:-]([0-5]\d)$/.exec(value || "");
  return match ? Number(match[1]) * 60 + Number(match[2]) : null;
};
const interval = day => {
  const from = minutes(day?.from), to = minutes(day?.to);
  return from === null || to === null || from === to ? null : { from, to };
};
function shopHours(shop, timestamp) {
  const unknown = reason => ({ closed: null, today: null, reason });
  if (!shop?.shop_working_days?.length) return unknown("Hours not provided");
  if (!shop.hours_timezone) return unknown("Shop timezone unavailable");
  let parts;
  try {
    parts = Object.fromEntries(new Intl.DateTimeFormat("en-GB", {
      timeZone: shop.hours_timezone, weekday: "long", year: "numeric", month: "2-digit",
      day: "2-digit", hour: "2-digit", minute: "2-digit", hourCycle: "h23",
    }).formatToParts(new Date(timestamp)).map(p => [p.type, p.value]));
  } catch { return unknown("Shop time unavailable"); }
  const weekday = parts.weekday.toLowerCase();
  const date = `${parts.year}-${parts.month}-${parts.day}`;
  const now = Number(parts.hour) * 60 + Number(parts.minute);
  const today = shop.shop_working_days.find(d => d.day === weekday);
  const previous = shop.shop_working_days.find(d => d.day === days[(days.indexOf(weekday) + 6) % 7]);
  const result = (closed, hours = today) => ({ closed, today: hours || null, reason: null });
  if (!shop.open || shop.shop_closed_date?.some(d => d.day?.slice(0, 10) === date)) return result(true);
  // Yesterday's overnight session belongs to yesterday's published interval.
  const prior = interval(previous);
  if (!previous?.disabled && prior && prior.to < prior.from && now < prior.to) {
    return result(false, previous);
  }
  if (!today) return unknown("Hours not provided for this day");
  if (today.disabled) return result(true);
  const range = interval(today);
  if (!range) return unknown("Hours unavailable for this day");
  return result(!(range.to > range.from
    ? now >= range.from && now < range.to
    : now >= range.from));
}
module.exports = { shopHours };