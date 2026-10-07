// Document generation ONLY: stdout is a non-secret review manifest.
// No PHP evaluation, database access, credential generation, downloads or seeding.
import fs from "node:fs";
import path from "node:path";
import crypto from "node:crypto";
import { fileURLToPath } from "node:url";
import { literalArray } from "./reference-literal-parser.mjs";

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "../..");
const sourceRoot = ".local/agendaally-clean-repository/.migration-backup/backend";
const base = path.join(root, sourceRoot);
const hashes = {};
const sha = bytes => crypto.createHash("sha256").update(bytes).digest("hex");
function read(relative) {
  const filename = path.join(base, relative);
  if (fs.lstatSync(filename).isSymbolicLink()) throw new Error("Symlinked source refused");
  const bytes = fs.readFileSync(filename);
  hashes[relative] = sha(bytes);
  return bytes.toString("utf8");
}
function seeder(name) { return read(`database/seeders/${name}.php`); }
const core = seeder("DemoServiceCatalogSeeder");
const expansion = seeder("DemoExpansionSeeder");
const education = seeder("EducationTattooDemoSeeder");
const categoryExpansion = seeder("CategoryCatalogExpansionSeeder");
const productSource = seeder("ProductCatalogDemoSeeder");
const branches = literalArray(expansion, "private const BRANCHES =");
const beauty = literalArray(core, "private const SERVICES =");
const products = literalArray(productSource, "private const PRODUCTS =");
const tree = { ...literalArray(core, "private const CATEGORY_TREE ="),
  ...literalArray(categoryExpansion, "private const CATEGORY_TREE ="),
  Education: literalArray(education, "private const EDUCATION_CATEGORY_TREE ="),
  "Tattoo & Piercing": literalArray(education, "private const TATTOO_CATEGORY_TREE ="),
  "Car Service": [] };
const carSource = seeder("CarServiceTaxonomySeeder");
// The exact native Car Service leaf titles are read rather than guessed.
const carLeaves = literalArray(carSource, "private const CHILD_TITLES =");
tree["Car Service"] = carLeaves;
const icons = { ...literalArray(core, "private const CATEGORY_ICONS ="),
  ...literalArray(categoryExpansion, "private const CATEGORY_ICONS ="),
  Education: "/icons/categories/education.svg", "Tattoo & Piercing": "/icons/categories/tattoo-piercing.svg",
  "Car Service": "/icons/categories/car-service.svg" };
