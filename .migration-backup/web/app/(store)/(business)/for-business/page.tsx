import CalendarCheckIcon from "@/assets/icons/calendar-check";
import ProgressIcon from "@/assets/icons/progress";
import MoneyOutlinedIcon from "@/assets/icons/money-outlined";
import BellOutlinedIcon from "@/assets/icons/bell-outlined";
import BagIcon from "@/assets/icons/bag";
import StoreIcon from "@/assets/icons/store";
import { ServiceCategoriesGrid } from "@/app/(store)/(booking)/(with-footer)/(home)/components/service-categories-grid";
import { categoryService } from "@/services/category";
import { globalService } from "@/services/global";
import { cookies } from "next/headers";
import { parseSettings } from "@/utils/parse-settings";
import type { Category } from "@/types/category";
import Link from "next/link";
import { TopHeader } from "../components/top-header";
import { BusinessDownloads } from "./business-downloads";
import styles from "./for-business.module.css";

const getServiceCategories = async (lang: string): Promise<Category[]> => {
  const categories: Category[] = [];
  let page = 1;
  let lastPage = 1;

  do {
    const response = await categoryService.getAll({
      lang,
      type: "service",
      perPage: 100,
      page,
      column: "input",
      sort: "asc",
    });
    categories.push(...response.data);
    lastPage = response.meta.last_page;
    page = response.meta.current_page + 1;
  } while (page <= lastPage);

  return categories;
};

const capabilities = [
  {
    icon: <CalendarCheckIcon size={32} />,
    title: "Online booking",
    description:
      "Give customers a booking entry point without promising unverified supply or appointment outcomes.",
  },
  {
    icon: <ProgressIcon />,
    title: "Management",
    description:
      "Manage services, scheduled work and customer records inside the existing permission-scoped workspace.",
  },
  {
    icon: <MoneyOutlinedIcon />,
    title: "Payment",
    description:
      "Configured methods and the existing protected checkout determine what payment options are available.",
  },
  {
    icon: <BellOutlinedIcon />,
    title: "Notifications",
    description:
      "Notification settings are available as a workspace topic; message delivery depends on provider configuration.",
  },
];

const CalendarShowcase = () => (
  <article className={styles.calendarCard} aria-labelledby="business-calendar-heading">
    <div className={styles.calendarHeader}>
      <div>
        <span className={styles.eyebrow}>AgendaAlly for Business</span>
        <h2 id="business-calendar-heading">Calendar · day view</h2>
      </div>
      <span className={styles.illustrativeTag}>Illustrative</span>
    </div>
    <div className={styles.calendarBody}>
      <div className={styles.calendarTimes} aria-hidden="true">
        {["09:00", "09:30", "10:00", "10:30", "11:00", "11:30"].map((time) => (
          <span key={time}>{time}</span>
        ))}
      </div>
      <div className={styles.calendarGrid} aria-label="Sample schedule layout">
        <div className={`${styles.calendarEvent} ${styles.eventOne}`}>
          <strong>Service appointment</strong>
          <span>09:00–09:30 · Service master</span>
          <small>Booking status · illustrative</small>
        </div>
        <div className={`${styles.calendarEvent} ${styles.eventTwo}`}>
          <strong>Service appointment</strong>
          <span>10:00–10:30 · Service master</span>
          <small>Booking status · illustrative</small>
        </div>
        <div className={`${styles.calendarEvent} ${styles.disabledEvent}`}>
          <strong>Disabled time</strong>
          <span>11:00–11:30</span>
          <small>Calendar block · illustrative</small>
        </div>
      </div>
    </div>
    <p className={styles.calendarNote}>
      Schematic concept only. No real booking, customer, staff, branch, or calendar data is shown.
    </p>
  </article>
);

