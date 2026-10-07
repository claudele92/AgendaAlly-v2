import React, { useState } from "react";
import "./_group.css";

const imageRoot = "/__mockup/images/agendaally-stage2-support-current";

type ShopLocation = {
  id: number;
  type: number;
  alias?: string;
  address?: string;
  city?: { translation?: { title: string } };
};

type Shop = {
  id: number;
  slug: string;
  background_img: string;
  logo_img: string;
  verify: boolean;
  phone?: string;
  r_avg?: number;
  r_count?: number;
  distance?: number;
  open: boolean;
  translation: { title: string; description: string; address: string } | null;
  matched_location?: ShopLocation;
  locations?: ShopLocation[];
};

type BlogTranslation = {
  title: string;
  short_desc: string;
  description: string;
};

type Blog = {
  id: number;
  published_at: string;
  img: string;
  r_count?: number;
  translation: BlogTranslation | null;
};

/*
 * API boundaries are static sandbox snapshots with the same fields read by
 * the native components. These are explicitly not live shop/blog records.
 */
const shopSnapshot: Shop = {
  id: 184,
  slug: "studio-example",
  background_img: `${imageRoot}/image-load-failed.png`,
  logo_img: `${imageRoot}/image-load-failed.png`,
  verify: true,
  phone: "+237 6 70 00 00 00",
  r_avg: 4.8,
  r_count: 23,
  distance: 2.4,
  open: true,
  translation: {
    title: "Studio example",
    description: "Hair care and styling",
    address: "Bonapriso, Douala",
  },
  matched_location: {
    id: 18,
    type: 2,
    alias: "Bonapriso",
    address: "Bonapriso, Douala",
    city: { translation: { title: "Douala" } },
  },
  locations: [
    {
      id: 18,
      type: 2,
      alias: "Bonapriso",
      address: "Bonapriso, Douala",
      city: { translation: { title: "Douala" } },
    },
    {
      id: 19,
      type: 2,
      alias: "Akwa",
      address: "Akwa, Douala",
      city: { translation: { title: "Douala" } },
    },
  ],
};

const resultSnapshots: Shop[] = [
  shopSnapshot,
  {
    ...shopSnapshot,
    id: 185,
    slug: "second-studio-example",
    verify: false,
    r_avg: 4.6,
    r_count: 11,
    distance: 4.1,
    translation: {
      title: "Second studio example",
      description: "Salon and beauty services",
      address: "Akwa, Douala",
    },
    matched_location: {
      id: 20,
      type: 2,
      alias: "Akwa",
      address: "Akwa, Douala",
      city: { translation: { title: "Douala" } },
    },
    locations: [],
  },
];

const blogSnapshots: Blog[] = [
  {
    id: 32,
    published_at: "2025-09-14",
    img: `${imageRoot}/image-load-failed.png`,
    r_count: 8,
    translation: {
      title: "Finding the right service for your routine",
      short_desc: "A sample summary used to show the native blog-card layout.",
      description:
        "<p>CMS-authored article content is provided by the native blog API. This short sandbox fixture only demonstrates the extracted reading rhythm and spacing.</p><h2>Plan your visit</h2><p>Keep the appointment details and service information together while you prepare for your visit.</p>",
    },
  },
  {
    id: 33,
    published_at: "2025-09-02",
    img: `${imageRoot}/image-load-failed.png`,
    r_count: 3,
    translation: {
      title: "A thoughtful guide to booking",
      short_desc: "A second sample summary for the native list-card presentation.",
      description:
        "<p>This local-only article sample is not published AgendaAlly editorial copy.</p>",
    },
  },
];

const legalSnapshots = {
  terms: {
    title: "Terms and Conditions",
    description:
      "<p>The native Terms content is supplied by the information API. This extraction preserves its reading layout; actual legal copy is not available in the local source snapshot.</p><h2>Using the service</h2><p>Replace this local-only sample with the current CMS response before using it as legal content.</p>",
  },
  privacy: {
    title: "Privacy Policy",
    description:
      "<p>The native Privacy content is supplied by the information API. This extraction preserves its reading layout; actual policy copy is not available in the local source snapshot.</p><h2>Information and privacy</h2><p>Replace this local-only sample with the current CMS response before using it as policy content.</p>",
  },
};

const faqSnapshot = {
  id: 1,
  uuid: "sandbox-faq-snapshot",
  active: true,
  translation: {
    locale: "en",
    id: 1,
    question: "How can I book a service?",
    answer:
      "This local-only FAQ fixture stands in for the answer returned by the native information API.",
  },
};

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