const rows = {};
const region = { id: 1, active: 1 };
rows.regions = [region];
rows.region_translations = [{ id: 1, region_id: 1, locale: "en", title: "Africa" }];
const countries = [
  [1, "Cameroon", "cm", 1], [2, "Burkina Faso", "bf", 2],
  [3, "Nigeria", "ng", 3], [4, "Ghana", "gh", 4]
];
rows.countries = countries.map(([id, , code, currency_id]) => ({ id, region_id: 1, active: 1, code, currency_id, img: null }));
rows.country_translations = countries.map(([id, title]) => ({ id, country_id: id, locale: "en", title }));
const cities = [
  [1, "Douala", 1], [2, "Yaoundé", 1], [3, "Ouagadougou", 2], [4, "Bobo-Dioulasso", 2],
  [5, "Lagos", 3], [6, "Accra", 4], [7, "Bafoussam", 1]
];
rows.cities = cities.map(([id, , country_id]) => ({ id, region_id: 1, country_id, active: 1 }));
rows.city_translations = cities.map(([id, title]) => ({ id, city_id: id, locale: "en", title }));
rows.areas = [{ id: 1, region_id: 1, country_id: 1, city_id: 1, active: 1 }];
rows.area_translations = [{ id: 1, area_id: 1, locale: "en", title: "Douala Demo Area" }];
rows.currencies = [
  { id: 1, title: "XAF", symbol: "FCFA", rate: 1, position: "after", default: 1, active: 1 },
  { id: 2, title: "XOF", symbol: "FCFA", rate: 1, position: "after", default: 0, active: 1 },
  { id: 3, title: "NGN", symbol: "₦", rate: 1550 / 600, position: "after", default: 0, active: 1 },
  { id: 4, title: "GHS", symbol: "GH₵", rate: 15 / 600, position: "after", default: 0, active: 1 }
];
for (const [id, title, symbol, rate] of [
  [5, "USD", "$", 1 / 600], [6, "EUR", "€", 0.92 / 600],
  [7, "CAD", "CA$", 1.37 / 600], [8, "GBP", "£", 0.79 / 600]
]) {
  rows.currencies.push({ id, title, symbol, rate, position: "after", default: 0, active: 1 });
}
rows.categories = [];
rows.category_translations = [];
const categoryByTitle = {};
for (const [title, children] of Object.entries(tree)) {
  const parent = rows.categories.length + 1;
  for (const [name, type, parent_id] of [[title, 11, 0], ...children.map(name => [name, 12, parent])]) {
    const id = rows.categories.length + 1;
    rows.categories.push({ id, type, parent_id, active: 1, status: "published", img: icons[title] });
    rows.category_translations.push({ id, category_id: id, locale: "en", title: name });
    if (categoryByTitle[name]) throw new Error("Ambiguous category title");
    categoryByTitle[name] = id;
  }
}
const retailTree = {
  "Beauty & Personal Care": {
    "Hair Care": ["Hair Care Essentials", "Combs & Brushes"],
    "Skin Care": ["Skincare Serums"],
    Makeup: ["Lipstick", "Foundation & Concealer"],
    "Bath & Body": ["Body Wash & Soap", "Body Lotion & Moisturizer"]
  },
  "Tailoring & Apparel Supplies": {
    Fabrics: ["Cotton & Linen Fabrics", "Embroidered & Lace Fabrics"],
    "Sewing Notions": ["Thread & Needles", "Buttons & Zippers"],
    "Ready-to-Wear Accessories": ["Headwraps & Scarves", "Belts & Bags"]
  }
};
function retailCategory(title, type, parent_id) {
  const id = rows.categories.length + 1;
  rows.categories.push({ id, type, parent_id, active: 1, status: "published", img: null });
  rows.category_translations.push({ id, category_id: id, locale: "en", title });
  // Typed titles: retail "Hair Care" is distinct from service "Hair Care".
  categoryByTitle[`retail:${title}`] = id;
  return id;
}
for (const [title, subtrees] of Object.entries(retailTree)) {
  const parent = retailCategory(title, 1, 0);
  for (const [subtitle, leaves] of Object.entries(subtrees)) {
    const subparent = retailCategory(subtitle, 2, parent);
    for (const leaf of leaves) retailCategory(leaf, 3, subparent);
  }
}
const shops = [
  { id: 501, user_id: 107, title: "Le Sawa Beauty Studio", description: "A modern beauty studio in the heart of Douala, offering hair, nail, and spa services for the whole family.",
    address: "12 Rue de la Joie, Bonanjo, Douala, Cameroon", latitude: 4.0511, longitude: 9.7679, country_id: 1, cities: [2, 1], masters: [112], staff: 114 },
  { id: 502, user_id: 113, title: "Ouaga Éclat Beauté", description: "A welcoming beauty salon in central Ouagadougou, offering hair, nail, and skin care for every occasion.",
    address: "Avenue Kwame Nkrumah, Ouagadougou, Burkina Faso", latitude: 12.3714277, longitude: -1.5196603, country_id: 2, cities: [3], masters: [116] },
  ...branches.map((branch, index) => ({
    ...branch.shop, id: 503 + index, user_id: branch.seller.id,
    country_id: branch.country ? ({ ng: 3, gh: 4 })[branch.country.iso2] : 1,
    cities: [({ Lagos: 5, Accra: 6, Douala: 1, "Yaoundé": 2, Bafoussam: 7 })[branch.city]],
    masters: [branch.master.id], staff: branch.staff.id
  })),
  { ...literalArray(education, "$this->shop($seller->id,"), id: 508, user_id: 132, country_id: 1, cities: [1, 7, 2], masters: [133, 134, 135, 136, 137] },
  { ...literalArray(education, "$this->shop($seller->id,", 1), id: 509, user_id: 138, country_id: 1, cities: [1, 2], masters: [139, 140] }
];
rows.shops = shops.map(shop => ({
  id: shop.id, user_id: shop.user_id, latitude: shop.latitude, longitude: shop.longitude, phone: null,
  open: 1, status: "approved", status_note: "approved", type: 1,
  delivery_time: { from: "10", to: "90", type: "minute" }, background_img: null, logo_img: null,
  slug: shop.title.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase()
    .replace(/[^a-z0-9]+/g, "-").replace(/^-|-$/g, "") + "-" + shop.id
}));
rows.shop_translations = shops.map((shop, index) => ({
  id: index + 1, shop_id: shop.id, locale: "en", title: shop.title,
  description: shop.description, address: shop.address
}));
rows.shop_locations = [];
for (const shop of shops) for (const city_id of shop.cities) for (const type of [1, 2]) {
  rows.shop_locations.push({ id: rows.shop_locations.length + 1, shop_id: shop.id,
    region_id: 1, country_id: shop.country_id, city_id, type,
    area_id: shop.id === 501 && city_id === 1 ? 1 : null });
}
const userDefinitions = [
  { id: 107, firstname: "sellers", lastname: "sellers" }, { id: 112, firstname: "Armand", lastname: "Fotso", gender: "male" },
  { id: 113, firstname: "sellers-bf", lastname: "sellers-bf" },
  { id: 114, firstname: "Branch", lastname: "Manager" },
  { id: 116, firstname: "Boureima", lastname: "Ouédraogo", gender: "male" },
  ...branches.flatMap(branch => [branch.seller, branch.master, branch.staff]),
  literalArray(education, "$this->user("),
  literalArray(education, "$this->user(", 1),
];
const masterMatches = [...education.matchAll(/\$this->masterFor\(\$shop,\s*\$(douala|bafoussam|yaounde),\s*/g)];
for (const match of masterMatches) userDefinitions.push(literalArray(education.slice(match.index), match[0]));
if (masterMatches.length !== 7) throw new Error("Expected seven branch-scoped education/tattoo specialists");
rows.users = userDefinitions.sort((a, b) => a.id - b.id).map(user => {
  const shop = shops.find(shop => shop.user_id === user.id || shop.masters.includes(user.id) || shop.staff === user.id);
  if (!shop) throw new Error("Missing demo-user shop relationship");
  return { id: user.id, firstname: user.firstname, lastname: user.lastname,
    email: `demo-${user.id}@agendaally.invalid`, phone: null, birthday: null,
    gender: user.gender ?? "male", password: null, email_verified_at: null, phone_verified_at: null,
    active: 1, img: null, lang: "en", currency_id: shop.country_id,
    remember_token: null, verify_token: null, firebase_token: null };
});
rows.model_has_roles = rows.users.map(user => ({
  role_id: shops.some(shop => shop.user_id === user.id) ? 11 : shops.some(shop => shop.staff === user.id) ? 14 : 22,
  model_type: "App\\Models\\User", model_id: user.id
}));
rows.invitations = [];
rows.invitation_shop_locations = [];
const invitationSource = read("app/Models/Invitation.php");
const acceptedStatus = invitationSource.match(/const\s+ACCEPTED\s*=\s*(\d+)\s*;/)?.[1];
if (!acceptedStatus) throw new Error("Native accepted invitation status must be an explicit integer");
const educationMasterCities = { 133: 1, 134: 1, 135: 7, 136: 2, 137: 2, 139: 1, 140: 2 };
for (const shop of shops) {
  for (const master of shop.masters) {
    const id = rows.invitations.length + 10001;
    rows.invitations.push({ id, shop_id: shop.id, user_id: master, role: "master", status: Number(acceptedStatus),
      created_by: shop.user_id, shop_role_id: null });
    // Explicitly bind all specialists, including the source's previously unscoped beauty specialists.
    const selectedCities = educationMasterCities[master] ? [educationMasterCities[master]] : shop.cities;
    for (const location of rows.shop_locations.filter(loc => loc.shop_id === shop.id && selectedCities.includes(loc.city_id))) {
      rows.invitation_shop_locations.push({ invitation_id: id, shop_location_id: location.id });
    }
  }
  if (shop.staff) {
    const id = rows.invitations.length + 10001;
    rows.invitations.push({ id, shop_id: shop.id, user_id: shop.staff, role: "shop_manager",
      status: Number(acceptedStatus), created_by: shop.user_id, shop_role_id: shop.id - 500 });
    for (const location of rows.shop_locations.filter(loc => loc.shop_id === shop.id && (shop.id !== 501 || loc.city_id === 1))) {
      rows.invitation_shop_locations.push({ invitation_id: id, shop_location_id: location.id });
    }
  }
}
rows.shop_roles = shops.filter(shop => shop.staff).map(shop => ({ id: shop.id - 500, shop_id: shop.id, name: "Branch Manager" }));
const branchKeys = ["bookings.view", "bookings.manage", "bookings.status", "bookings.availability",
  "orders.view", "orders.manage", "products.view", "products.manage", "marketing.view"];
const main = JSON.parse(fs.readFileSync(path.join(root, "docs/deployment/local-synthetic-reference-manifest.json"), "utf8"));
const shopPermissionIds = Object.fromEntries(main.rows.shop_permissions.map(row => [row.key, row.id]));
rows.shop_role_permissions = rows.shop_roles.flatMap(role => branchKeys.map(key => ({
  shop_role_id: role.id, shop_permission_id: shopPermissionIds[key]
})));
// Preserve the source's other shop-role labels without its broad/finance grants.
rows.shop_roles.push({ id: 10, shop_id: 501, name: "Moderator" },
  { id: 11, shop_id: 501, name: "Receptionist" }, { id: 12, shop_id: 501, name: "Cashier" });
for (const [shop_role_id, keys] of [
  [11, ["bookings.view", "bookings.manage", "bookings.status", "customers.view"]],
  [12, ["orders.view", "orders.manage", "products.view", "customers.view"]]
]) for (const key of keys) rows.shop_role_permissions.push({ shop_role_id, shop_permission_id: shopPermissionIds[key] });
rows.country_roles = countries.flatMap(([country_id], index) =>
  ["Country Manager", "Country Accountant", "Support/Customer Service"].map((name, n) => ({
    id: index * 3 + n + 1, country_id, name
  })));
rows.country_role_permissions = [];
rows.services = [];
rows.service_translations = [];
rows.service_masters = [];
const photosFile = "attached_assets/service-photos/sources.json";
const photosBytes = fs.readFileSync(path.join(root, photosFile));
const photos = JSON.parse(photosBytes);
const photoTargets = Object.fromEntries(photos.targets.map(row => [row.id, row]));
const referencedPhotos = new Set();
const educationServices = literalArray(education, "$services =");
const tattooServices = literalArray(education, "$services =", 1);
for (const shop of shops) {
  const definitions = shop.id === 508 ? educationServices : shop.id === 509 ? tattooServices : beauty;
  for (const definition of definitions) {
    const id = rows.services.length + 1;
    const target = photoTargets[id];
    if (!target || target.shop_id !== shop.id || target.title !== definition.category) throw new Error("Photo target identity mismatch");
    referencedPhotos.add(target.asset);
    const img = `/storage/portable-demo/services/${target.asset}.jpg`;
    rows.services.push({ id, shop_id: shop.id, category_id: categoryByTitle[definition.category],
      status: "accepted", price: definition.price, interval: definition.interval, pause: 10, img,
      type: "offline_in" });
    rows.service_translations.push({ id, service_id: id, locale: "en",
      title: definition.category, description: definition.description });
    for (const master_id of shop.masters) rows.service_masters.push({
      id: rows.service_masters.length + 1, service_id: id, shop_id: shop.id, master_id,
      active: 1, price: definition.price, interval: definition.interval, pause: 10, commission_fee: 0
    });
  }
}
const days = ["monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"];
rows.shop_working_days = shops.flatMap(shop => days.map(day => ({
  shop_id: shop.id, day, from: "09:00", to: "18:00", disabled: day === "sunday" ? 1 : 0
})));
rows.user_working_days = rows.users.filter(user => rows.model_has_roles.some(role => role.model_id === user.id && role.role_id === 22))
  .flatMap(user => days.map(day => ({ user_id: user.id, day, from: "09:00", to: "18:00", disabled: 0 })));
rows.galleries = rows.services.map((service, index) => ({ id: index + 1, loadable_type: "App\\Models\\Service",
  loadable_id: service.id, path: service.img, type: "services", title: path.basename(service.img) }));
const unitDefinitions = literalArray(seeder("UnitSeeder"), "$units =");
rows.units = unitDefinitions.map(({ id }) => ({ id, active: 1, position: "after" }));
rows.unit_translations = unitDefinitions.map(({ id, title }) => ({ id, unit_id: id, locale: "en", title }));
rows.subscriptions = literalArray(seeder("SubscriptionSeeder").replaceAll("now()", "null"), "$data =");
rows.shop_subscriptions = [];
rows.brands = [{ id: 1, title: "Ela De Pure", active: 1, img: null }];
rows.products = [];
rows.product_translations = [];
rows.stocks = [];
for (const shop of shops) {
  for (const [index, definition] of (shop.id === 501 ? products : [products[0]]).entries()) {
    const id = rows.products.length + 1;
    const img = `/storage/portable-demo/products/product-${shop.id === 501 ? index + 1 : 1}.jpg`;
    rows.products.push({ id, shop_id: shop.id, category_id: categoryByTitle[`retail:${definition.category}`],
      brand_id: 1, unit_id: 1, img, active: 1, visibility: 1, status: "published",
      min_qty: 1, max_qty: 20, tax: 0, min_price: definition.price,
      max_price: id === 1 ? definition.price * 1.5 : definition.price });
    rows.product_translations.push({ id, product_id: id, locale: "en",
      title: definition.title, description: definition.description });
    rows.stocks.push({ id, product_id: id, price: definition.price, quantity: definition.quantity,
      img, sku: shop.id === 501 ? null : `AAG-DEMO-SHOP-${shop.id}` });
    rows.galleries.push({ id: rows.galleries.length + 1, loadable_type: "App\\Models\\Product",
      loadable_id: id, path: img, type: "products", title: definition.title });
  }
}
rows.stocks.push({ id: 14, product_id: 1, price: products[0].price * 1.5, quantity: 20,
  img: rows.products[0].img, sku: "AGENDAALLY-DEMO-SERUM-100ML" });
rows.extra_groups = [{ id: 1, shop_id: 501, type: "text", active: 1 }];
rows.extra_group_translations = [{ id: 1, extra_group_id: 1, locale: "en", title: "Demo Volume" }];
rows.extra_values = [{ id: 1, extra_group_id: 1, value: "100 ml", active: 1 }];
rows.stock_extras = [{ id: 1, stock_id: 14, extra_group_id: 1, extra_value_id: 1 }];
for (const shop of rows.shops) {
  const shopProducts = rows.products.filter(product => product.shop_id === shop.id);
  shop.min_price = Math.min(...shopProducts.map(product => product.min_price));
  shop.max_price = Math.max(...shopProducts.map(product => product.max_price));
}
const settingsSource = seeder("SettingsSeeder");
const general = literalArray(settingsSource.replace(/\(string\)\s*/g, ""), "$requiredGeneralSettings =");
const settingValues = {
  ...literalArray(settingsSource.replace("date('Y')", "'2026'"), "$firstOrCreateItems ="),
  ...literalArray(settingsSource, "$defaultCountryItems ="), ...general,
  is_demo: "0", products_enabled: "1",
  description: "Discover local services, specialists and products from businesses in your community."
};
rows.settings = Object.entries(settingValues).map(([key, value], index) => ({ id: index + 1, key, value: String(value) }));
const content = seeder("DevelopmentPreviewContentSeeder");
const contentLiterals = content.replaceAll("Page::ABOUT_THREE", "'about_three'")
  .replaceAll("Page::ABOUT_SECOND", "'about_second'").replaceAll("Page::ABOUT", "'about'");
const about = literalArray(contentLiterals, "$pages =");
const faqs = literalArray(content, "$faqs =");
const articles = literalArray(content, "$articles =");
rows.pages = about.map((page, index) => ({ id: index + 1, type: page.type, active: 1, img: null, bg_img: null, buttons: [] }));
rows.page_translations = about.map((page, index) => ({ id: index + 1, page_id: index + 1,
  locale: "en", title: page.title, description: page.description }));
rows.faqs = faqs.map((faq, index) => ({ id: index + 1, uuid: faq.uuid, type: "development-preview", active: 1 }));
rows.faq_translations = faqs.map((faq, index) => ({ id: index + 1, faq_id: index + 1,
  locale: "en", question: faq.question, answer: faq.answer }));
rows.blogs = articles.map((article, index) => ({ id: index + 1, uuid: article.uuid, user_id: 1,
  type: 1, active: 1, published_at: "2026-10-06", img: null }));
rows.blog_translations = articles.map((article, index) => ({ id: index + 1, blog_id: index + 1,
  locale: "en", title: article.title, short_desc: article.short_desc, description: article.description }));
const policy = read("database/seeders/Support/LegalPolicyContent.php");
const termsDraft = content.match(/\$terms = <<<'HTML'\n([\s\S]*?)\nHTML;/)?.[1];
if (!termsDraft) throw new Error("Missing literal terms draft");
const policyHtml = [...policy.matchAll(/return <<<'HTML'\n([\s\S]*?)\nHTML;/g)].map(match => match[1]);
if (policyHtml.length !== 2) throw new Error("Missing financial/refund nowdoc bodies");
const policyLiterals = policy.replace("self::financialTerms()", JSON.stringify(policyHtml[0]));
const policyFind = literalArray(policyLiterals, "return str_replace(");
const policyReplace = literalArray(policyLiterals, "        ],");
if (policyFind.length !== policyReplace.length) throw new Error("Legal replacement arity mismatch");
let terms = termsDraft;
for (let n = 0; n < policyFind.length; n++) terms = terms.split(policyFind[n]).join(policyReplace[n]);
let privacy = content.match(/\$privacy = <<<'HTML'\n([\s\S]*?)\nHTML;/)?.[1];
if (!privacy) throw new Error("Missing literal privacy draft");
const privacySource = policy.slice(policy.indexOf("public static function privacy()"));
const privacyFind = literalArray(privacySource, "return str_replace(");
const privacyReplace = literalArray(privacySource, "        ],");
if (privacyFind.length !== privacyReplace.length) throw new Error("Privacy replacement arity mismatch");
for (let n = 0; n < privacyFind.length; n++) privacy = privacy.split(privacyFind[n]).join(privacyReplace[n]);
rows.term_conditions = [{ id: 1 }];
rows.term_condition_translations = [{ id: 1, term_condition_id: 1, locale: "en", title: "Terms of Service", description: terms }];
rows.privacy_policies = [{ id: 1 }];
rows.privacy_policy_translations = [{ id: 1, privacy_policy_id: 1, locale: "en", title: "Privacy Policy", description: privacy }];
rows.pages.push({ id: 4, type: "refund_cancellation", active: 1, img: null, bg_img: null, buttons: [] });
rows.page_translations.push({ id: 4, page_id: 4, locale: "en", title: "Refund & Cancellation Policy", description: policyHtml[1] });
const canonical = literalArray(read("resources/lang/translations.php"), "return");
const translationKeys = literalArray(seeder("DevelopmentTranslationSeeder"), "private const CLIENT_KEYS =");
const english = new Map();
for (const row of canonical.filter(row => row.locale === "en")) {
  const key = `${row.locale}|${row.group}|${row.key}`;
  if (!english.has(key)) english.set(key, { locale: row.locale, group: row.group, key: row.key, value: row.value, status: 1 });
}
for (const key of translationKeys) {
  const naturalKey = `en|web|${key}`;
  const value = english.get(naturalKey)?.value;
  if (typeof value !== "string" || value.trim() === "") throw new Error(`Missing canonical English web key: ${key}`);
}
rows.translations = [...english.values()].map((row, index) => ({ id: index + 1, ...row }));
rows.delivery_points = [{ id: 1, active: 1, region_id: 1, country_id: 1, city_id: 1, area_id: 1,
  price: 0, address: { en: "Douala, Cameroon (local development pickup fixture)" },
  location: { latitude: 4.0511, longitude: 9.7679 }, fitting_rooms: 0 }];
rows.delivery_point_translations = [{ id: 1, delivery_point_id: 1, locale: "en",
  title: "Le Sawa Beauty Studio — Douala demo pickup",
  description: "Free local pickup for the Cameroon demo shop. This fixture does not use live maps or external services." }];
rows.delivery_point_working_days = days.map(day => ({ delivery_point_id: 1, day, from: "09:00", to: "18:00", disabled: 0 }));
rows.delivery_prices = [];
for (const country of rows.countries) {
  const cityIds = shops.filter(shop => shop.country_id === country.id).flatMap(shop => shop.cities);
  for (const city_id of [null, ...new Set(cityIds)]) rows.delivery_prices.push({
    id: rows.delivery_prices.length + 1, price: 1200, region_id: 1, country_id: country.id,
    city_id, area_id: null, shop_id: null
  });
}
rows.delivery_prices.push({ id: rows.delivery_prices.length + 1, price: 1200, region_id: 1,
  country_id: 1, city_id: 1, area_id: 1, shop_id: null });
rows.delivery_price_translations = rows.delivery_prices.map(price => ({
  id: price.id, delivery_price_id: price.id, locale: "en", title: price.area_id ? "Local demo delivery" : "Standard delivery"
}));
for (const table of ["country_admins", "country_invitations", "country_payments", "model_has_permissions",
  "role_has_permissions", "wallets", "wallet_histories", "orders", "order_details", "transactions",
  "bookings", "payment_process", "payment_payloads", "platform_payment_configs", "email_settings", "email_templates"]) rows[table] = [];
for (const table of ["users", "shops", "categories", "products", "brands"]) for (const row of rows[table]) {
  const digest = sha(`agendaally-portable-demo|${table}|${row.id}`).slice(0, 32);
  row.uuid = `${digest.slice(0, 8)}-${digest.slice(8, 12)}-${digest.slice(12, 16)}-${digest.slice(16, 20)}-${digest.slice(20)}`;
  if (table === "users") row.my_referral = "D" + String(row.id).padStart(7, "0");
}
const brandMigrationPath = "database/migrations/2022_04_06_181958_create_brands_table.php";
const brandMigration = read(brandMigrationPath);
if (!/\$table->uuid\('uuid'\)->index\(\);/.test(brandMigration) ||
  !/\$table->string\('title'/.test(brandMigration)) {
  throw new Error("Native Brand identity schema contract changed; review required");
}
for (const name of ["DatabaseSeeder", "DevelopmentDemoSeeder", "UserSeeder", "DemoAfricaSeeder", "SettingsSeeder",
  "DemoStaffRolesSeeder", "DemoStaffInvitationSeeder", "DemoCountryInvitationSeeder",
  "DevelopmentGeographyPickupSeeder", "DevelopmentServicePhotosSeeder", "ShopWorkingDaysDemoSeeder",
  "DevelopmentPreviewContentSeeder", "ContentPagesSeeder", "LegalPoliciesSeeder", "BlogStorySeeder",
  "DevelopmentTranslationSeeder", "MissingTranslationsSeeder", "UnitSeeder", "ShopTagSeeder",
  "DevelopmentCurrencyCatalogSeeder", "SubscriptionSeeder", "OrderSeeder"]) seeder(name);
for (const relative of ["app/Observers/CountryObserver.php", "app/Observers/UserObserver.php",
  "app/Support/DefaultCountryRoles.php", "app/Services/CountryRoleService/CountryRoleService.php",
  "database/seeders/Support/CountryRoleDefaultsBackfiller.php", "app/Models/Category.php", "app/Models/Service.php",
  "app/Models/Shop.php", "app/Models/ShopLocation.php", "app/Models/Invitation.php",
  "app/Services/UserServices/UserService.php"]) read(relative);
const assets = [];
const webRoot = ".local/agendaally-clean-repository/.migration-backup/web/public";
for (const reference of Object.values(icons)) {
  const file = webRoot + reference;
  const bytes = fs.readFileSync(path.join(root, file));
  assets.push({ classification: "REQUIRED REFERENCE", file, target: reference, sha256: sha(bytes), bytes: bytes.length, status: "PRESENT_FROZEN_SOURCE" });
}
for (const asset of photos.assets.filter(asset => referencedPhotos.has(asset.key))) {
  const bytes = fs.readFileSync(path.join(root, asset.file));
  if (sha(bytes) !== asset.sha256 || bytes.length !== asset.bytes) throw new Error("Photo integrity mismatch");
  assets.push({ ...asset, classification: "APPROVED PORTABLE DEMO",
    target: `/storage/portable-demo/services/${asset.key}.jpg`, status: "PRESENT_WORKSPACE_NOT_IN_SANITIZED_CHECKOUT" });
}
const result = {
  status: "DRAFT_REQUIRES_OWNER_APPROVAL",
  authority: "Source/data review only. These classifications identify proposed content, not execution approval.",
  source_root: sourceRoot,
  source_hashes: hashes,
  schema_contracts: {
    brands: { source: brandMigrationPath, required_fields: ["uuid", "title"],
      uuid: "Native non-null UUID with no default; explicit deterministic value required.",
      note: "This bounded required-field check supplements frozen source hashes; not an executed native schema/FK proof." }
  },
  service_photo_source: { file: photosFile, sha256: sha(photosBytes), source_assets: photos.assets.length,
    selected_assets: referencedPhotos.size, targets: photos.targets.length },
  classifications: {
    "REQUIRED REFERENCE": ["Fixed native role/permission catalogs and English", "Africa, four countries, seven cities, explicit country-currency links",
      "15 service category roots and their 47 leaves, with local icons; two retail roots, seven intermediate groups and 13 leaves",
      "33 native units and translations; canonical English client translation rows",
      "Eight source-native currencies with synthetic rates; general branding/default-location/settings definitions"],
    "APPROVED PORTABLE DEMO": ["Nine named source demo Shops", "Nine seller profiles, fourteen Specialists and six branch staff profiles",
      "Twenty-six branch locations, twenty invitations and explicit branch pivots", "51 Services, 75 ServiceMaster assignments, working-day definitions",
      "Douala Demo Area, 11 delivery-price definitions, free local pickup and its seven working-day rows",
      "12 unassigned country role labels; nine Shop role labels with 62 explicit non-financial permission links",
      "13 Products/14 stock rows/13 product galleries, brand and one Volume variant",
      "Four subscription plan definitions without assignment/payment; CMS/legal review drafts and three blog drafts with separately approved author",
      "Reviewed local Service photographs; other source media candidates require local bytes"],
    "EXCLUDED RUNTIME/PRIVATE": ["Passwords and imported demo credentials; normal user contact data", "Demo Admin/Manager/finance/delivery accounts and Main Accountant invitation",
      "Automatic country permission pivots/all sentinel grants; payment/gateway/refund/payout staff grants", "Financial transactions, Orders, booking examples, cart/checkout state, payment attempts",
      "Wallets and Wallet histories (not only funded balances)", "Country gateway configurations, SMTP/provider credentials and revisions",
      "Subscriber assignments/payments, generated notifications, expiring Stories, reviews/likes/coupons, challenges/sessions/tokens",
      "Private uploads, receipt attachments, normal/staging datasets or keys"]
  },
  rows,
  counts: Object.fromEntries(Object.entries(rows).map(([table, data]) => [table, data.length])),
  deterministic_value_policy: {
    catalog_keys: "Explicit IDs here are proposed only for a new owned empty lab, never a repair/rekey of existing data.",
    uuid: "For demo User/Shop/Category/Product/Brand UUIDs: first 32 hex characters of SHA256('agendaally-portable-demo|' + table + '|' + id), formatted 8-4-4-4-12; exact values included; stable non-secret synthetic identity.",
    referral: "Demo User my_referral: 'D' + decimal user ID left-padded to seven digits. No existing-referral adoption.",
    timestamps: "Initial approved UTC epoch recorded privately once; retries preserve exact timestamps, all NULL/default fields and auto-increment values.",
    side_effects: "Proposed controlled raw inserts, not Eloquent/seeder calls. Deliberately no User points/audit/Wallet effects for non-login demo profiles. Separate native administrator observer proof remains required.",
    full_rows: "Rows list all proposed non-default fields. Remaining native columns retain their frozen schema NULL/defaults and must be captured in full native receipts before acceptance.",
    currency_rates: "Historical synthetic fixture ratios from DevelopmentCurrencyCatalogSeeder, not live FX. Exact native precision/serialization must be recorded from the frozen migrations; no payment/FX execution.",
    service_types: "Proposed all 51 offline_in/in-person; source's accidental defaults and online-capable education are owner-review differences, not silently accepted.",
    subscriptions: "Source plan timestamps proposed NULL, fixed definitions only; no Shop subscription or funded renewal. Validate native nullable timestamp contract before execution.",
    blogs: "All three draft blogs reference separately approved synthetic admin ID1. Insert only AFTER independent admin approval/proof; never create an author account just to satisfy the FK.",
    public_settings: "Exact source defaults with 2026 frozen copyright year, natural-key-resolved Cameroon/Yaoundé defaults, blank contact/social/store links. Source cancellation fee/timing defaults require owner approval, not production legal authority."
  },
  assets,
  remote_media_policy: {
    flag_images: "Proposed NULL country images until owner-approved vendored flag bytes/hashes exist; geography does not depend on flags.",
    shops_specialists: "Current source references Unsplash photos. Proposed temporary NULLs are disclosed, not claimed photo parity; local curated bytes/license/hash review required before a photo-complete portable baseline.",
    service_photos: `${referencedPhotos.size} selected local photos are workspace-present and hash checked, but not present under the sanitized repository. Approve packaging/license and source inclusion before initialization; no runtime download.`,
    asset_copy: "Future explicit copy only to fresh public demo namespace; verify byte hashes/MIME, symlink-free destination, no existing blob overwrite; one native gallery relationship per Service."
  },
  other_content_candidates: {
    categories: "Service roots/leaves above REQUIRED REFERENCE. Generic CategorySeeder inserts six placeholder type-label rows; EXCLUDED duplicate/non-idempotent placeholder scaffolding, not business categories.",
    products: { classification: "APPROVED PORTABLE DEMO", source_definitions: products.map(({ img, ...product }) => product),
      count_core_products: 5, count_core_stock_rows: 5, count_branch_clones: 8, count_retail_variant: 1,
      proposed_total_products: 13, proposed_total_stocks: 14, shop_ids: shops.map(shop => shop.id),
      relationship: "Five products on Shop501; clone first product/translation/stock on Shops502..509; one 100ml variant on first Shop501 product at 1.5x price, quantity20.",
      additional_category_titles: ["Beauty & Personal Care", "Tailoring & Apparel Supplies"],
      brand: "Ela De Pure, proposed ID1, active with NULL image pending local asset approval. No brand/payment/shop subscription implication.",
      source_asset_candidates: products.map((product, index) => ({ source_url: product.img,
        target: `/storage/portable-demo/products/product-${index + 1}.jpg`, bytes: null, sha256: null,
        status: "BLOCKED_LOCAL_BYTES_LICENSE_AND_OWNER_APPROVAL" })),
      status: "EXACT_DRAFT_ROWS_ABOVE; product gallery paths require independently approved local image bytes/hashes before inclusion." },
    cms: { classification: "APPROVED PORTABLE DEMO", status: "EXACT_DRAFT_ROWS_ABOVE_SEPARATE_AUTHOR_AND_LEGAL_APPROVAL",
      source_candidates: ["DevelopmentPreviewContentSeeder.php", "LegalPoliciesSeeder.php", "DevelopmentTranslationSeeder.php"],
      rule: "Three about pages, four FAQs, three blog drafts, Terms/Privacy and Refund/Cancellation page definitions are explicit above. Native draft disclaimers remain; no real legal/contact authority. Blog author dependency stays gated. No expiring Stories or host-year-dependent defaults.",
      source_asset_candidates: [...about, ...articles].map(item => ({ source_url: item.img,
        target: null, bytes: null, sha256: null, status: "CURRENT_PROPOSAL_NULL_IMAGE_OWNER_REVIEW_DIFFERENCE" })) }
  },
  order: [
    "Freeze source and owner-approved data/asset hashes; independent empty socket-only lab authority and secure custody approvals required.",
    "Run native ledger segments and reference role/permission checkpoints from main manifest with zero users/grants and zero geography DURING migrations only.",
    "Complete all 229 migrations; default-country backfill has no targets and creates no country permission grants. Templates stay empty without an approved provider.",
    "AFTER the complete ledger: raw explicit region/currency/country/city/area and translation inserts with reviewed natural-key/ID/FK equality checks. Never invoke CountryObserver or default-country-role backfiller.",
    "Create 12 exact country role labels without permission pivots or country members. Finance definitions remain unassigned.",
    "Create service categories/icons, then non-login demo profiles and explicit seller/master/shop_manager links, Shops/translations and branch locations.",
    "Create six Branch Manager roles with nine non-financial keys only, exact invitations/branch pivots, working days, Service/translation/ServiceMaster rows and approved local gallery assets.",
    "Create approved units/product categories/brand, Products/Stocks/Volume extra/galleries, plan definitions without assignment, delivery-price/pickup rows and working days, general settings, translations and non-blog CMS drafts.",
    "All local media bytes and source inclusion must be separately approved; preserve existing native FKs and no broad legacy/dev seeder execution.",
    "After independent synthetic Admin approval/proof only, insert the three explicitly authored blog drafts and their translations.",
    "Verify full native FKs/rows/negative absence/metadata and read-only second-invocation equality; separately provision approved initial administrator/key custody."
  ],
  side_effect_review: [
    "Country::create invokes CountryRoleService and can grant every current non-currency permission to Country Manager; after Finance definitions this includes Finance keys. Raw manifest insertion bypass is explicit and approval-gated.",
    "CountryRoleDefaultsBackfiller has the same all-permissions hazard; never rerun it after data creation.",
    "Legacy User/demo seeders generate credentials/UUIDs and call UserWalletService; broad DevelopmentDemoSeeder also creates Orders, paid Transactions, funded Wallet, finance ledger and notifications. Never invoke.",
    "DemoStaffRolesSeeder grants Moderator all shop keys and creates Main Accountant; exclude broad Moderator grant and accountant membership.",
    "Demo Expansion Branch Manager has legacy payments.view/payments.refunds.manage; this proposal explicitly removes those two keys while retaining six staff assignments.",
    "Source beauty Specialists were not location-pivot scoped; this proposal explicitly binds each to its intended branch pairs, with Armand on both Cameroon branches.",
    "Shop model location observers can update seller currency; explicit country/currency links avoid ambient observer writes. Future native evidence must verify final associations.",
    "Working-days seeder updates on retry despite a preservation comment; proposed initializer is verify-only on an existing manifest.",
    "External assets and workspace-only local photos do not prove portability; never fetch or borrow private uploads at bootstrap."
  ],
  acceptance: "NOT EXECUTED. Exact source-derived draft projections/counts are review data, not native proof. Missing portable asset packaging, proposed NULL media/type/scoping changes, source FX/policy choices and owner identity/key authorities remain explicit review blockers."
};
// Never serialize source credentials or original account contacts.
function reviewJson(value, depth = 0) {
  const indent = "  ".repeat(depth);
  if (Array.isArray(value)) {
    return value.length ? "[\n" + value.map(item => indent + "  " + JSON.stringify(item)).join(",\n") + "\n" + indent + "]" : "[]";
  }
  if (value && typeof value === "object") {
    return "{\n" + Object.entries(value).map(([key, item]) =>
      indent + "  " + JSON.stringify(key) + ": " + reviewJson(item, depth + 1)).join(",\n") + "\n" + indent + "}";
  }
  return JSON.stringify(value);
}
const serialized = reviewJson(result) + "\n";
if (serialized.includes("@githubit.com") || /\"password\"\s*:\s*\"/.test(serialized)) throw new Error("Source credential/contact leakage refused");
process.stdout.write(serialized);
