import React, { useState } from "react";
import "./_group.css";
import CalendarCheckIcon from "./_native/calendar-check";
import ProgressIcon from "./_native/progress";
import MoneyOutlinedIcon from "./_native/money-outlined";
import BellOutlinedIcon from "./_native/bell-outlined";
import LoginIcon from "./_native/login";
import BagIcon from "./_native/bag";
import Menu2Icon from "./_native/menu-2";
import AnchorLeftIcon from "./_native/anchor-left";

const imageRoot = "/__mockup/images/agendaally-stage2-business-current";

/*
 * Local-only data boundary. The original page gets these values from the
 * translation/settings/category/brand APIs. No category, vendor, download URL,
 * location, social account, or API data is invented for this extraction.
 */
const publicSettings = {
  title: "AgendaAlly",
  logo: `${imageRoot}/logo.png`,
  dark_logo: undefined as string | undefined,
  description: "",
  footer_text: "",
  customer_app_ios: "",
  customer_app_android: "",
  vendor_app_ios: "",
  vendor_app_android: "",
  pos_app_ios: "",
  pos_app_android: "",
  delivery_app_ios: "",
  delivery_app_android: "",
  instagram: "",
  facebook: "",
  twitter: "",
  linkedin: "",
};

// The original i18next values are backend-managed. Only labels explicitly
// available in the Stage 2 brief are translated here; unresolved text remains
// empty rather than being replaced with invented marketing claims.
const nativeCopy: Record<string, string> = {
  "business.section.title": "AgendaAlly for Business",
  "get.started": "Get started",
  "online.booking": "Online booking",
  management: "Management",
  payment: "Payment",
  notification: "Notification",
  "stay.in.control": "Stay in control",
  "run.your.business": "Run your business your way",
  "download.the.app": "Download the app",
  "fastest.growing.companies": "Fastest-growing companies",
  "grow.your.business": "Grow your business",
  clients: "clients",
  "grow.percent": "growth",
  professinals: "professionals",
  "client.app": "Client app",
  "business.app": "Business app",
  "pos.system": "POS system",
  "driver.app": "Driver app",
  shops: "Shops",
  deals: "Deals",
  blog: "Blog",
  "my.appointments": "My appointments",
  login: "Login",
  information: "Information",
  "about.company": "About",
  "work.for.us": "Careers",
  "contact.us": "Contact",
  help: "Help",
  "faqs.short": "FAQs",
  terms: "Terms",
  "privacy.policy": "Privacy",
  socials: "Social",
};

function Translate({ value }: { value: string }) {
  return <>{nativeCopy[value] ?? ""}</>;
}

function FillImage({
  src,
  alt,
  className,
}: {
  src: string;
  alt: string;
  className?: string;
}) {
  return <img src={src} alt={alt} className={`aa-business-image-fill ${className ?? ""}`} />;
}

function Header() {
  const [menuOpen, setMenuOpen] = useState(false);

  return (
    <header>
      <div className="xl:container px-4 lg:py-7 sm:py-4 py-2.5 flex items-center justify-between">
        <div className="flex gap-7 items-center">
          <div className="relative lg:hidden z-[9]">
            <button
              type="button"
              aria-label="Open navigation menu"
              aria-expanded={menuOpen}
              onClick={() => setMenuOpen((open) => !open)}
              className="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-link bg-white"
            >
              <Menu2Icon />
            </button>
            {menuOpen && (
              <nav className="aa-business-drawer" aria-label="Mobile navigation">
                <a href="/shops"><Translate value="shops" /></a>
                <a href="/shops?column=b_count&sort=desc"><Translate value="deals" /></a>
                <a href="/blogs"><Translate value="blog" /></a>
                <a href="/appointments"><Translate value="my.appointments" /></a>
                <a href="/for-business">For business</a>
                <a href="/login"><Translate value="login" /></a>
              </nav>
            )}
          </div>
          <a href="/" className="relative z-10 lg:z-[4] lg:inline">
            <img
              src={publicSettings.logo}
              alt={publicSettings.title}
              width={148}
              height={28}
              className="object-contain h-7 w-auto"
            />
          </a>
          {/* No selected country is seeded in this API-isolated preview. */}
        </div>
        <nav className="lg:flex items-center gap-14 hidden" aria-label="Customer navigation">
          <a href="/shops" className="text-base font-medium"><Translate value="shops" /></a>
          <a href="/shops?column=b_count&sort=desc" className="text-base font-medium"><Translate value="deals" /></a>
          <a href="/blogs" className="text-base font-medium"><Translate value="blog" /></a>
          <a href="/appointments" className="text-base font-medium"><Translate value="my.appointments" /></a>
        </nav>
        <div className="relative z-[4]">
          <div className="items-center gap-5 lg:flex hidden">
            <a href="/cart" aria-label="Cart" className="rounded-button border border-footerBg py-2.5 px-3">
              <div className="relative"><BagIcon /></div>
            </a>
            <a href="/login" className="border border-footerBg rounded-button text-sm font-semibold py-2.5 px-6 inline-flex items-center gap-2">
              <LoginIcon /><Translate value="login" />
            </a>
          </div>
          <div className="lg:hidden">
            <a href="/login" aria-label="Login" className="w-10 h-10 inline-flex items-center justify-center border border-footerBg rounded-button">
              <LoginIcon />
            </a>
          </div>
        </div>
      </div>
    </header>
  );
}

