import { cookies } from "next/headers";
import { unstable_rethrow } from "next/navigation";
import { Metadata } from "next";
import Link from "next/link";
import dynamic from "next/dynamic";
import { shopService } from "@/services/shop";
import { globalService } from "@/services/global";
import { parseSettings } from "@/utils/parse-settings";
import { ImageWithFallBack } from "@/components/image";
import { Button } from "@/components/button";
import { Translate } from "@/components/translate";
import { MasterCardLoading } from "@/components/master-card/master-card-loading";
import { SlidableProductList } from "@/components/slidable-product-list";
import type { ProductGallery } from "@/types/product";
import { TopInfo } from "../components/top-info";
import { ShopLocation } from "../components/location";
import { BranchGate } from "../components/branch-gate";
import { MainInfo } from "../components/main-info";
import { WorkingSchedule } from "../components/working-schedule";
import { BuyOptions } from "../components/buy-options";
import { SellerChat } from "../components/seller-chat";
import "./profile-display.css";

const Services = dynamic(() =>
  import("../components/services").then((component) => ({ default: component.Services }))
);

const Masters = dynamic(
  () => import("../components/masters").then((component) => ({ default: component.Masters })),
  {
    loading: () => (
      <div className="aa-s2-panel animate-pulse">
        <div className="h-6 rounded-button w-1/3 bg-gray-300" />
        <div className="grid md:grid-cols-4 sm:grid-cols-3 grid-cols-2 md:gap-4 gap-2 mt-6">
          {Array.from(Array(8).keys()).map((item) => (
            <MasterCardLoading key={item} />
          ))}
        </div>
      </div>
    ),
  }
);

const Reviews = dynamic(() => import("../components/reviews"));
const NearbyShops = dynamic(() =>
  import("../components/near-by").then((component) => ({ default: component.NearBy }))
);

// These are the same location filters carried by native shop search results
// and the branch switcher. They let ShopResource resolve matched_location
// without silently substituting the shop-level address.
const shopLocationSearchKeys = ["region_id", "country_id", "city_id", "area_id", "location_type"];

const extractShopLocationParams = (searchParams: Record<string, string | string[] | undefined>) => {
  const params: Record<string, string> = {};
  shopLocationSearchKeys.forEach((key) => {
    const value = searchParams[key];
    if (typeof value === "string") {
      params[key] = value;
    }
  });
  return params;
};

export const generateMetadata = async (props: {
  params: Promise<{ id: string }>;
  searchParams: Promise<Record<string, string | string[] | undefined>>;
}): Promise<Metadata> => {
  const params = await props.params;
  const searchParams = await props.searchParams;
  const lang = (await cookies()).get("lang")?.value || "en";
  const currencyId = (await cookies()).get("currency_id")?.value;
  let shop;
  try {
    shop = await shopService.getBySlug(params.id, {
      lang,
      currency_id: currencyId,
      ...extractShopLocationParams(searchParams),
    });
  } catch (error) {
    unstable_rethrow(error);
    throw error;
  }

  return {
    title: shop.data.translation?.title,
    description: shop.data.translation?.description,
    openGraph: {
      images: shop.data.logo_img ? { url: shop.data.logo_img } : undefined,
      title: shop.data.translation?.title,
      description: shop.data.translation?.description,
    },
  };
};

