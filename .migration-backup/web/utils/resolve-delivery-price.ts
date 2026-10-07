import { DeliveryPrice } from "@/types/global";

// A city can have several DeliveryPrice rows side by side - one per area a
// seller configured a distinct price for, plus an area-less city-wide
// default (see DeliveryPrice.area_id on the backend) - so picking data[0]
// blindly can charge a customer the wrong shop's/area's price whenever more
// than one row comes back. Prefers an exact area match, falls back to the
// city-wide (no area_id) row, and only then to the first result, so a city
// with no area-specific pricing configured behaves exactly as before.
export const resolveDeliveryPrice = (
  prices?: DeliveryPrice[],
  areaId?: number | null
): DeliveryPrice | undefined => {
  if (!prices || prices.length === 0) {
    return undefined;
  }

  if (areaId) {
    const match = prices.find((price) => price.area_id === areaId);
    if (match) {
      return match;
    }
  }

  return prices.find((price) => !price.area_id) || prices[0];
};
