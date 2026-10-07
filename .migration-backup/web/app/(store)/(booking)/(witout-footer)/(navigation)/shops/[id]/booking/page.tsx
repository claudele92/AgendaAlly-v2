import { cookies } from "next/headers";
import { notFound } from "next/navigation";
import { shopService } from "@/services/shop";
import dynamic from "next/dynamic";
import { WorkingSchedule } from "../components/working-schedule";
import { BookingTotal } from "../components/booking-total";
import { BookingBackButton } from "./booking-back-button";
import { BuyOptions } from "../components/buy-options";
import { BranchGate } from "../components/branch-gate";
import { extractShopLocationParams } from "@/utils/extract-shop-location-params";

const Services = dynamic(
  () => import("../components/services").then((component) => ({ default: component.Services })),
  {
    loading: () => (
      <div className="border border-gray-link rounded-button py-6 px-5 animate-pulse col-span-2">
        <div className="h-6 rounded-button w-1/3 bg-gray-300" />
        <div className="mt-6">
          <div className="w-1/2 mb-2 rounded-full h-[18px] bg-gray-300" />
          <div className="w-full mb-1 rounded-full h-[14px] bg-gray-300" />
          <div className="w-1/4 rounded-full h-[14px] bg-gray-300" />
        </div>
        <div className="mt-6">
          <div className="w-1/2 mb-2 rounded-full h-[18px] bg-gray-300" />
          <div className="w-full mb-1 rounded-full h-[14px] bg-gray-300" />
          <div className="w-1/4 rounded-full h-[14px] bg-gray-300" />
        </div>
        <div className="mt-6">
          <div className="w-1/2 mb-2 rounded-full h-[18px] bg-gray-300" />
          <div className="w-full mb-1 rounded-full h-[14px] bg-gray-300" />
          <div className="w-1/4 rounded-full h-[14px] bg-gray-300" />
        </div>
      </div>
    ),
  }
);

const ShopBooking = async (
  props: {
    params: Promise<{ id: string }>;
    searchParams?: Promise<Record<string, string | string[] | undefined>>;
  }
) => {
  const searchParams = (await props.searchParams) ?? {};
  const params = await props.params;
  const lang = (await cookies()).get("lang")?.value || "en";
  const currencyId = (await cookies()).get("currency_id")?.value;
  const masterId =
    typeof searchParams.master_id === "string" ? searchParams.master_id : undefined;
  // This shop is the whole page's subject - a failed fetch reads as "not
  // available" (404) rather than crashing.
  let shop;
  try {
    shop = await shopService.getBySlug(params.id, {
      lang,
      currency_id: currencyId,
      ...extractShopLocationParams(searchParams),
    });
  } catch {
    notFound();
  }
  return (
    <section className="xl:container px-4 py-7">
      <BookingBackButton resetAll />
      <div className="grid lg:grid-cols-3 grid-cols-1 lg:gap-x-7 gap-y-7 lg:mt-6">
        <div className="col-span-2">
          <BranchGate data={shop}>
            <Services isInBookingPage shopId={shop?.data?.id} masterId={masterId} />
          </BranchGate>
        </div>
        <div>
          <div className="sticky top-6 flex flex-col gap-7">
            <BookingTotal
              data={shop}
              nextPage={searchParams?.master_id ? "/booking/staff" : "/booking/staff-select"}
            />
            <BuyOptions shopSlug={shop?.data.slug} shopId={shop?.data.id} />
            <WorkingSchedule data={shop?.data?.shop_working_days} />
          </div>
        </div>
      </div>
    </section>
  );
};

export default ShopBooking;