const SingleShop = async (props: {
  params: Promise<{ id: string }>;
  searchParams: Promise<Record<string, string | string[] | undefined>>;
}) => {
  const params = await props.params;
  const searchParams = await props.searchParams;
  const lang = (await cookies()).get("lang")?.value || "en";
  const currencyId = (await cookies()).get("currency_id")?.value;
  let settingsLoadFailed = false;
  const settings = await globalService.settings().catch((error) => {
    console.error("Unable to load storefront settings for shop profile", error);
    settingsLoadFailed = true;
    return undefined;
  });
  const parsedSettings = parseSettings(settings?.data);
  const productsEnabled = parsedSettings?.products_enabled === "1";
  const shopReviewsEnabled = parsedSettings?.shop_reviews_enabled === "1";
  const shopLocationParams = extractShopLocationParams(searchParams);
  let shop;
  try {
    shop = await shopService.getBySlug(params.id, {
      lang,
      currency_id: currencyId,
      ...shopLocationParams,
    });
  } catch (error) {
    unstable_rethrow(error);
    throw error;
  }

  let shopGallery: ProductGallery[] = [];
  let shopGalleryLoadFailed = false;
  try {
    const response = await shopService.gellery(params.id);
    shopGallery = response.data?.galleries ?? [];
  } catch {
    shopGalleryLoadFailed = true;
  }

  const shopLocationQuery = new URLSearchParams(shopLocationParams).toString();
  const galleryHref = `/shops/${params.id}/gallery${shopLocationQuery ? `?${shopLocationQuery}` : ""}`;
  const initialHoursTime = Date.now();

  return (
    <div className="aa-s2 aa-s2-profile-page">
      <main className="aa-s2-wrap aa-s2-profile-content">
        <nav className="aa-s2-profile-breadcrumb" aria-label="Breadcrumb">
          <Link href="/">
            <Translate value="home" />
          </Link>
          <span aria-hidden="true">/</span>
          <Link href="/shops"><Translate value="businesses" /></Link>
          <span aria-hidden="true">/</span>
          <span aria-current="page">
            {shop.data.translation?.title || "Business name unavailable"}
          </span>
        </nav>

        <TopInfo data={shop} initialHoursTime={initialHoursTime} />
        {settingsLoadFailed && (
          <p className="aa-s2-profile-settings-warning" role="alert">
            Some storefront settings could not be loaded. Product and review sections may be unavailable until settings return.
          </p>
        )}

        <nav className="aa-s2-profile-jump-links" aria-label="Shop profile sections">
          <a href="#services"><Translate value="services" /></a>
          {productsEnabled && <a href="#products"><Translate value="products" /></a>}
          <a href="#specialists"><Translate value="our.specialists" /></a>
          {shopReviewsEnabled && <a href="#reviews"><Translate value="reviews" /></a>}
          <a href="#location"><Translate value="location" /></a>
        </nav>

        <div className="aa-s2-profile-grid">
          <div className="aa-s2-profile-main">
            <section className="aa-s2-panel aa-s2-profile-gallery-panel" aria-labelledby="shop-gallery-heading">
              <div className="aa-s2-section-head">
                <div>
                  <span className="aa-s2-eyebrow">Shop photos</span>
                  <h2 id="shop-gallery-heading">Gallery</h2>
                </div>
                {shopGallery.length > 0 && (
                  <Link href={galleryHref} className="aa-s2-profile-text-link">
                    <Translate value="see.photos" />
                  </Link>
                )}
              </div>
              {shopGalleryLoadFailed ? (
                <div className="aa-s2-profile-state" role="alert">
                  <p>Shop photos could not be loaded right now.</p>
                  <Link href={galleryHref}>Open the full gallery</Link>
                </div>
              ) : shopGallery.length > 0 ? (
                <>
                  <div className="aa-s2-profile-gallery-grid">
                    {shopGallery.slice(0, 4).map((image) => (
                      <Link href={galleryHref} key={image.id} className="aa-s2-profile-gallery-item">
                        <ImageWithFallBack
                          src={image.preview || image.path}
                          alt={image.title || shop.data.translation?.title || "Shop photo"}
                          fill
                          sizes="(max-width: 700px) 42vw, 220px"
                          className="aa-s2-profile-gallery-image"
                        />
                      </Link>
                    ))}
                  </div>
                  <Link href={galleryHref} className="aa-s2-profile-text-link aa-s2-profile-gallery-link">
                    <Translate value="see.all.photos" />
                  </Link>
                </>
              ) : (
                <p className="aa-s2-profile-empty">No shop gallery photos are available.</p>
              )}
            </section>

            <div className="aa-s2-profile-domains">
              <section
                className="aa-s2-profile-domain-panel"
                id="services"
                aria-label="Services"
              >
                <div className="aa-s2-profile-domain-intro">
                  <span className="aa-s2-eyebrow"><Translate value="services" /></span>
                <p>Explore available services and book with this business.</p>
                </div>
                <BranchGate data={shop}>
                  <Services shopId={shop.data.id} shopSlug={shop.data.slug} />
                  <section
                    className="aa-s2-panel aa-s2-profile-specialists"
                    id="specialists"
                    aria-label="Specialists"
                  >
                    <div className="aa-s2-section-head">
                      <div>
                        <span className="aa-s2-eyebrow">People</span>
                        <h2><Translate value="our.specialists" /></h2>
                      </div>
                    </div>
                    <Masters shopId={shop.data.id} shopSlug={shop.data.slug} />
                  </section>
                </BranchGate>
              </section>

              {productsEnabled && (
                <section
                  className="aa-s2-panel aa-s2-profile-domain-panel aa-s2-profile-products"
                  id="products"
                  aria-label="Products"
                >
                  <div className="aa-s2-profile-domain-intro">
                    <span className="aa-s2-eyebrow"><Translate value="products" /></span>
                    <p>Shop products from {shop.data.translation?.title || "this business"}.</p>
                  </div>
                  <SlidableProductList
                    title="products"
                    link="/products"
                    visibleListCount={3}
                    params={{ shop_id: shop.data.id }}
                    layout="grid"
                  />
                </section>
              )}
            </div>

            {shopReviewsEnabled && (
              <section id="reviews" aria-label="Reviews">
                <Reviews
                  reviewCount={shop.data.r_count}
                  reviewAvg={shop.data.r_avg}
                  id={shop.data.id}
                />
              </section>
            )}
          </div>

          <aside className="aa-s2-profile-sidebar">
            <div className="aa-s2-profile-sidebar-sticky">
              <section id="location" aria-label="Location">
                <ShopLocation data={shop} />
              </section>
              <MainInfo data={shop} showReviewAction={false} initialHoursTime={initialHoursTime} />
              <BuyOptions shopSlug={shop.data.slug} shopId={shop.data.id} />
              <WorkingSchedule data={shop.data.shop_working_days} />
              {!shop.data.phone && (
                <div className="aa-s2-panel aa-s2-profile-contact">
                  <span className="aa-s2-eyebrow"><Translate value="contact" /></span>
                  <p className="aa-s2-profile-empty">No phone number is available for this business.</p>
                </div>
              )}
            </div>
          </aside>
        </div>

        <div className="aa-s2-profile-nearby">
          <NearbyShops shop={shop.data} />
        </div>
      </main>

      {shop.data.user_id && (
        <div className="aa-s2-profile-chat">
          <SellerChat receiverId={shop.data.user_id} />
        </div>
      )}

      <div className="aa-s2-profile-mobile-booking">
        <Button
          color="black"
          fullWidth
          as={Link}
          href={`/shops/${params.id}/booking${shopLocationQuery ? `?${shopLocationQuery}` : ""}`}
        >
          <Translate value="book.now" />
        </Button>
      </div>
    </div>
  );
};

export default SingleShop;