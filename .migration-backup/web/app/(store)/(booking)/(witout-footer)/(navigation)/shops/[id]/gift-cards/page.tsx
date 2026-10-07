import React from "react";
import { BackButton } from "@/components/back-button";
import { cookies } from "next/headers";
import { notFound } from "next/navigation";
import { shopService } from "@/services/shop";
import { GiftCardList } from "./list";
import { GiftCardPurchase } from "./purchase";

const GiftCartList = async (props: { params: Promise<{ id: string }> }) => {
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
    <div className="xl:container px-4 md:mt-7">
      <div className="hidden lg:block">
        <BackButton />
      </div>
      <div className="grid lg:grid-cols-3 gap-7 mt-6">
        <div className="lg:col-span-2 rounded-button md:border border-gray-link md:px-5 md:py-6">
          <GiftCardList />
        </div>
        <div>
          <GiftCardPurchase data={shop?.data} />
        </div>
      </div>
    </div>
  );
};

export default GiftCartList;
