import { cookies } from "next/headers";
import { notFound } from "next/navigation";
import { shopService } from "@/services/shop";
import { BookingTotal } from "../../components/booking-total";
import { StaffSelect } from "./components/staff-select";
import { BookingBackButton } from "../booking-back-button";

const ShopBookingStaff = async (props: { params: Promise<{ id: string }> }) => {
  const params = await props.params;
  const lang = (await cookies()).get("lang")?.value || "en";
  const currencyId = (await cookies()).get("currency_id")?.value;
  // This shop is the whole page's subject - a failed fetch reads as "not
  // available" (404) rather than crashing.
  let shop;
  try {
    shop = await shopService.getBySlug(params.id, { lang, currency_id: currencyId });
  } catch {
    notFound();
  }
  return (
    <section className="xl:container px-4 py-7">
      <BookingBackButton deleteStaff />
      <div className="grid md:grid-cols-3 grid-cols-1 gap-7 lg:mt-6">
        <div className="col-span-2">
          <h2 className="text-xl font-semibold">
            Select a specialist
          </h2>
          <StaffSelect shopId={shop?.data.id} />
        </div>
        <div>
          <div className="sticky top-6 flex flex-col gap-7">
            <BookingTotal data={shop} nextPage="/booking/staff" />
          </div>
        </div>
      </div>
    </section>
  );
};

export default ShopBookingStaff;
