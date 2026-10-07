import { cookies } from "next/headers";
import { notFound } from "next/navigation";
import { shopService } from "@/services/shop";
import { Translate } from "@/components/translate";
import { PaymentFinish } from "./components/payment-finish";
import { ProtectedPayment } from "./protected-payment";
import { BookingBackButton } from "../booking-back-button";

const ShopBookingPayment = async (props: { params: Promise<{ id: string }> }) => {
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
      <BookingBackButton deletePayment />
      <div className="grid lg:grid-cols-3 grid-cols-1 gap-7 lg:mt-6">
        <div className="lg:col-span-2">
          <div className="border border-gray-link rounded-button col-span-2 py-6 px-5">
            <h2 className="text-2xl font-semibold">
              <Translate value="payments" />
            </h2>
            <div className="text-base mt-5 mb-10">
              <Translate value="payment.description" />
            </div>
            <ProtectedPayment shopSlug={shop?.data.slug} shopId={shop?.data.id} />
          </div>
        </div>
        <div>
          <div className="sticky top-6 flex flex-col gap-7">
            <PaymentFinish shop={shop} />
          </div>
        </div>
      </div>
    </section>
  );
};

export default ShopBookingPayment;
