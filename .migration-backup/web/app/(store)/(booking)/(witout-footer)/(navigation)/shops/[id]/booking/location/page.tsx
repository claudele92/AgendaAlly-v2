import { cookies } from "next/headers";
import { notFound } from "next/navigation";
import { shopService } from "@/services/shop";
import { Translate } from "@/components/translate";
import { ExtraAddressForm } from "@/app/(store)/(booking)/(witout-footer)/(navigation)/shops/[id]/booking/location/axtra-address-form";
import { BookingTotal } from "../../components/booking-total";
import { LocationSelect } from "./location-select";
import { BookingBackButton } from "../booking-back-button";

const ShopBookingLocation = async (props: { params: Promise<{ id: string }> }) => {
  const params = await props.params;
  const lang = (await cookies()).get("lang")?.value || "en";
  // This shop is the whole page's subject - a failed fetch reads as "not
  // available" (404) rather than crashing.
  let shop;
  try {
    shop = await shopService.getBySlug(params.id, { lang });
  } catch {
    notFound();
  }
  return (
    <section className="xl:container px-2 py-7">
      <BookingBackButton deleteAddress />
      <div className="grid lg:grid-cols-3 grid-cols-1 gap-7 lg:mt-6">
        <div className="col-span-2">
          <div className="border border-gray-link rounded-button col-span-2 py-6 px-5">
            <h2 className="text-2xl font-semibold">
              <Translate value="location" />
            </h2>
            <ExtraAddressForm shopSlug={params.id} />
            <LocationSelect shopSlug={shop?.data.slug} />
          </div>
        </div>
        <div>
          <div className="sticky top-6 flex flex-col gap-7">
            <BookingTotal hasExtraAddressForm data={shop} checkLocation nextPage="/booking/note" />
          </div>
        </div>
      </div>
    </section>
  );
};

export default ShopBookingLocation;