function TopHeader({
  title,
  description,
  buttonText,
  link,
}: {
  title: string;
  description: string;
  buttonText: string;
  link: string;
}) {
  return (
    <section className="flex items-center justify-center flex-col text-center xl:container px-4 md:pb-24 pb-16 relative pt-12 gap-4">
      <div className="absolute md:-top-1/4 top-0 left-0 w-[425px] h-[245px] scale-150 z-[-1]">
        <FillImage src={`${imageRoot}/fb_ellipse.png`} alt="" className="object-contain" />
      </div>
      <div className="absolute -top-1/2 md:right-72 right-0 md:w-[425px] w-[200px] h-[245px] scale-150 z-[-1]">
        <FillImage src={`${imageRoot}/fb_ellipse1.png`} alt="" className="object-contain" />
      </div>
      <h1 className="md:text-[65px] text-3xl font-semibold break-words"><Translate value={title} /></h1>
      {description && <span className="md:text-xl text-sm"><Translate value={description} /></span>}
      <a href={link} className="outline-none focus:outline-none rounded-button overflow-hidden text-ellipsis whitespace-nowrap inline-flex items-center gap-2 justify-center active:translate-y-px hover:brightness-95 focus-ring disabled:cursor-not-allowed bg-primary text-white text-base font-semibold py-[18px] md:px-14 px-6 md:mt-10 mt-4">
        <Translate value={buttonText} />
      </a>
    </section>
  );
}

const features = [
  { icon: <CalendarCheckIcon size={40} />, title: "online.booking" },
  { icon: <ProgressIcon />, title: "management", description: "management.description" },
  { icon: <MoneyOutlinedIcon />, title: "payment", description: "payment.description" },
  { icon: <BellOutlinedIcon />, title: "notification", description: "notifications.description" },
];

type NativeCategory = {
  id: number;
  img?: string;
  translation?: { title?: string | null } | null;
};

// Stub shape follows categoryService's Paginate<Category> result. The request
// is type=service, perPage=11, column=input, sort=asc; no rows are fabricated.
const serviceCategories: NativeCategory[] = [];

function NativeServices() {
  return (
    <div className="mt-20 lg:mt-0 rounded-b-3xl">
      <div className="xl:container overflow-hidden">
        <div className="flex flex-nowrap overflow-x-auto px-4 xl:px-0 md:gap-[30px] gap-2">
          {serviceCategories.map((service, index) => (
            <a href={`/search?category_id=${service.id}`} key={service.id} className="shrink-0 md:max-w-[200px] w-[43%] md:w-[200px]">
              <div
                style={{
                  backgroundColor: ["#FFEDD7", "#D6FFD2", "#F1D2D2", "#D8DCFF", "#F7D8FF", "#C3F8FF", "#E8E8E8", "#FFE6B4", "#FFD2E8", "#C6F4E4", "#C1E8FF", "#C2B6A4"][index % 12],
                  backgroundImage: `url(${imageRoot}/service${(index % 12) + 1}.png)`,
                  backgroundRepeat: "no-repeat",
                  backgroundSize: "cover",
                  backgroundPosition: "center",
                }}
                className="rounded-button relative overflow-hidden flex flex-col md:max-w-[200px]"
              >
                <div className="pt-6 pl-6">
                  <span className="md:text-xl text-lg font-semibold line-clamp-1">{service.translation?.title}</span>
                </div>
                <div className="flex items-end justify-end flex-1">
                  <div className="relative bottom-0 right-0 max-h-[80%] w-full min-h-[100px]">
                    {service.img && <FillImage src={service.img} alt={service.translation?.title || "service"} className="object-contain max-w-max right-0 !left-auto" />}
                  </div>
                </div>
              </div>
            </a>
          ))}
        </div>
      </div>
    </div>
  );
}

