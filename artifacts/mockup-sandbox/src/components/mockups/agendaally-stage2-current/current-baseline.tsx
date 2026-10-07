import React, { type MouseEvent } from "react";

const assetRoot = "/__mockup/img/agendaally-stage2-current";
const stayInSandbox = (event: MouseEvent<HTMLAnchorElement>) => event.preventDefault();

const SearchIcon = () => (
  <svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
    <path
      d="M9.58317 17.4998C13.9554 17.4998 17.4998 13.9554 17.4998 9.58317C17.4998 5.21092 13.9554 1.6665 9.58317 1.6665C5.21092 1.6665 1.6665 5.21092 1.6665 9.58317C1.6665 13.9554 5.21092 17.4998 9.58317 17.4998Z"
      stroke="#080210"
      strokeWidth="1.5"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
    <path
      d="M18.3332 18.3332L16.6665 16.6665"
      stroke="#080210"
      strokeWidth="1.5"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

const LocationIcon = ({ size = 20 }: { size?: number }) => (
  <svg width={size} height={size} viewBox="0 0 20 20" fill="none" aria-hidden="true">
    <path
      d="M9.9999 11.1917C11.4358 11.1917 12.5999 10.0276 12.5999 8.5917C12.5999 7.15576 11.4358 5.9917 9.9999 5.9917C8.56396 5.9917 7.3999 7.15576 7.3999 8.5917C7.3999 10.0276 8.56396 11.1917 9.9999 11.1917Z"
      stroke="#080210"
      strokeWidth="1.5"
    />
    <path
      d="M3.01675 7.07484C4.65842 -0.141827 15.3501 -0.133494 16.9834 7.08317C17.9418 11.3165 15.3084 14.8998 13.0001 17.1165C11.3251 18.7332 8.67508 18.7332 6.99175 17.1165C4.69175 14.8998 2.05842 11.3082 3.01675 7.07484Z"
      stroke="#080210"
      strokeWidth="1.5"
    />
  </svg>
);

const CalendarIcon = () => (
  <svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
    <path
      d="M6.6665 1.6665V4.1665M13.3335 1.6665V4.1665M2.9165 7.5752H17.0832M17.5 7.08317V14.1665C17.5 16.6665 16.25 18.3332 13.3333 18.3332H6.66667C3.75 18.3332 2.5 16.6665 2.5 14.1665V7.08317C2.5 4.58317 3.75 2.9165 6.66667 2.9165H13.3333C16.25 2.9165 17.5 4.58317 17.5 7.08317Z"
      stroke="currentColor"
      strokeWidth="1.5"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
    <path
      d="M13.0791 11.4167H13.0866M13.0791 13.9167H13.0866M9.99607 11.4167H10.0036M9.99607 13.9167H10.0036M6.91209 11.4167H6.91957M6.91209 13.9167H6.91957"
      stroke="currentColor"
      strokeWidth="2"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

const ClockIcon = () => (
  <svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
    <path
      d="M18.3332 9.99984C18.3332 14.5998 14.5998 18.3332 9.99984 18.3332C5.39984 18.3332 1.6665 14.5998 1.6665 9.99984C1.6665 5.39984 5.39984 1.6665 9.99984 1.6665C14.5998 1.6665 18.3332 5.39984 18.3332 9.99984Z"
      stroke="#080210"
      strokeWidth="1.5"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
    <path
      d="M13.0919 12.65L10.5086 11.1083C10.0586 10.8416 9.69189 10.2 9.69189 9.67497V6.2583"
      stroke="#080210"
      strokeWidth="1.5"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

const MenuIcon = () => (
  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
    <path d="M3 10H21M3 15H21" stroke="#080210" strokeWidth="1.5" strokeLinecap="round" />
  </svg>
);

const BagIcon = () => (
  <svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
    <path
      d="M7.08301 11.875C7.08301 13.475 8.39967 14.7917 9.99967 14.7917C11.5997 14.7917 12.9163 13.475 12.9163 11.875M7.34186 1.66699L4.3252 4.69199M12.6582 1.66699L15.6749 4.69199"
      stroke="currentColor"
      strokeWidth="1.5"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
    <path
      d="M1.66699 6.54167C1.66699 5 2.49199 4.875 3.51699 4.875H16.4837C17.5087 4.875 18.3337 5 18.3337 6.54167C18.3337 8.33333 17.5087 8.20833 16.4837 8.20833H3.51699C2.49199 8.20833 1.66699 8.33333 1.66699 6.54167Z"
      stroke="currentColor"
      strokeWidth="1.5"
    />
    <path
      d="M2.91699 8.33301L4.09199 15.533C4.35866 17.1497 5.00033 18.333 7.38366 18.333H12.4087C15.0003 18.333 15.3837 17.1997 15.6837 15.633L17.0837 8.33301"
      stroke="currentColor"
      strokeWidth="1.5"
      strokeLinecap="round"
    />
  </svg>
);

const LoginIcon = () => (
  <svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
    <path
      d="M7.4165 6.3002C7.67484 3.3002 9.2165 2.0752 12.5915 2.0752H12.6998C16.4248 2.0752 17.9165 3.56686 17.9165 7.29186V12.7252C17.9165 16.4502 16.4248 17.9419 12.6998 17.9419H12.5915C9.2415 17.9419 7.69984 16.7335 7.42484 13.7835M1.6665 10H12.3998M10.5415 7.2085L13.3332 10.0002L10.5415 12.7918"
      stroke="#080210"
      strokeWidth="1.5"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

const StaticLink = ({ href, children, className }: React.PropsWithChildren<{ href: string; className?: string }>) => (
  <a href={href} onClick={stayInSandbox} className={className}>
    {children}
  </a>
);

export const NativeHeader = ({ showLinks = false }: { showLinks?: boolean }) => (
  <header className="agendaally-native-header">
    <div className="xl:container px-4 lg:py-7 sm:py-4 py-2.5 flex items-center justify-between">
      <div className="flex gap-7 items-center">
        <button
          type="button"
          aria-label="Open navigation drawer (interaction stub)"
          className="focus-ring outline-none aspect-square rounded-button p-1"
        >
          <MenuIcon />
        </button>
        <StaticLink href="/" className="relative z-10 lg:z-[4] lg:inline">
          <img
            src={`${assetRoot}/logo.png`}
            alt="AgendaAlly"
            width="148"
            height="28"
            className="object-contain h-7 w-auto"
          />
        </StaticLink>
        <button
          type="button"
          className="stage2-country-indicator border border-footerBg px-4 py-2 rounded-button"
          aria-label="Change country (interaction stub)"
        >
          <span className="flex flex-row items-center gap-x-2">
            <LocationIcon />
            <span className="font-medium">Cameroon</span>
          </span>
        </button>
      </div>
      {showLinks && (
        <nav className="lg:flex items-center gap-14 hidden">
          <StaticLink href="/shops" className="text-base font-medium">Shops</StaticLink>
          <StaticLink href="/shops?column=b_count&sort=desc" className="text-base font-medium">
            Deals
          </StaticLink>
          <StaticLink href="/blogs" className="text-base font-medium">Blog</StaticLink>
          <StaticLink href="/appointments" className="text-base font-medium">My appointments</StaticLink>
        </nav>
      )}
      <div className="relative z-[4]">
        <div className="items-center gap-5 lg:flex hidden">
          <StaticLink href="/cart" className="rounded-button border border-footerBg py-2.5 px-3">
            <BagIcon />
          </StaticLink>
          <StaticLink
            href="/for-business"
            className="rounded-button inline-flex items-center justify-center border border-footerBg overflow-hidden text-ellipsis whitespace-nowrap text-sm font-semibold py-2.5 px-6"
          >
            For business
          </StaticLink>
          <StaticLink
            href="/login"
            className="rounded-button inline-flex items-center justify-center gap-2 border border-footerBg overflow-hidden text-ellipsis whitespace-nowrap text-sm font-semibold py-2.5 px-6"
          >
            <LoginIcon />
            Login
          </StaticLink>
        </div>
        <div className="lg:hidden">
          <button
            type="button"
            aria-label="Login (navigation stub)"
            className="stage2-login-icon border border-footerBg rounded-button p-2"
          >
            <LoginIcon />
          </button>
        </div>
      </div>
    </div>
  </header>
);

const SearchSeparator = ({ isInHeader = false }: { isInHeader?: boolean }) => (
  <div
    className={`w-px hidden lg:block ${isInHeader ? "h-7" : "h-12"}`}
    style={{ backgroundColor: "#000", opacity: 0.2 }}
  />
);

export const NativeSearchField = ({
  variant = "shared",
}: {
  variant?: "shared" | "home2" | "home3";
}) => {
  if (variant === "home2") {
    return (
      <div className="rounded-button bg-white flex items-end justify-between gap-2.5 drop-shadow-gray flex-wrap mx-4 z-[1] py-2.5 px-5">
        <div className="relative xl:min-w-[195px] min-w-full">
          <div className="flex gap-2.5 mb-2"><SearchIcon /><span className="text-sm text-start">Search</span></div>
          <button type="button" className="pl-5 md:py-5 py-4 text-sm w-full outline-none text-start bg-gray-bright rounded-button text-gray-field">
            Type
          </button>
        </div>
        <div className="relative xl:min-w-[195px] min-w-full">
          <div className="flex gap-2.5 mb-2"><LocationIcon /><span className="text-sm text-start">Location</span></div>
          <button type="button" className="pl-5 md:py-5 py-4 text-sm w-full outline-none text-start bg-gray-bright rounded-button text-gray-field">
            Type your location
          </button>
        </div>
        <div className="relative xl:min-w-[195px] min-w-full">
          <div className="flex gap-2.5 mb-2"><ClockIcon /><span className="text-sm text-start">Date</span></div>
          <button type="button" className="relative pl-5 md:py-5 py-4 text-sm w-full outline-none text-start bg-gray-bright rounded-button text-gray-field">
            Choose date<span className="absolute right-4 top-1/2 -translate-y-1/2 text-dark">⌄</span>
          </button>
        </div>
        <div className="relative xl:min-w-[195px] min-w-full">
          <div className="flex gap-2.5 mb-2"><ClockIcon /><span className="text-sm text-start">Time</span></div>
          <button type="button" className="relative pl-5 md:py-5 py-4 text-sm w-full outline-none text-start bg-gray-bright rounded-button text-gray-field">
            Choose time<span className="absolute right-4 top-1/2 -translate-y-1/2 text-dark">⌄</span>
          </button>
        </div>
        <div className="xl:max-w-[160px] w-full">
          <button type="button" className="rounded-button inline-flex items-center justify-center bg-giantsOrange text-white text-base font-semibold py-[18px] px-6 w-full">
            Search
          </button>
        </div>
      </div>
    );
  }

  if (variant === "home3") {
    return (
      <div className="w-full lg:w-auto">
        <div className="flex items-center lg:gap-3 gap-2.5 flex-col md:flex-row w-full lg:w-auto">
          <div className="bg-white px-7 md:py-3 py-0.5 relative w-full lg:w-auto rounded-4xl border border-gray-link lg:border-none">
            <span className="absolute lg:left-7 left-3 top-1/2 -translate-y-1/2"><SearchIcon /></span>
            <button type="button" className="lg:pl-6 pl-3 lg:py-2 py-3 text-sm outline-none lg:min-w-[304px] min-w-full text-start text-gray-field">
              Any
            </button>
          </div>
          <div className="bg-white px-7 md:py-3 py-0.5 relative w-full lg:w-auto rounded-4xl border border-gray-link lg:border-none">
            <span className="absolute lg:left-7 left-3 top-1/2 -translate-y-1/2"><LocationIcon /></span>
            <button type="button" className="lg:pl-6 pl-3 lg:py-2 py-3 text-sm outline-none lg:min-w-[240px] lg:max-w-[240px] min-w-full text-start text-gray-field">
              Where
            </button>
          </div>
          <div className="w-full lg:w-auto h-full">
            <button type="button" className="rounded-button inline-flex items-center justify-center bg-dark text-white text-sm font-semibold py-2.5 px-6 w-full">
              Search
            </button>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="w-full lg:w-auto dropdown-shadow rounded-button">
      <div className="rounded-button bg-white flex items-center justify-between lg:gap-5 gap-2.5 flex-col lg:flex-row w-full lg:w-auto lg:py-2 lg:px-5 px-3 py-3">
        <div className="relative w-full lg:w-auto rounded-button border border-gray-link lg:border-none">
          <span className="absolute lg:left-0 left-3 top-1/2 -translate-y-1/2"><SearchIcon /></span>
          <button type="button" className="lg:pl-6 pl-9 lg:py-2 py-3 text-sm outline-none lg:min-w-[160px] min-w-full text-start text-gray-field">
            Any
          </button>
        </div>
        <SearchSeparator />
        <div className="relative w-full lg:w-auto rounded-button border border-gray-link lg:border-none">
          <span className="absolute lg:left-0 left-3 top-1/2 -translate-y-1/2"><LocationIcon /></span>
          <button type="button" className="lg:pl-6 pl-9 lg:py-2 py-3 text-sm outline-none lg:min-w-[160px] lg:max-w-[200px] min-w-full text-start whitespace-nowrap text-ellipsis overflow-hidden text-gray-field">
            Where
          </button>
        </div>
        <SearchSeparator />
        <div className="relative md:relative w-full lg:w-auto min-w-full lg:min-w-[160px] rounded-button border border-gray-link lg:border-none">
          <span className="absolute lg:left-0 left-3 top-1/2 -translate-y-1/2"><CalendarIcon /></span>
          <button type="button" className="lg:pl-6 pl-9 lg:py-2 py-3 text-sm outline-none text-gray-field">
            Date
          </button>
        </div>
        <SearchSeparator />
        <div className="relative md:relative w-full lg:w-auto min-w-full lg:min-w-[160px] rounded-button border border-gray-link lg:border-none">
          <span className="absolute lg:left-0 left-3 top-1/2 -translate-y-1/2"><ClockIcon /></span>
          <button type="button" className="lg:pl-6 pl-9 lg:py-2 py-3 text-sm outline-none text-gray-field">
            Time
          </button>
        </div>
        <SearchSeparator />
        <div className="w-full lg:w-auto">
          <button type="button" className="rounded-button inline-flex items-center justify-center bg-dark text-white text-sm font-semibold py-2.5 px-6 w-full">
            Search
          </button>
        </div>
      </div>
    </div>
  );
};

interface FixtureCategory {
  id: number;
  img: string;
  translation: { title: string } | null;
}

// Local visual fixtures use the same persisted demo category names and local
// icon files; IDs here are non-operative except Hair Care (1), which is
// recorded as such in the accepted development provenance.
export const categoryFixtures: FixtureCategory[] = [
  { id: 1, img: `${assetRoot}/categories/hair-care.svg`, translation: { title: "Hair Care" } },
  { id: 2, img: `${assetRoot}/categories/nail-care.svg`, translation: { title: "Nail Care" } },
  { id: 3, img: `${assetRoot}/categories/spa-massage.svg`, translation: { title: "Spa & Massage" } },
  { id: 4, img: `${assetRoot}/categories/makeup.svg`, translation: { title: "Makeup" } },
  { id: 5, img: `${assetRoot}/categories/barbershop.svg`, translation: { title: "Barbershop" } },
  { id: 6, img: `${assetRoot}/categories/skin-care.svg`, translation: { title: "Skin Care" } },
  { id: 7, img: `${assetRoot}/categories/tailoring.svg`, translation: { title: "Tailoring" } },
  { id: 8, img: `${assetRoot}/categories/dental-care.svg`, translation: { title: "Dental Care" } },
  { id: 9, img: `${assetRoot}/categories/healthcare.svg`, translation: { title: "Healthcare" } },
  { id: 10, img: `${assetRoot}/categories/handyman.svg`, translation: { title: "Handyman" } },
  { id: 11, img: `${assetRoot}/categories/laundry-dry-cleaning.svg`, translation: { title: "Laundry & Dry Cleaning" } },
];

const categoryColors = [
  "#FFEDD7",
  "#D6FFD2",
  "#F1D2D2",
  "#D8DCFF",
  "#F7D8FF",
  "#C3F8FF",
  "#E8E8E8",
  "#FFE6B4",
  "#FFD2E8",
  "#C6F4E4",
  "#C1E8FF",
];

const Ui1And3ServiceCard = ({ data, index }: { data: FixtureCategory; index: number }) => (
  <div
    style={{
      backgroundColor: categoryColors[index % categoryColors.length],
      backgroundImage: `url(${assetRoot}/service${index + 1}.png)`,
      backgroundRepeat: "no-repeat",
      backgroundSize: "cover",
      backgroundPosition: "center",
    }}
    className="rounded-button relative overflow-hidden flex flex-col md:max-w-[200px] h-[152px]"
  >
    <div className="pt-6 pl-6">
      <span className="md:text-xl text-lg font-semibold line-clamp-1">{data.translation?.title}</span>
    </div>
    <div className="flex items-end justify-end flex-1">
      <div className="relative bottom-0 right-0 max-h-[80%] w-full min-h-[100px]">
        <img
          src={data.img}
          alt={data.translation?.title || "service"}
          className="object-contain max-w-max h-full right-0 absolute"
        />
      </div>
    </div>
  </div>
);

const Ui2ServiceCard = ({ data }: { data: FixtureCategory }) => (
  <div className="overflow-hidden flex flex-col items-center justify-between">
    <div className="relative flex items-center justify-center h-[152px] w-[136px] rounded-xl overflow-hidden">
      <img
        src={data.img}
        alt={data.translation?.title || "service"}
        className="absolute inset-0 w-full h-full object-cover"
      />
    </div>
    <div className="pt-2.5">
      <span className="text-xl font-semibold">{data.translation?.title}</span>
    </div>
  </div>
);

export const NativeServiceCategories = ({ uiType }: { uiType: 1 | 2 | 3 }) => (
  <section className={`agendaally-native-categories ${uiType === 2 ? "" : "mt-20 lg:mt-0 rounded-b-3xl"}`}>
    <div className="flex items-center justify-between mb-4">
      <span className="md:text-head text-xl font-semibold">Services</span>
    </div>
    <div className={`stage2-category-track ${uiType === 2 ? "stage2-category-track-ui2" : ""} ${uiType === 1 ? "xl:container xl:!px-0" : ""}`}>
      {categoryFixtures.map((category, index) => (
        <div
          className={`stage2-category-slide ${uiType === 2 ? "stage2-category-slide-ui2" : ""}`}
          key={`${uiType}-${category.id}`}
        >
          <StaticLink
            href={`/search?category_id=${category.id}`}
            className="block"
          >
            {uiType === 2 ? (
              <Ui2ServiceCard data={category} />
            ) : (
              <Ui1And3ServiceCard data={category} index={index} />
            )}
          </StaticLink>
        </div>
      ))}
    </div>
  </section>
);

export const Home2HeroPeople = () => (
  <div className="hidden md:flex items-center gap-2 pb-32">
    <div className="flex items-center">
      {[1, 2, 3, 4, 5, 6].map((number, index) => (
        <img
          src={`${assetRoot}/user${number}.png`}
          alt={`user-${index}`}
          width="36"
          height="36"
          className={`w-9 h-9 rounded-full ${index !== 0 ? "-ml-3" : ""}`}
          key={number}
        />
      ))}
    </div>
    <span className="text-gray-field text-xl">10 people booked</span>
  </div>
);

export const NativeText = ({ value }: { value: string }) => <>{value}</>;

export { assetRoot };