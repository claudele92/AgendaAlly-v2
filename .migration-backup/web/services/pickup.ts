import fetcher from "@/lib/fetcher";
import { buildUrlQueryParams } from "@/utils/build-url-query-params";
import { PickupAvailability } from "@/types/pickup";
import { DefaultResponse } from "@/types/global";

export const pickupService = {
  availability: (shopId: number, locationId?: number, date?: string) =>
    fetcher<DefaultResponse<PickupAvailability>>(
      buildUrlQueryParams(`v1/rest/shops/${shopId}/pickup-availability`, {
        shop_location_id: locationId,
        date,
      }),
      { cache: "no-store" }
    ),
};