// BrandService.getAll() supplies this original marquee. The API response is
// intentionally empty in the sandbox because no authentic brand rows are part
// of the checked-in public source.
function NativeBrands({ reverse = false, slower = false }: { reverse?: boolean; slower?: boolean }) {
  const className = `flex items-center justify-center md:justify-start md:gap-7 gap-2.5 [&_img]:max-w-none ${slower ? "aa-brand-track-slower" : "aa-brand-track"}`;
  const row = (
    <ul className={className}>
      {([] as Array<{ id: number; img: string; title?: string }>).map((brand) => (
        <li key={brand.id} className="md:w-[282px] w-[168px] md:h-[120px] h-20 relative rounded-button bg-gray-bg flex items-center justify-center">
          <img src={brand.img} alt={brand.title || "brand"} className="w-3/5 h-3/5 object-contain" />
        </li>
      ))}
    </ul>
  );
  return (
    <div className="w-full inline-flex flex-nowrap overflow-hidden md:gap-7 gap-2.5" aria-label={reverse ? "Business partners" : "Business brands"}>
      {row}
      <ul className={className} aria-hidden="true" />
    </div>
  );
}

function AppList() {
  const list = [
    { link: publicSettings.customer_app_ios, title: "client.app" },
    { link: publicSettings.vendor_app_ios, title: "business.app" },
    { link: publicSettings.pos_app_ios, title: "pos.system" },
    { link: publicSettings.delivery_app_ios, title: "driver.app" },
  ];

  return (
    <div className="flex flex-col md:gap-7 gap-3">
      {list.map((item) => (
        <a href={item.link || ""} key={item.title} className="md:grid flex justify-between grid-cols-2 items-center gap-28 bg-gradient-to-r from-white to-transparent rounded-button md:py-7 py-4 md:px-12 px-5">
          <span className="md:text-3xl text-xl font-medium"><Translate value={item.title} /></span>
          <AnchorLeftIcon style={{ rotate: "180deg" }} />
        </a>
      ))}
    </div>
  );
}

function Footer() {
  const logo = publicSettings.dark_logo || publicSettings.logo;
  const columns = [
    {
      title: "information",
      links: [
        ["about.company", "/about"],
        ["work.for.us", "/careers"],
        ["contact.us", "/contact"],
      ],
    },
    {
      title: "help",
      links: [
        ["faqs.short", "/faq"],
        ["terms", "/terms"],
        ["privacy.policy", "/privacy"],
      ],
    },
    { title: "socials", links: [] as Array<[string, string]> },
  ];

  return (
    <footer className="bg-footerBg pt-12 pb-5">
      <div className="xl:container px-4 text-white flex justify-between flex-wrap xl:flex-nowrap">
        <div className="flex md:flex-col md:justify-between md:w-auto w-full justify-center mb-10 md:mb-0 gap-8">
          <div>
            <div className="relative h-[45px] max-w-[420px]">
              <a href="/" className="mb-3 max-w-max">
                <img src={logo} alt={publicSettings.title} className="object-contain max-h-11 h-full w-auto" />
              </a>
            </div>
            {publicSettings.description && <p className="text-base font-medium max-w-sm">{publicSettings.description}</p>}
          </div>
          <div className="md:flex items-center gap-2.5 hidden mb-8 md:mb-0">
            {publicSettings.customer_app_ios && <a href={publicSettings.customer_app_ios} target="_blank" rel="noreferrer"><img src={`${imageRoot}/apple_store.png`} alt="App Store" width={147} height={55} /></a>}
            {publicSettings.customer_app_android && <a href={publicSettings.customer_app_android} target="_blank" rel="noreferrer"><img src={`${imageRoot}/play_market.png`} alt="Google Play" width={147} height={55} /></a>}
          </div>
        </div>
        <div className="md:gap-28 gap-3 justify-between lg:justify-start w-full lg:max-w-max lg:flex grid sm:grid-cols-2 grid-cols-1 flex-wrap xl:flex-nowrap">
          {columns.map((column) => (
            <details key={column.title} className="aa-footer-column" open>
              <summary className="flex items-center justify-between font-medium text-head md:mb-2.5 mb-1 w-full py-3 sm:py-0 sm:border-none border-white border-opacity-20">
                <Translate value={column.title} />
                <svg className="h-5 w-5 sm:hidden" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m6 9 6 6 6-6" stroke="currentColor" strokeWidth="1.6" /></svg>
              </summary>
              <div className="flex flex-col md:gap-6 gap-2">
                {column.links.map(([label, href]) => (
                  <a key={label} href={href} className="text-lg font-medium transition-all hover:underline"><Translate value={label} /></a>
                ))}
              </div>
            </details>
          ))}
        </div>
        <div className="grid grid-cols-2 items-center gap-6 md:hidden mt-10 w-full">
          {publicSettings.customer_app_ios && <div className="md:h-16 h-12 relative flex items-start"><a href={publicSettings.customer_app_ios}><img src={`${imageRoot}/apple_store.png`} alt="App Store" className="object-contain object-left" /></a></div>}
          {publicSettings.customer_app_android && <div className="md:h-16 h-12 relative"><a href={publicSettings.customer_app_android}><img src={`${imageRoot}/play_market.png`} alt="Google Play" className="object-contain object-left" /></a></div>}
        </div>
      </div>
      <div className="xl:container px-4 md:mt-12 mt-6">
        <div className="border-t border-white border-opacity-20 pt-3">
          <p className="text-white text-sm">{publicSettings.footer_text}</p>
        </div>
      </div>
    </footer>
  );
}

