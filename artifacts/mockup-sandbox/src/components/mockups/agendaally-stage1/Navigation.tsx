import "./_group.css";
import "./_navigation.css";
import { Brand } from "./_shared/Stage1Primitives";
import { useMemo, useState, type ReactNode } from "react";
import {
  ArrowDownLeft,
  ArrowRight,
  ArrowUpRight,
  CalendarDays,
  Check,
  ChevronDown,
  ChevronRight,
  CircleHelp,
  Clock3,
  Command,
  CreditCard,
  FileText,
  Globe2,
  LayoutGrid,
  Menu,
  Package,
  Search,
  Settings2,
  ShoppingBag,
  Scissors,
  ShieldCheck,
  Store,
  Users,
  Wallet,
  X,
} from "lucide-react";

type Role =
  | "Vendor"
  | "Branch staff"
  | "Master"
  | "Finance"
  | "Country manager"
  | "Platform admin"
  | "Customer";

type NavItem = { label: string; icon: typeof LayoutGrid; id: string };
type NavGroup = { title: string; items: NavItem[] };

const navByRole: Record<Role, NavGroup[]> = {
  Vendor: [
    { title: "OVERVIEW", items: [{ label: "Workspace", icon: LayoutGrid, id: "workspace" }] },
    {
      title: "BOOKING",
      items: [
        { label: "Appointments", icon: CalendarDays, id: "appointments" },
        { label: "Services & team", icon: Scissors, id: "services" },
      ],
    },
    {
      title: "PRODUCT COMMERCE",
      items: [
        { label: "Orders", icon: ShoppingBag, id: "orders" },
        { label: "Products", icon: Package, id: "products" },
      ],
    },
    {
      title: "BUSINESS",
      items: [
        { label: "Branches", icon: Store, id: "branches" },
        { label: "Customers", icon: Users, id: "customers" },
        { label: "Earnings & payouts", icon: Wallet, id: "finance" },
      ],
    },
  ],
  "Branch staff": [
    { title: "THIS BRANCH", items: [{ label: "Branch overview", icon: LayoutGrid, id: "workspace" }] },
    {
      title: "DAILY WORK",
      items: [
        { label: "Appointments", icon: CalendarDays, id: "appointments" },
        { label: "Orders", icon: ShoppingBag, id: "orders" },
        { label: "Customers", icon: Users, id: "customers" },
      ],
    },
  ],
  Master: [
    { title: "MY WORK", items: [{ label: "My appointments", icon: CalendarDays, id: "appointments" }] },
    { title: "ACCOUNT", items: [{ label: "Availability", icon: Clock3, id: "availability" }] },
  ],
  Finance: [
    { title: "FINANCE", items: [{ label: "Earnings", icon: Wallet, id: "finance" }] },
    { title: "SETTLEMENTS", items: [{ label: "Payout history", icon: CreditCard, id: "payouts" }] },
  ],
  "Country manager": [
    { title: "CAMEROON", items: [{ label: "Country overview", icon: LayoutGrid, id: "workspace" }] },
    {
      title: "MARKETPLACE",
      items: [
        { label: "Businesses", icon: Store, id: "businesses" },
        { label: "Customers", icon: Users, id: "customers" },
        { label: "Bookings", icon: CalendarDays, id: "appointments" },
        { label: "Product orders", icon: ShoppingBag, id: "orders" },
      ],
    },
    { title: "OPERATIONS", items: [{ label: "Country staff", icon: ShieldCheck, id: "staff" }] },
  ],
  "Platform admin": [
    { title: "PLATFORM", items: [{ label: "Global overview", icon: LayoutGrid, id: "workspace" }] },
    {
      title: "MARKETPLACE",
      items: [
        { label: "Countries", icon: Globe2, id: "countries" },
        { label: "Businesses", icon: Store, id: "businesses" },
        { label: "Customers", icon: Users, id: "customers" },
      ],
    },
    {
      title: "OPERATIONS",
      items: [
        { label: "Bookings", icon: CalendarDays, id: "appointments" },
        { label: "Product orders", icon: ShoppingBag, id: "orders" },
        { label: "Payout oversight", icon: Wallet, id: "finance" },
        { label: "Platform settings", icon: Settings2, id: "settings" },
      ],
    },
  ],
  Customer: [
    { title: "DISCOVER", items: [{ label: "Explore", icon: Search, id: "explore" }] },
    { title: "YOUR ACCOUNT", items: [{ label: "My bookings", icon: CalendarDays, id: "appointments" }] },
  ],
};