const MapPinIcon = () => (
  <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
    <path
      d="M13.7467 5.63317C13.0467 2.55317 10.3601 1.1665 8.00006 1.1665C8.00006 1.1665 8.00006 1.1665 7.9934 1.1665C5.64006 1.1665 2.94673 2.5465 2.24673 5.6265C1.46673 9.0665 3.5734 11.9798 5.48006 13.8132C6.18673 14.4932 7.0934 14.8332 8.00006 14.8332C8.90673 14.8332 9.8134 14.4932 10.5134 13.8132C12.4201 11.9798 14.5267 9.07317 13.7467 5.63317ZM8.00006 8.97317C6.84006 8.97317 5.90006 8.03317 5.90006 6.87317C5.90006 5.71317 6.84006 4.77317 8.00006 4.77317C9.16006 4.77317 10.1001 5.71317 10.1001 6.87317C10.1001 8.03317 9.16006 8.97317 8.00006 8.97317Z"
      fill="currentColor"
    />
  </svg>
);

const ReviewIcon = () => (
  <svg width="19" height="19" viewBox="0 0 19 19" fill="none" aria-hidden="true">
    <g opacity="0.4">
      <path
        d="M11.3843 2.375H8.18475C4.90372 2.375 2.24393 5.0348 2.24393 8.31582L2.34249 12.7892C2.36379 13.7558 2.21837 14.7189 1.91262 15.6362C1.77129 16.0602 2.15818 16.4712 2.58992 16.3557L2.95855 16.2571C3.99085 15.981 5.0566 15.8501 6.12504 15.8682L8.7278 15.9125H11.3601C14.2676 15.9125 16.6247 13.5555 16.6247 10.6479V7.61532C16.6247 4.72117 14.2785 2.375 11.3843 2.375Z"
        stroke="currentColor"
        strokeWidth="1.26667"
      />
      <circle cx="6.33268" cy="9.50004" r="0.791667" fill="currentColor" />
      <circle cx="9.49967" cy="9.50004" r="0.791667" fill="currentColor" />
      <circle cx="12.6667" cy="9.50004" r="0.791667" fill="currentColor" />
    </g>
  </svg>
);

const VerifiedIcon = () => (
  <svg viewBox="0 0 24 24" aria-hidden="true" width="20" height="20" fill="#fff">
    <path
      d="M22.25 12c0-1.43-.88-2.67-2.19-3.34.46-1.39.2-2.9-.81-3.91s-2.52-1.27-3.91-.81c-.66-1.31-1.91-2.19-3.34-2.19s-2.67.88-3.33 2.19c-1.4-.46-2.91-.2-3.92.81s-1.26 2.52-.8 3.91c-1.31.67-2.2 1.91-2.2 3.34s.89 2.67 2.2 3.34c-.46 1.39-.21 2.9.8 3.91s2.52 1.26 3.91.81c.67 1.31 1.91 2.19 3.34 2.19s2.68-.88 3.34-2.19c1.39.45 2.9.2 3.91-.81s1.27-2.52.81-3.91c1.31-.67 2.19-1.91 2.19-3.34zm-11.71 4.2L6.8 12.46l1.41-1.42 2.26 2.26 4.8-5.23 1.47 1.36-6.2 6.77z"
      fill="#42a5f5"
    />
  </svg>
);

function ratingText(review?: number | null) {
  if (typeof review !== "number") return "new";
  if (review <= 1) return "very.bad";
  if (review <= 2) return "bad";
  if (review <= 3) return "not.bad";
  if (review <= 4) return "good";
  if (review <= 4.5) return "very.good";
  if (review <= 5) return "exceptional";
  return "new";
}

function translateRating(review?: number | null) {
  const labels: Record<string, string> = {
    "very.bad": "Very bad",
    bad: "Bad",
    "not.bad": "Not bad",
    good: "Good",
    "very.good": "Very good",
    exceptional: "Exceptional",
    new: "New",
  };

  return labels[ratingText(review)];
}

function StaticLink({
  href,
  children,
  className,
}: React.PropsWithChildren<{ href: string; className?: string }>) {
  return (
    <a href={href} onClick={(event) => event.preventDefault()} className={className}>
      {children}
    </a>
  );
}

