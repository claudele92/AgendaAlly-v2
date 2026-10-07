import { notFound } from "next/navigation";
import { globalService } from "@/services/global";
import { parseSettings } from "@/utils/parse-settings";
import BrandsList from "./components/brand-list/brand-list";

const Brands = async () => {
  const settings = await globalService.settings().catch((e) => console.log("settings error", e));
  const parsedSettings = parseSettings(settings?.data);
  const productsEnabled = parsedSettings?.products_enabled === "1";

  if (!productsEnabled) {
    return notFound();
  }

  return <BrandsList />;
};

export default Brands;