const previewContent: Record<string, { eyebrow: string; title: string; description: string; rows: string[] }> = {
  workspace: {
    eyebrow: "TUESDAY, 18 JUNE · DOUALA",
    title: "Good morning, Naomi",
    description: "A clear view of today at Maison Naya, Bonapriso.",
    rows: ["10:30 · Amina T. · Knotless braids", "12:00 · Mireille K. · Wash & set", "14:15 · Chantal N. · Trim & finish"],
  },
  appointments: {
    eyebrow: "TODAY · TUESDAY, 18 JUNE",
    title: "Appointments",
    description: "Bookings scheduled for the selected branch.",
    rows: ["10:30 · Amina T. · Knotless braids · Confirmed", "12:00 · Mireille K. · Wash & set · Confirmed", "14:15 · Chantal N. · Trim & finish · Awaiting confirmation"],
  },
  services: {
    eyebrow: "BOOKING · SERVICE MENU",
    title: "Services & team",
    description: "Manage the services and specialists available to book.",
    rows: ["Knotless braids · 3h 30m · 18,000 XAF", "Wash & set · 1h 15m · 8,500 XAF", "Trim & finish · 45m · 6,000 XAF"],
  },
  orders: {
    eyebrow: "PRODUCT COMMERCE · ORDERS",
    title: "Recent orders",
    description: "Fulfilment overview for Maison Naya, Bonapriso.",
    rows: ["AA-2048 · Shea curl cream · Ready for pickup", "AA-2045 · Satin bonnet, 2 items · Processing", "AA-2039 · Hibiscus hair oil · Ready for pickup"],
  },
  products: {
    eyebrow: "PRODUCT COMMERCE · CATALOG",
    title: "Products",
    description: "Keep your shop catalog current and easy to browse.",
    rows: ["Shea curl cream · 12 in stock · 9,500 XAF", "Satin bonnet · 8 in stock · 4,000 XAF", "Hibiscus hair oil · 5 in stock · 6,500 XAF"],
  },
  branches: {
    eyebrow: "BUSINESS · LOCATIONS",
    title: "Your branches",
    description: "Branch context determines the work shown in this workspace.",
    rows: ["Bonapriso · Douala · Current branch", "Akwa · Douala · 4 team members", "Bonamoussadi · Douala · 3 team members"],
  },
  customers: {
    eyebrow: "RELATIONSHIPS",
    title: "Customers",
    description: "Recent customer activity for the current scope.",
    rows: ["Amina T. · Last visit today · 6 visits", "Mireille K. · Last visit 12 Jun · 3 visits", "Chantal N. · New customer · Booking today"],
  },
  finance: {
    eyebrow: "BUSINESS · EARNINGS",
    title: "Earnings & payouts",
    description: "Review financial activity available to your assigned role.",
    rows: ["This week · 284,500 XAF · Preview only", "Next payout · 21 Jun · Destination ending 0482", "Recent payout · 14 Jun · Completed"],
  },
  payouts: {
    eyebrow: "SETTLEMENTS · FINANCE SCOPE",
    title: "Payout history",
    description: "Finance access is shown only where granted by the business.",
    rows: ["14 Jun · 186,000 XAF · Completed", "07 Jun · 243,500 XAF · Completed", "31 May · 198,000 XAF · Completed"],
  },
  availability: {
    eyebrow: "MY WORK · PERSONAL SCOPE",
    title: "My appointments",
    description: "Your own assigned appointments and availability.",
    rows: ["10:30 · Amina T. · Knotless braids", "12:00 · Mireille K. · Wash & set", "14:15 · Chantal N. · Trim & finish"],
  },
  businesses: {
    eyebrow: "CAMEROON · COUNTRY SCOPE",
    title: "Businesses",
    description: "Marketplace operations within your assigned country.",
    rows: ["Maison Naya · Douala · Active", "Atelier Sawa · Yaoundé · Active", "Bela Botanics · Bafoussam · Review pending"],
  },
  staff: {
    eyebrow: "CAMEROON · COUNTRY OPERATIONS",
    title: "Country staff",
    description: "Manage country-level operational access, not platform-wide roles.",
    rows: ["Country operations · 4 assigned staff", "Douala support · 2 assigned staff", "Yaoundé support · 2 assigned staff"],
  },
  countries: {
    eyebrow: "PLATFORM · GLOBAL SCOPE",
    title: "Countries",
    description: "Platform-wide administration is separate from country management.",
    rows: ["Cameroon · Active · Central Africa", "Senegal · Active · West Africa", "Côte d’Ivoire · Active · West Africa"],
  },
  settings: {
    eyebrow: "PLATFORM · CONFIGURATION",
    title: "Platform settings",
    description: "Global controls appear only in the platform administration scope.",
    rows: ["Marketplace configuration", "Country availability", "Role and permission policy"],
  },
  explore: {
    eyebrow: "AGENDAALLY · DOUALA",
    title: "Find something good nearby",
    description: "Choose how you’d like to explore local businesses.",
    rows: ["Braids & styling · 18 services nearby", "Wellness · 12 services nearby", "Beauty essentials · 26 products nearby"],
  },
};