function SupportImage({
  src,
  alt,
  className = "",
  fill = false,
}: {
  src?: string | null;
  alt: string;
  className?: string;
  fill?: boolean;
}) {
  return (
    <img
      src={src || `${imageRoot}/image-load-failed.png`}
      alt={alt}
      className={`${fill ? "aa-support-image-fill" : ""} ${className}`}
    />
  );
}

function SearchSeparator({ isInHeader = false }: { isInHeader?: boolean }) {
  return (
    <div
      className={`w-px bg-dark bg-opacity-20 hidden lg:block ${isInHeader ? "h-7" : "h-12"}`}
    />
  );
}

function SearchControls({ onChooseLocation }: { onChooseLocation: () => void }) {
  return (
    <div className="dropdown-shadow rounded-button w-full lg:w-auto">
      <div className="rounded-button bg-white flex items-center justify-between lg:gap-5 gap-2.5 flex-col lg:flex-row w-full lg:w-auto lg:py-2 lg:px-5 px-3 py-3">
        <div className="relative w-full lg:w-auto rounded-button border border-gray-link lg:border-none">
          <span className="absolute lg:left-0 left-3 top-1/2 -translate-y-1/2">
            <SearchIcon />
          </span>
          <button
            type="button"
            className="lg:pl-6 pl-9 lg:py-2 py-3 text-sm outline-none lg:min-w-[160px] min-w-full text-start text-gray-field"
          >
            Any
          </button>
        </div>
        <SearchSeparator />
        <div className="relative w-full lg:w-auto rounded-button border border-gray-link lg:border-none">
          <span className="absolute lg:left-0 left-3 top-1/2 -translate-y-1/2">
            <LocationIcon />
          </span>
          <button
            type="button"
            onClick={onChooseLocation}
            className="lg:pl-6 pl-9 lg:py-2 py-3 text-sm outline-none lg:min-w-[160px] lg:max-w-[200px] min-w-full text-start whitespace-nowrap text-ellipsis overflow-hidden text-gray-field"
          >
            Where
          </button>
        </div>
        <SearchSeparator />
        <div className="relative w-full lg:w-auto min-w-full lg:min-w-[160px] rounded-button border border-gray-link lg:border-none">
          <span className="absolute lg:left-0 left-3 top-1/2 -translate-y-1/2">
            <CalendarIcon />
          </span>
          <button
            type="button"
            className="lg:pl-6 pl-9 lg:py-2 py-3 text-sm outline-none text-gray-field"
          >
            Date
          </button>
        </div>
        <SearchSeparator />
        <div className="relative w-full lg:w-auto min-w-full lg:min-w-[160px] rounded-button border border-gray-link lg:border-none">
          <span className="absolute lg:left-0 left-3 top-1/2 -translate-y-1/2">
            <ClockIcon />
          </span>
          <button
            type="button"
            className="lg:pl-6 pl-9 lg:py-2 py-3 text-sm outline-none text-gray-field"
          >
            Time
          </button>
        </div>
        <SearchSeparator />
        <div className="w-full lg:w-auto">
          <button
            type="button"
            className="rounded-button inline-flex items-center justify-center bg-dark text-white text-sm font-semibold py-2.5 px-6 w-full"
          >
            Search
          </button>
        </div>
      </div>
    </div>
  );
}

function LocationInput({
  label,
  value,
  onChange,
}: {
  label: string;
  value: string;
  onChange: (value: string) => void;
}) {
  return (
    <div className="relative flex flex-col items-start w-full">
      <div className="relative w-full">
        <input
          id="native-place-select-input"
          type="text"
          autoComplete="off"
          placeholder=" "
          value={value}
          onChange={(event) => onChange(event.target.value)}
          className="block px-4 w-full text-sm bg-transparent rounded-button border appearance-none focus:outline-none focus:ring-0 peer pl-10 pt-4 pb-[12px] border-gray-link focus-visible:border-primary"
        />
        <div className="absolute inset-y-0 left-3 flex items-center pr-3 z-[4]">
          <LocationIcon />
        </div>
        <label
          htmlFor="native-place-select-input"
          className="absolute text-sm text-gray-placeholder duration-300 transform -translate-y-3 scale-75 top-3.5 origin-[0] peer-focus:text-black left-10"
        >
          {label}
        </label>
      </div>
    </div>
  );
}