const ForBusinessPage = async () => {
  const cookieStore = await cookies();
  const lang = cookieStore.get("lang")?.value || "en";
  const [settingsResult, categoriesResult] = await Promise.all([
    globalService
      .settings()
      .then((result) => ({ settings: result?.data, failed: false }))
      .catch(() => ({ settings: undefined, failed: true })),
    getServiceCategories(lang)
      .then((categories) => ({ categories, failed: false }))
      .catch(() => ({ categories: [] as Category[], failed: true })),
  ]);
  const productsEnabled =
    !settingsResult.failed && parseSettings(settingsResult.settings)?.products_enabled === "1";

  return (
    <main className={styles.page}>
      <section className={styles.hero}>
        <div className={`${styles.container} ${styles.heroGrid}`}>
          <TopHeader
            variant="stage2"
            title="Appointments, services and people—clearer in one workday."
            description="A practical workspace for service businesses: see scheduled work, manage service offerings and keep customer details in reach."
            buttonText="get.started"
            link="/be-seller"
          />
          <CalendarShowcase />
        </div>
      </section>

      <section className={`${styles.container} ${styles.capabilitiesSection}`}>
        <div className={styles.sectionHeading}>
          <span className={styles.eyebrow}>A grounded feature story</span>
          <h2>Built around the working day—not a hotel reservation grid.</h2>
          <p>
            The native seller Calendar shows booking events with service titles, start and end
            times, status labels, and separately represented disabled times. This schematic follows
            those concepts; it does not add room occupancy, branch assignment, or product events.
          </p>
        </div>
        <div className={styles.capabilityGrid}>
          {capabilities.map((feature) => (
            <article className={styles.capabilityCard} key={feature.title}>
              <span className={styles.capabilityIcon} aria-hidden="true">
                {feature.icon}
              </span>
              <h3>{feature.title}</h3>
              <p>{feature.description}</p>
            </article>
          ))}
        </div>
      </section>

      <section className={`${styles.container} ${styles.businessToolsSection}`}>
        <div className={styles.sectionHeading}>
          <span className={styles.eyebrow}>Services & independent commerce</span>
          <h2>Two marketplace paths, with their own catalog and rules.</h2>
          <p>
            Services support booking discovery. Products use their own catalog, stock, and checkout
            path. Branch and shop visibility inside the workspace remains subject to each account’s
            existing role and permissions.
          </p>
        </div>
        <div className={styles.marketplaceGrid}>
          <article className={styles.marketplaceCard}>
            <span className={styles.marketplaceIcon} aria-hidden="true">
              <CalendarCheckIcon size={26} />
            </span>
            <div>
              <h3>Services & team</h3>
              <p>Service setup, specialists and staff, bookings, and schedules stay in view.</p>
              <Link href="/services" className={styles.textLink}>
                Explore service categories <span aria-hidden="true">→</span>
              </Link>
            </div>
          </article>
          <article className={`${styles.marketplaceCard} ${styles.productCard}`}>
            <span className={styles.marketplaceIcon} aria-hidden="true">
              <BagIcon />
            </span>
            <div>
              <h3>Products & branches</h3>
              <p>
                Product listings and stock belong to the commerce catalog, separate from Calendar
                events and service schedules.
              </p>
              {productsEnabled ? (
                <Link href="/products" className={styles.textLink}>
                  Explore the product marketplace <span aria-hidden="true">→</span>
                </Link>
              ) : settingsResult.failed ? (
                <p className={styles.marketplaceStatus} role="status">
                  Product availability could not be confirmed because marketplace settings are
                  unavailable.
                </p>
              ) : (
                <p className={styles.marketplaceStatus} role="status">
                  Product discovery is not enabled for the current storefront configuration.
                </p>
              )}
            </div>
            <span className={styles.branchIcon} aria-hidden="true">
              <StoreIcon />
            </span>
          </article>
        </div>
      </section>

      <section className={`${styles.categoriesSection} aa-s2`}>
        <div className={styles.container}>
          <ServiceCategoriesGrid
            categories={categoriesResult.categories}
            loadError={categoriesResult.failed}
          />
        </div>
      </section>

      <section className={`${styles.container} ${styles.downloadSection}`}>
        <div className={styles.downloadCopy}>
          <span className={styles.eyebrow}>Business apps</span>
          <h2>Business app downloads</h2>
          <p>
            Official download links are shown here when configured.
          </p>
        </div>
        <div className={styles.downloadLinks}>
          <BusinessDownloads />
        </div>
      </section>
    </main>
  );
};

export default ForBusinessPage;