/**
 * Extracted from the native public /for-business route. Its section JSX and
 * utility classes follow that source; external Next navigation, dynamic image
 * loading, auth, and public APIs are kept as local/static preview boundaries.
 */
export function CurrentBusiness() {
  return (
    <div className="agendaally-business min-h-screen" onClickCapture={(event) => {
      if (event.target instanceof Element && event.target.closest("a")) event.preventDefault();
    }}>
      <Header />
      <main>
        <TopHeader
          title="business.section.title"
          description="business.section.description"
          buttonText="get.started"
          link="/login"
        />
        <section className="bg-gray-bg lg:py-12 md:py-8 py-5">
          <div className="xl:container px-4 grid lg:grid-cols-4 md:grid-cols-2 grid-cols-1 md:gap-7 gap-2.5">
            {features.map((feature) => (
              <div key={feature.title} className="p-10 rounded-button bg-white flex flex-col gap-5 items-center text-center group hover:bg-primary hover:shadow-fixedBooking hover:text-white transition-all">
                <div className="w-20 h-20 aspect-square flex items-center justify-center rounded-full bg-primary text-white group-hover:bg-white group-hover:bg-opacity-30">
                  {feature.icon}
                </div>
                <h2 className="text-head font-semibold"><Translate value={feature.title} /></h2>
                {feature.description && (
                  <span className="text-sm"><Translate value={feature.description} /></span>
                )}
              </div>
            ))}
          </div>
        </section>
        <section className="md:pt-24 pt-14">
          <h3 className="md:text-5xl text-3xl font-semibold text-center"><Translate value="stay.in.control" /></h3>
          <p className="md:text-xl text-sm text-center md:mb-44 mb-24"><Translate value="intuitive.software" /></p>
          <div className="bg-gray-bg">
            <div className="xl:container px-4 flex justify-center">
              <div className="relative w-full -top-[140px] md:h-[700px] h-[205px]">
                <FillImage
                  src={`${imageRoot}/manager_dashboard.png`}
                  alt="Reservation management dashboard screenshot"
                  className="object-contain md:w-4/5 w-full md:!h-full !h-[345px] aspect-[1136/697]"
                />
              </div>
            </div>
          </div>
        </section>
        <section className="md:py-24 py-14">
          <h3 className="md:text-5xl text-3xl font-semibold text-center"><Translate value="run.your.business" /></h3>
          <p className="md:text-xl text-sm text-center md:mb-10 mb-0"><Translate value="choose.category" /></p>
          <NativeServices />
        </section>
        <section className="md:py-24 py-14 bg-gray-bg">
          <h3 className="md:text-5xl text-3xl font-semibold text-center"><Translate value="download.the.app" /></h3>
          <p className="md:text-xl text-sm text-center mb-10"><Translate value="application.description" /></p>
          <div className="xl:container px-4 grid md:grid-cols-2">
            <AppList />
            <div className="relative aspect-square md:-left-40 left-0 md:scale-125">
              <FillImage src={`${imageRoot}/mobile_app.png`} alt="Mobile marketplace app screens" className="object-contain" />
            </div>
          </div>
        </section>
        <section className="md:py-24 py-14">
          <h3 className="md:text-5xl text-3xl font-semibold text-center mb-10 break-words"><Translate value="fastest.growing.companies" /></h3>
          <div className="flex flex-col md:gap-7 gap-3">
            <NativeBrands />
            <NativeBrands reverse slower />
          </div>
        </section>
        <section className="md:py-24 py-14 bg-gray-bg md:mb-24 mb-14">
          <h3 className="md:text-5xl text-3xl font-semibold text-center"><Translate value="grow.your.business" /></h3>
          <p className="md:text-xl text-sm text-center md:mb-20 mb-10"><Translate value="grow.your.business.desc" /></p>
          <div className="xl:container px-4 grid md:grid-cols-3 gap-y-16">
            <div className="text-center">
              <h4 className="text-6xl font-semibold">121m+</h4>
              <p className="text-lg"><Translate value="clients" /></p>
            </div>
            <div className="text-center">
              <h4 className="text-6xl font-semibold">12%</h4>
              <p className="text-lg"><Translate value="grow.percent" /></p>
            </div>
            <div className="text-center">
              <h4 className="text-6xl font-semibold">221k+</h4>
              <p className="text-lg"><Translate value="professinals" /></p>
            </div>
          </div>
        </section>
      </main>
      <Footer />
    </div>
  );
}

export default CurrentBusiness;