function PlaceSelect({ onSelect }: { onSelect: (place: string) => void }) {
  const [value, setValue] = useState("");

  const handleSelectLocation = () => {
    if (value.trim()) onSelect(value.trim());
  };

  return (
    <div className="rounded-button border border-gray-link bg-white md:pt-6 md:px-5 px-4 py-5 flex flex-col justify-between">
      <div>
        <h3 className="text-base font-semibold mb-5">Location</h3>
        <div className="mb-5">
          <LocationInput
            value={value}
            onChange={setValue}
            label="Search services"
          />
        </div>
      </div>
      <button
        type="button"
        onClick={handleSelectLocation}
        className="outline-none rounded-button inline-flex items-center gap-2 justify-center active:translate-y-px hover:brightness-95 bg-dark text-white text-base font-medium py-[13px] px-8 w-full mt-10"
      >
        Search
      </button>
    </div>
  );
}

function ShopCard({ data }: { data: Shop }) {
  const displayAddress =
    data.matched_location?.address ||
    data.translation?.address ||
    data.matched_location?.city?.translation?.title;

  return (
    <div className="relative group border-b border-gray-link pb-4">
      <StaticLink href={`/shops/${data.slug}`} className="flex gap-3 flex-col sm:flex-row">
        <div className="relative sm:h-[140px] aspect-[345/190] sm:aspect-[190/140] rounded-button overflow-hidden">
          <SupportImage
            src={data.background_img}
            alt={data.translation?.title || ""}
            fill
            className="object-cover transition-all group-hover:scale-105"
          />
          <div className="absolute top-3 right-3 rounded-button border border-white w-7 h-7 flex items-center justify-center z-[1] bg-white bg-opacity-60">
            <span className="text-sm font-semibold">{data.r_avg || 0}</span>
          </div>
        </div>
        <div className="flex flex-col justify-between gap-3 flex-1">
          <div className="flex items-start justify-between">
            <div>
              <div className="flex items-center gap-2">
                <strong
                  className={`md:text-lg text-base font-semibold ${
                    data.verify ? "line-clamp-1" : ""
                  }`}
                >
                  {data.translation?.title}
                </strong>
                {data.verify && <VerifiedIcon />}
              </div>
              <span className="text-sm text-gray-field line-clamp-2">
                {data.translation?.description}
              </span>
            </div>
            <div className="rounded-full relative w-10 h-10 flex items-center justify-center z-[1] aspect-square flex-shrink-0">
              <SupportImage
                src={data.logo_img}
                alt={data.translation?.title || "shoplogo"}
                fill
                className="rounded-full object-cover w-9 h-9"
              />
            </div>
          </div>
          <div>
            <div className="flex items-center gap-1">
              <MapPinIcon />
              <span className="text-xs text-gray-field line-clamp-1">
                {displayAddress}
                {displayAddress && data.distance != null && " · "}
                {data.distance != null && `${data.distance} km away from you`}
              </span>
            </div>
            <div className="flex items-center gap-2 mt-3">
              <span className="text-sm font-medium">{translateRating(data.r_avg)}</span>
              <div className="bg-footerBg rounded-full w-1 h-1" />
              <span className="text-sm font-normal">{data.r_count || 0} reviews</span>
            </div>
          </div>
        </div>
      </StaticLink>
    </div>
  );
}

function ShopResults() {
  return (
    <div className="xl:h-[calc(100vh-100px)] flex flex-col pt-7 xl:mx-7">
      <div className="flex items-center justify-between">
        <h2 className="text-2xl font-medium">Results</h2>
        <div className="flex items-center gap-2.5 xl:hidden">
          <button type="button" aria-label="Map view" className="rounded-button border border-gray-link p-2.5">
            ◫
          </button>
          <button type="button" aria-label="Filters" className="rounded-button border border-gray-link p-2.5">
            ≡
          </button>
        </div>
      </div>
      <div className="overflow-y-auto flex-1 lg:pt-11 pt-4 gap-4 flex flex-col h-full">
        {resultSnapshots.map((shop) => (
          <ShopCard data={shop} key={shop.id} />
        ))}
      </div>
    </div>
  );
}

function CustomerSearchSection() {
  const [showLocation, setShowLocation] = useState(false);
  const [selectedPlace, setSelectedPlace] = useState("");

  return (
    <section className="xl:container px-4 py-7">
      <div className="aa-support-section-label mb-3">Extracted sections · customer discovery</div>
      <h2 className="text-xl font-semibold mb-5">Search, results &amp; location control</h2>
      <div className="mb-5">
        <SearchControls onChooseLocation={() => setShowLocation((shown) => !shown)} />
      </div>
      {selectedPlace && <p className="text-sm text-gray-field mb-4">Location: {selectedPlace}</p>}
      {showLocation && (
        <div className="max-w-xl mb-7">
          <PlaceSelect onSelect={(place) => { setSelectedPlace(place); setShowLocation(false); }} />
        </div>
      )}
      <div className="grid xl:grid-cols-12 grid-cols-5">
        <div className="col-span-5 xl:col-span-5">
          <ShopResults />
        </div>
      </div>
    </section>
  );
}