const scopeNote: Record<Role, string> = {
  Vendor: "Business-wide · 3 branches",
  "Branch staff": "Bonapriso branch only",
  Master: "Personal appointments only",
  Finance: "Earnings & payouts as granted",
  "Country manager": "Cameroon only · no global administration",
  "Platform admin": "All countries · platform scope",
  Customer: "Personal account",
};

function IconButton({ label, onClick, children, className = "" }: { label: string; onClick: () => void; children: ReactNode; className?: string }) {
  return <button type="button" className={`aa-nav-icon-button ${className}`} aria-label={label} onClick={onClick}>{children}</button>;
}

export function Navigation() {
  const [role, setRole] = useState<Role>("Vendor");
  const [activeId, setActiveId] = useState("workspace");
  const [activeGroup, setActiveGroup] = useState<"Booking" | "Shop products">("Booking");
  const [branch, setBranch] = useState("Bonapriso");
  const [branchOpen, setBranchOpen] = useState(false);
  const [menuOpen, setMenuOpen] = useState(false);
  const [search, setSearch] = useState("");
  const [notice, setNotice] = useState("");

  const groups = navByRole[role];
  const allItems = groups.flatMap((group) => group.items);
  const activeItem = allItems.find((item) => item.id === activeId) ?? allItems[0];
  const page = previewContent[activeItem.id] ?? previewContent.workspace;
  const visibleGroups = useMemo(
    () => groups.map((group) => ({ ...group, items: group.items.filter((item) => item.label.toLowerCase().includes(search.toLowerCase())) })).filter((group) => group.items.length),
    [groups, search],
  );

  const chooseRole = (nextRole: Role) => {
    setRole(nextRole);
    setActiveId(navByRole[nextRole][0].items[0].id);
    setSearch("");
    setMenuOpen(false);
    setNotice("");
  };

  const customer = role === "Customer";
  const selectItem = (id: string) => {
    setActiveId(id);
    setMenuOpen(false);
    setNotice("");
  };

  const sideNavigation = (
    <div className="aa-nav-side-inner">
      <div className="aa-nav-brand">
        <a className="aa-nav-wordmark" href="#workspace" onClick={(event) => { event.preventDefault(); selectItem("workspace"); }}>
          <Brand />
        </a>
        <IconButton label="Close navigation" className="aa-nav-close-mobile" onClick={() => setMenuOpen(false)}><X size={19} /></IconButton>
      </div>

      {customer ? (
        <div className="aa-nav-customer-switch" role="group" aria-label="Marketplace">
          <button className={activeGroup === "Booking" ? "is-active" : ""} onClick={() => { setActiveGroup("Booking"); selectItem("explore"); }} type="button"><CalendarDays size={16} />Book services</button>
          <button className={activeGroup === "Shop products" ? "is-active" : ""} onClick={() => { setActiveGroup("Shop products"); selectItem("explore"); }} type="button"><ShoppingBag size={16} />Shop products</button>
        </div>
      ) : (
        <div className="aa-nav-location">
          <span className="aa-nav-loc-label">WORKING IN</span>
          <button className="aa-nav-branch-select" type="button" aria-expanded={branchOpen} onClick={() => setBranchOpen(!branchOpen)}>
            <span className="aa-nav-location-icon"><Store size={16} /></span>
            <span className="aa-nav-branch-copy"><strong>{role === "Country manager" ? "Cameroon" : role === "Platform admin" ? "All countries" : branch}</strong><small>{role === "Country manager" ? "Country scope" : role === "Platform admin" ? "Platform scope" : "Douala, Cameroon"}</small></span>
            {role === "Vendor" && <ChevronDown size={15} />}
          </button>
          {branchOpen && role === "Vendor" && <div className="aa-nav-branch-menu" role="menu">
            {["Bonapriso", "Akwa", "Bonamoussadi"].map((name) => <button type="button" role="menuitem" key={name} onClick={() => { setBranch(name); setBranchOpen(false); }}><span>{name}<small>Douala, Cameroon</small></span>{branch === name && <Check size={15} />}</button>)}
          </div>}
        </div>
      )}

      <label className="aa-nav-search"><Search size={16} /><input aria-label="Search navigation" placeholder="Find a page" value={search} onChange={(event) => setSearch(event.target.value)} /><kbd>/</kbd></label>
      <nav className="aa-nav-groups" aria-label={customer ? "Customer navigation" : `${role} navigation`}>
        {visibleGroups.map((group) => <section className="aa-nav-section" key={group.title}>
          <h2>{group.title}</h2>
          {group.items.map(({ label, icon: Icon, id }) => <button type="button" key={id} className={`aa-nav-link ${activeId === id ? "is-current" : ""}`} aria-current={activeId === id ? "page" : undefined} onClick={() => selectItem(id)}>
            <Icon size={17} strokeWidth={1.8} /><span>{label}</span>
          </button>)}
        </section>)}
        {visibleGroups.length === 0 && <p className="aa-nav-no-results">No pages match “{search}”.</p>}
      </nav>

      <div className="aa-nav-side-footer">
        <div className="aa-nav-review-note"><ShieldCheck size={15} /><span>Role-aware preview<br /><small>Access remains server-controlled</small></span></div>
        <button className="aa-nav-help" type="button" onClick={() => setNotice("Help centre is represented in this prototype only.")}><CircleHelp size={17} />Help centre<ArrowRight size={14} /></button>
        <div className="aa-nav-profile"><span className="aa-nav-avatar">{customer ? "AM" : "NM"}</span><span><strong>{customer ? "Amina M." : "Naomi Mbarga"}</strong><small>{role === "Vendor" ? "Business owner" : role}</small></span><ChevronDown size={15} /></div>
      </div>
    </div>
  );

  return (
    <div className="aa-stage1 aa-nav-app">
      <a className="aa-nav-skip" href="#aa-nav-main">Skip to workspace</a>
      <aside className="aa-nav-sidebar">{sideNavigation}</aside>
      {menuOpen && <div className="aa-nav-scrim" onClick={() => setMenuOpen(false)} aria-hidden="true" />}
      <aside className={`aa-nav-mobile-drawer ${menuOpen ? "is-open" : ""}`} aria-hidden={!menuOpen} inert={!menuOpen}>{sideNavigation}</aside>

      <section className="aa-nav-main-shell">
        <header className="aa-nav-topbar">
          <div className="aa-nav-top-left">
            <IconButton label={menuOpen ? "Close menu" : "Open menu"} className="aa-nav-menu-button" onClick={() => setMenuOpen(!menuOpen)}>{menuOpen ? <X size={20} /> : <Menu size={20} />}</IconButton>
            <div className="aa-nav-breadcrumb"><span>{customer ? "Marketplace" : role === "Country manager" ? "Cameroon" : role === "Platform admin" ? "Platform" : "Maison Naya"}</span><ChevronRight size={14} /><strong>{activeItem.label}</strong></div>
          </div>
          <div className="aa-nav-top-actions">
            <span className="aa-nav-context"><span className="aa-nav-context-dot" />{customer ? "Douala, Cameroon" : role === "Platform admin" ? "Global · all countries" : role === "Country manager" ? "Cameroon · country scope" : `${branch} · Douala`}</span>
            <button className="aa-nav-top-help" type="button" aria-label="Open help" onClick={() => setNotice("Help centre is represented in this prototype only.")}><CircleHelp size={18} /></button>
          </div>
        </header>
        <div className="aa-nav-review-banner"><span><span className="aa-nav-banner-dot" />REVIEW PROPOSAL</span><p>Role context is illustrative. It does not sign in, impersonate, or change permissions.</p></div>

        <div className="aa-nav-review-control">
          <label htmlFor="aa-nav-role">Review-only role context</label>
          <div className="aa-nav-role-wrap"><Command size={15} /><select id="aa-nav-role" value={role} onChange={(event) => chooseRole(event.target.value as Role)} aria-describedby="aa-nav-role-note">
            {(["Customer", "Vendor", "Branch staff", "Master", "Finance", "Country manager", "Platform admin"] as Role[]).map((item) => <option value={item} key={item}>{item}</option>)}
          </select><ChevronDown size={15} /></div>
          <span id="aa-nav-role-note">{scopeNote[role]}</span>
        </div>

        <main className="aa-nav-content" id="aa-nav-main">
          <div className="aa-nav-content-heading">
            <div><p className="aa-nav-eyebrow">{page.eyebrow}</p><h1>{page.title}</h1><p className="aa-nav-description">{page.description}</p></div>
            {!customer && role !== "Master" && role !== "Finance" && <button className="aa-nav-branch-pill" type="button" onClick={() => { if (role === "Vendor") setBranchOpen(!branchOpen); else setNotice("Scope is assigned by the platform and cannot be changed here."); }}><Store size={15} />{role === "Country manager" ? "Cameroon" : role === "Platform admin" ? "Global" : branch}<ChevronDown size={14} /></button>}
          </div>

          {customer ? (
            <section className="aa-nav-discovery">
              <div className="aa-nav-discovery-title"><div><span className="aa-nav-eyebrow">{activeGroup === "Booking" ? "BOOK A LOCAL SERVICE" : "SHOP LOCAL FINDS"}</span><h2>{activeGroup === "Booking" ? "What would you like to book?" : "Thoughtful finds, nearby."}</h2></div><span className="aa-nav-place"><Globe2 size={15} />Douala, CM</span></div>
              <div className="aa-nav-discovery-actions">
                <button type="button" onClick={() => setNotice(activeGroup === "Booking" ? "Service discovery is a visual preview; no booking is created." : "Product discovery is a visual preview; no order is placed.")}>
                  <span className="aa-nav-discovery-icon">{activeGroup === "Booking" ? <Scissors size={21} /> : <ShoppingBag size={21} />}</span><span><strong>{activeGroup === "Booking" ? "Browse services" : "Browse products"}</strong><small>{activeGroup === "Booking" ? "Find a trusted local professional" : "Explore products from nearby shops"}</small></span><ArrowRight size={17} />
                </button>
                <button type="button" onClick={() => { setActiveGroup(activeGroup === "Booking" ? "Shop products" : "Booking"); setNotice(""); }}>
                  <span className="aa-nav-discovery-icon">{activeGroup === "Booking" ? <Package size={21} /> : <CalendarDays size={21} />}</span><span><strong>{activeGroup === "Booking" ? "Shop products" : "Book a service"}</strong><small>{activeGroup === "Booking" ? "Shop local beauty essentials" : "Choose a time that works for you"}</small></span><ArrowRight size={17} />
                </button>
              </div>
              <div className="aa-nav-preview-label">A FEW IDEAS NEAR YOU <span>Illustrative preview</span></div>
              <div className="aa-nav-synthetic-list">{(activeGroup === "Booking"
                ? ["Braids & styling · 18 services nearby", "Wellness · 12 services nearby", "Beauty essentials · 26 products nearby"]
                : ["Shea curl cream · Maison Naya · 9,500 XAF", "Satin bonnet · Atelier Sawa · 4,000 XAF", "Hibiscus hair oil · Bela Botanics · 6,500 XAF"]
              ).map((row, index) => <div className="aa-nav-synthetic-row" key={row}><span className="aa-nav-row-number">0{index + 1}</span><span>{row}</span><ChevronRight size={15} /></div>)}</div>
            </section>
          ) : (
            <section className="aa-nav-work-panel">
              <div className="aa-nav-panel-head"><div><h2>{activeId === "workspace" ? "Today at a glance" : page.title}</h2><span>Preview data · not connected to live activity</span></div><button type="button" className="aa-nav-date" onClick={() => setNotice("Date selection is not enabled in this navigation proposal.")}><CalendarDays size={15} />Tue, 18 Jun <ChevronDown size={14} /></button></div>
              <div className="aa-nav-summary-strip" aria-label="Illustrative preview counts">
                <div><span>{role === "Master" ? "MY BOOKINGS" : role === "Finance" ? "PENDING REVIEW" : role === "Country manager" ? "BUSINESSES" : role === "Platform admin" ? "COUNTRIES" : "TODAY'S BOOKINGS"}</span><strong>{role === "Master" ? "3" : role === "Finance" ? "2" : role === "Country manager" ? "128" : role === "Platform admin" ? "4" : "12"}</strong><small><ArrowUpRight size={13} /> Illustrative</small></div>
                <div><span>{role === "Finance" ? "PAYOUTS" : role === "Master" ? "NEXT APPOINTMENT" : role === "Country manager" ? "ORDERS TODAY" : role === "Platform admin" ? "ACTIVE BUSINESSES" : "ORDERS TO FULFIL"}</span><strong>{role === "Master" ? "10:30" : role === "Finance" ? "6" : role === "Country manager" ? "46" : role === "Platform admin" ? "2.4k" : "4"}</strong><small><ArrowDownLeft size={13} /> Preview only</small></div>
                <div><span>{role === "Master" ? "THIS WEEK" : role === "Finance" ? "SETTLED" : role === "Country manager" ? "IN THIS SCOPE" : role === "Platform admin" ? "SCOPE" : "TEAM ON SHIFT"}</span><strong>{role === "Master" ? "8 h" : role === "Finance" ? "1.8m" : role === "Country manager" ? "Cameroon" : role === "Platform admin" ? "Global" : "6"}</strong><small><span className="aa-nav-status-dot" />{role === "Platform admin" ? "All countries" : role === "Country manager" ? "Country only" : "Illustrative"}</small></div>
              </div>
              <div className="aa-nav-work-grid">
                <div className="aa-nav-list-card">
                  <div className="aa-nav-list-head"><div><h3>{activeId === "workspace" ? "Next appointments" : page.title}</h3><p>{role === "Master" ? "Assigned to you · personal scope" : role === "Branch staff" ? `${branch} branch · assigned access` : role === "Country manager" ? "Cameroon only · country scope" : "Selected workspace scope"}</p></div><button type="button" aria-label="Open all appointments" onClick={() => selectItem(activeId === "workspace" ? "appointments" : activeId)}><ArrowRight size={17} /></button></div>
                  <div className="aa-nav-work-rows">{page.rows.map((row, index) => <div className="aa-nav-work-row" key={row}><span className={`aa-nav-time ${index === 2 ? "is-pending" : ""}`}>{row.split(" · ")[0]}</span><span className="aa-nav-row-copy"><strong>{row.split(" · ").slice(1, 2).join("")}</strong><small>{row.split(" · ").slice(2).join(" · ")}</small></span>{index === 2 ? <span className="aa-nav-row-state is-pending">Pending</span> : <span className="aa-nav-row-state">Confirmed</span>}</div>)}</div>
                </div>
                <div className="aa-nav-quick-card">
                  <span className="aa-nav-quick-kicker"><FileText size={15} /> WORKSPACE NOTE</span>
                  <h3>{role === "Master" ? "Your schedule, at a glance." : role === "Finance" ? "A focused finance view." : role === "Country manager" ? "Country scope, made clear." : role === "Platform admin" ? "One platform. Clear boundaries." : role === "Branch staff" ? "Work for this branch." : "Everything in its place."}</h3>
                  <p>{role === "Master" ? "Only your assigned appointments and availability appear in this preview." : role === "Finance" ? "Earnings and payouts appear only when those capabilities are granted." : role === "Country manager" ? "Cameroon operations stay distinct from global platform controls." : role === "Platform admin" ? "Global administration is clearly separated from country operations." : role === "Branch staff" ? "Branch teams see only the work assigned to their location." : "Move between booking, product commerce and branch operations without losing context."}</p>
                  <button type="button" onClick={() => setNotice("This action is represented for review only; no data has been changed.")}>View workspace guide <ArrowRight size={15} /></button>
                </div>
              </div>
              <div className="aa-nav-native-actions"><div><span className="aa-nav-native-icon"><CalendarDays size={16} /></span><span><strong>{role === "Master" ? "Availability" : "Calendar"}</strong><small>Review scheduled work</small></span><button type="button" onClick={() => selectItem(role === "Master" ? "availability" : "appointments")} aria-label="Open calendar"><ArrowRight size={16} /></button></div>
                {role !== "Master" && role !== "Finance" && <div><span className="aa-nav-native-icon"><Package size={16} /></span><span><strong>{role === "Branch staff" ? "Branch orders" : "Product orders"}</strong><small>Follow up on fulfilment</small></span><button type="button" onClick={() => selectItem("orders")} aria-label="Open orders"><ArrowRight size={16} /></button></div>}
              </div>
            </section>
          )}

          <div className="aa-nav-scope-note"><ShieldCheck size={16} /><span><strong>Scope stays explicit.</strong> {role === "Country manager" ? "Country management is limited to Cameroon; global controls are not shown." : role === "Branch staff" ? "Branch access is limited to the selected assigned location." : role === "Master" ? "Masters see their own assigned appointments only." : role === "Finance" ? "Financial views depend on individually granted capabilities." : role === "Platform admin" ? "Platform-wide administration is separate from country-scoped access." : "Navigation visibility is illustrative and never grants permissions."}</span></div>
          {notice && <div className="aa-nav-toast" role="status">{notice}<button type="button" onClick={() => setNotice("")} aria-label="Dismiss message"><X size={15} /></button></div>}
        </main>
        <footer className="aa-nav-footer"><span>AgendaAlly · Stage 1 navigation proposal</span><span>Cameroon <span className="aa-nav-footer-sep">/</span> Illustrative fixture <span className="aa-nav-footer-sep">/</span> Not live data</span></footer>
      </section>
    </div>
  );
}