function ShopProfile() {
  const data = shopSnapshot;
  const displayAddress =
    data.matched_location?.address ||
    data.translation?.address ||
    data.matched_location?.city?.translation?.title;
  const [selectedLocation, setSelectedLocation] = useState(data.matched_location?.id);
  const serviceLocations = data.locations?.filter((location) => location.type === 2);

  return (
    <section className="xl:container px-4 pt-7 pb-7">
      <div className="aa-support-section-label mb-3">Extracted sections · shop profile</div>
      <h2 className="text-xl font-semibold mb-5">Top profile, details &amp; branch location</h2>
      <div className="relative rounded-button md:h-[330px] h-[260px] w-full overflow-hidden">
        <div className="absolute z-[1] md:top-5 md:right-5 top-3 right-3 flex items-center gap-2.5">
          <button type="button" aria-label="Shop social links" className="border border-white rounded-full p-1 text-white">
            <span aria-hidden="true">•••</span>
          </button>
          <button type="button" aria-label="Share shop" className="text-white p-2">
            ↗
          </button>
          <button type="button" aria-label="Like shop" className="text-white p-2">
            ♡
          </button>
        </div>
        <SupportImage
          src={data.background_img}
          alt={data.translation?.title || "banner"}
          className="object-cover w-full h-full"
          fill
        />
        <div className="store-bg absolute w-full h-full top-0 left-0" />
        <div className="absolute z-[1] bottom-5 left-5 right-5 flex items-end justify-between">
          <div>
            <div className="relative md:w-20 md:h-20 w-14 h-14 rounded-full overflow-hidden border-2 border-white mb-2">
              <SupportImage
                src={data.logo_img}
                alt={data.translation?.title || "shop"}
                fill
                className="object-contain"
              />
            </div>
            <div className="flex items-center gap-2">
              <h1 className="lg:text-[32px] md:text-3xl text-2xl font-semibold text-white line-clamp-1">
                {data.translation?.title}
              </h1>
              {data.verify && <VerifiedIcon />}
            </div>
            <div className="md:flex items-center gap-1 text-white hidden">
              <MapPinIcon />
              <span className="text-sm line-clamp-1">{displayAddress}</span>
            </div>
            <div className="md:flex items-center gap-3 text-white mt-3 hidden">
              <div className="flex items-center gap-2">
                <div className="w-10 h-10 rounded-button bg-white bg-opacity-30 backdrop-blur-md flex items-center justify-center text-sm">
                  {data.r_avg || 0}
                </div>
                <span className="text-sm font-medium">Great</span>
              </div>
              <div className="rounded-full w-2 h-2 bg-white bg-opacity-30" />
              <span className="text-sm">{data.r_count || 0} reviews</span>
              <div className="rounded-full w-2 h-2 bg-white bg-opacity-30" />
              <span className="text-sm">{data.open ? "Open" : "Closed"}</span>
            </div>
          </div>
          <div className="hidden md:block">
            <button
              type="button"
              className="rounded-button text-sm font-semibold py-2.5 px-6 border border-white text-white"
            >
              See photos
            </button>
          </div>
        </div>
      </div>
      <div className="flex items-center justify-between md:hidden my-4">
        <div className="flex items-center gap-1">
          <MapPinIcon />
          <span className="text-sm line-clamp-1">{displayAddress}</span>
        </div>
        <div className="w-10 h-10 rounded-button flex items-center justify-center text-sm border border-dark">
          {data.r_avg || 0}
        </div>
      </div>
      <div className="flex items-center gap-3 justify-between mb-4 md:hidden">
        <span className="text-sm font-medium">{translateRating(data.r_avg)}</span>
        <div className="rounded-full w-2 h-2 bg-gray-link" />
        <span className="text-sm">
          {data.r_count || 0} reviews
        </span>
        <div className="rounded-full w-2 h-2 bg-gray-link" />
        <span className="text-sm">{data.open ? "Open" : "Closed"}</span>
      </div>
      <div className="md:hidden">
        <button
          type="button"
          className="rounded-button inline-flex items-center gap-2 justify-center active:translate-y-px hover:brightness-95 border border-footerBg text-sm font-semibold py-2.5 px-6 w-full"
        >
          See all photos
        </button>
      </div>
      <div className="grid lg:grid-cols-3 xl:gap-7 sm:gap-4 grid-cols-1 md:gap-7 gap-y-7 mt-6">
        <div className="flex flex-col gap-7 col-span-2">
          <div className="rounded-button py-5 px-5 border border-gray-link col-span-2">
            <h2 className="text-xl font-semibold">Location</h2>
            <a href="#" onClick={(event) => event.preventDefault()} className="flex items-center gap-1 my-5">
              <MapPinIcon />
              <span className="text-sm">{displayAddress}</span>
            </a>
            {serviceLocations && serviceLocations.length > 1 && (
              <div className="mt-5 pt-5 border-t border-gray-link">
                <h3 className="text-sm font-semibold mb-3">Our locations</h3>
                <div className="flex flex-wrap gap-2">
                  {serviceLocations.map((location) => {
                    const label =
                      location.alias || location.address || location.city?.translation?.title;
                    const isActive = selectedLocation === location.id;

                    return (
                      <button
                        key={location.id}
                        type="button"
                        onClick={() => setSelectedLocation(location.id)}
                        className={`text-sm px-3 py-2 rounded-button border transition-colors ${
                          isActive
                            ? "border-dark bg-dark text-white"
                            : "border-gray-link hover:border-dark"
                        }`}
                      >
                        {label}
                      </button>
                    );
                  })}
                </div>
              </div>
            )}
          </div>
        </div>
        <div className="hidden lg:block">
          <div className="sticky top-7 flex flex-col gap-7">
            <div className="rounded-button py-5 px-5 border border-gray-link">
              <div className="flex items-center justify-between gap-2.5 flex-wrap">
                <div className="flex items-center gap-2">
                  <h2 className="xl:text-[28px] md:text-2xl text-xl font-semibold">
                    {data.translation?.title}
                  </h2>
                  {data.verify && <VerifiedIcon />}
                </div>
                <button
                  type="button"
                  className="text-xs font-medium py-2 px-4 border border-footerBg rounded-button"
                >
                  Add comment
                </button>
              </div>
              <div className="py-10 border-b border-gray-link">
                <button
                  type="button"
                  className="rounded-button inline-flex items-center justify-center bg-dark text-white text-base font-medium py-[13px] px-8 w-full"
                >
                  Book now
                </button>
              </div>
              {data.phone && (
                <div className="flex items-center gap-3 mt-3">
                  <span aria-hidden="true">☎</span>
                  <a className="text-lg font-medium" href={`tel:${data.phone}`}>
                    {data.phone}
                  </a>
                </div>
              )}
              <div className="flex items-center gap-3 mt-3">
                <ClockIcon />
                <span className="text-lg font-medium">{data.open ? "Open now" : "Closed"}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}

function formatBlogDate(date: string) {
  const published = new Date(date);
  const day = String(published.getDate()).padStart(2, "0");
  const month = published.toLocaleString("en-US", { month: "short" });
  const year = String(published.getFullYear()).slice(-2);

  return `${day} ${month}, ${year}`;
}

function formatArticleDate(date: string) {
  const published = new Date(date);
  const dayNames = ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"];
  const month = published.toLocaleString("en-US", { month: "short" });

  return `${dayNames[published.getDay()]} ${month}, ${published.getFullYear()}`;
}

function BlogCard({ data, horizontal = false, detailed = false }: {
  data: Blog;
  horizontal?: boolean;
  detailed?: boolean;
}) {
  return (
    <StaticLink href={`/blogs/${data.id}`}>
      <div
        className={
          horizontal
            ? "flex items-center h-full border rounded-3xl border-gray-border dark:border-gray-bold"
            : "flex flex-col h-full"
        }
      >
        <div
          className={
            horizontal
              ? "relative rounded-l-3xl flex-1 h-full overflow-hidden border-t border-gray-border dark:border-gray-bold"
              : "relative md:aspect-[1.5/1] aspect-[2/1] rounded-t-3xl overflow-hidden border-l border-gray-border dark:border-gray-bold"
          }
        >
          <SupportImage
            src={data.img}
            alt={data.translation?.title || "blog"}
            fill
            className="object-cover"
          />
        </div>
        <div
          className={`py-5 px-4 border-gray-border dark:border-gray-bold flex-1 ${
            horizontal ? "rounded-t-3xl" : "border-b border-x rounded-b-3xl"
          }`}
        >
          <div className="text-base font-medium line-clamp-2">{data.translation?.title}</div>
          {detailed && (
            <span className="line-clamp-4 text-base my-5">
              {data.translation?.short_desc}
            </span>
          )}
          <div className="flex items-center gap-7 text-gray-field mt-4">
            <span className="text-sm">{formatBlogDate(data.published_at)}</span>
            <div className="flex items-center gap-1">
              <ReviewIcon />
              <span className="text-sm">{data.r_count || 0}</span>
            </div>
          </div>
        </div>
      </div>
    </StaticLink>
  );
}

function BlogSections() {
  const article = blogSnapshots[0];

  return (
    <section className="xl:container px-4 my-7">
      <div className="aa-support-section-label mb-3">Extracted sections · blog</div>
      <h2 className="text-xl font-semibold mb-5">Blog list card</h2>
      <div className="grid grid-cols-4 gap-7 my-7">
        {blogSnapshots.map((blog, index) => (
          <div
            key={blog.id}
            className={index === 0 ? "lg:col-span-2 lg:row-span-2 col-span-4" : "lg:col-span-2 col-span-4"}
          >
            <BlogCard detailed horizontal={index !== 0} data={blog} />
          </div>
        ))}
      </div>
      <h2 className="text-xl font-semibold mb-5 mt-10">Blog article body</h2>
      <div className="text-sm text-gray-bold mt-7">{formatArticleDate(article.published_at)}</div>
      <h3 className="font-bold md:text-3xl text-2xl">{article.translation?.title}</h3>
      <div className="relative rounded-3xl overflow-hidden lg:aspect-[3/1] md:aspect-[2/1] aspect-square my-5">
        <SupportImage
          src={article.img}
          alt={article.translation?.title || "blog"}
          fill
          className="object-cover"
        />
      </div>
      <div className="grid grid-cols-7 gap-7 my-7">
        <div className="xl:col-span-5 lg:col-span-4 col-span-7">
          <div
            dangerouslySetInnerHTML={{ __html: article.translation?.description || "" }}
            className="text-base leading-relaxed [&_p]:mb-4 [&_p:last-child]:mb-0 [&_h2]:text-xl [&_h2]:font-semibold [&_h2]:mt-8 [&_h2]:mb-3 [&_h2:first-child]:mt-0 [&_a]:underline [&_a]:font-medium [&_em]:text-gray-field"
          />
        </div>
        <div className="xl:col-span-2 lg:col-span-3 col-span-7">
          <div className="sticky top-5 rounded-button border border-gray-link p-5">
            <h3 className="font-semibold">Comments</h3>
            <p className="text-sm text-gray-field mt-2">Article discussion and review controls</p>
          </div>
        </div>
      </div>
    </section>
  );
}

function LegalReadingLayout({ kind }: { kind: "terms" | "privacy" }) {
  const content = legalSnapshots[kind];
  const title = content.title || (kind === "terms" ? "Terms" : "Privacy Policy");

  return (
    <section className="xl:container px-4 py-7">
      <div className="max-w-3xl mx-auto">
        <div className="aa-support-section-label mb-3">
          Extracted section · {kind === "terms" ? "terms" : "privacy"} reading layout
        </div>
        <h1 className="md:text-head text-xl font-semibold mb-6">{title}</h1>
        {content.description ? (
          <div
            className="text-base leading-relaxed [&_p]:mb-4 [&_p:last-child]:mb-0 [&_h2]:text-xl [&_h2]:font-semibold [&_h2]:mt-8 [&_h2]:mb-3 [&_h2:first-child]:mt-0 [&_a]:underline [&_a]:font-medium [&_em]:text-gray-field"
            dangerouslySetInnerHTML={{ __html: content.description }}
          />
        ) : (
          <p role="status">{kind === "terms" ? "Terms content is not available." : "Privacy content is not available."}</p>
        )}
      </div>
    </section>
  );
}

function FaqItem() {
  const [open, setOpen] = useState(false);

  return (
    <div className="bg-gray-faq dark:bg-gray-darkSegment rounded-2xl w-full">
      <button
        type="button"
        aria-expanded={open}
        onClick={() => setOpen((value) => !value)}
        className="text-start py-6 px-5 flex items-center justify-between w-full text-sm"
      >
        {faqSnapshot.translation.question}
        <div className="aa-support-faq-chevron" data-open={open}>
          <svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
            <path d="m7 4 6 6-6 6" stroke="currentColor" strokeWidth="1.5" />
          </svg>
        </div>
      </button>
      <div className="aa-support-faq-answer" data-open={open}>
        <div className="px-5 pb-6">{faqSnapshot.translation.answer}</div>
      </div>
    </div>
  );
}

function FaqSection() {
  return (
    <section className="xl:container px-4 h-full relative py-7">
      <div className="aa-support-section-label mb-3">Extracted section · FAQ</div>
      <h2 className="my-7 md:text-head text-xl font-semibold">FAQ item</h2>
      <FaqItem />
    </section>
  );
}

function Footer() {
  const columns = [
    {
      title: "Information",
      links: [
        ["About", "/about"],
        ["Careers", "/careers"],
        ["Contact", "/contact"],
        ["Blog", "/blogs"],
      ],
    },
    {
      title: "Help",
      links: [
        ["FAQs", "/faq"],
        ["Terms", "/terms"],
        ["Privacy", "/privacy"],
      ],
    },
    { title: "Socials", links: [] as string[][] },
  ];

  return (
    <footer className="bg-footerBg pt-12 pb-5">
      <div className="xl:container px-4 text-white flex justify-between flex-wrap xl:flex-nowrap">
        <div className="flex md:flex-col md:justify-between md:w-auto w-full justify-center mb-10 md:mb-0 gap-8">
          <div>
            <div className="relative h-[45px] max-w-[420px]">
              <StaticLink href="/" className="mb-3 max-w-max">
                <img
                  src={`${imageRoot}/logo.png`}
                  alt="AgendaAlly"
                  className="object-contain max-h-11 h-full !w-auto"
                  width="122"
                  height="18"
                />
              </StaticLink>
            </div>
            <p className="text-sm max-w-sm text-white text-opacity-75" role="status">
              Store information and official contact details are not available in this development
              preview until configured.
            </p>
          </div>
        </div>
        <div className="md:gap-28 gap-3 justify-between lg:justify-start w-full lg:max-w-max lg:flex grid sm:grid-cols-2 grid-cols-1 flex-wrap xl:flex-nowrap">
          {columns.map((column) => (
            <details key={column.title} className="aa-support-footer-column" open>
              <summary className="flex items-center justify-between font-medium text-head md:mb-2.5 mb-1 w-full py-3 sm:py-0 sm:border-none border-white border-opacity-20">
                {column.title}
                <svg className="h-5 w-5 sm:hidden" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <path d="m6 9 6 6 6-6" stroke="currentColor" strokeWidth="1.6" />
                </svg>
              </summary>
              <div className="flex flex-col md:gap-6 gap-2">
                {column.links.map(([label, href]) => (
                  <StaticLink
                    key={label}
                    href={href}
                    className="text-lg font-medium transition-all hover:underline"
                  >
                    {label}
                  </StaticLink>
                ))}
              </div>
            </details>
          ))}
        </div>
      </div>
      <div className="xl:container px-4 md:mt-12 mt-6">
        <div className="border-t border-white border-opacity-20 pt-3">
          <p className="text-white text-sm" />
        </div>
      </div>
    </footer>
  );
}

function ContactNotice() {
  return (
    <section className="xl:container px-4 py-7">
      <div className="aa-support-section-label mb-3">Extracted section · contact notice</div>
      <h2 className="md:text-head text-xl font-medium my-6">Contact</h2>
      <div className="flex flex-col gap-6 my-10">
        <p
          className="md:text-base text-sm text-gray-600"
          role="alert"
          data-testid="status-contact-settings-error"
        >
          Contact information could not be loaded for this development preview.
        </p>
      </div>
    </section>
  );
}

export function CurrentSupport() {
  const legalSections: Array<"terms" | "privacy"> = ["terms", "privacy"];

  return (
    <main className="agendaally-stage2-support-current min-h-screen">
      <CustomerSearchSection />
      <ShopProfile />
      <BlogSections />
      <div className="grid lg:grid-cols-2 gap-4">
        {legalSections.map((kind) => (
          <LegalReadingLayout key={kind} kind={kind} />
        ))}
      </div>
      <FaqSection />
      <div className="aa-support-section-label xl:container px-4 mb-3">
        Extracted section · footer
      </div>
      <Footer />
      <ContactNotice />
    </main>
  );
}

export default CurrentSupport;