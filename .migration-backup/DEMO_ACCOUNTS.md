# Demo accounts

Login credentials for every demo user seeded by `php artisan migrate:fresh --seed`. All emails end in `@githubit.com`; all countries/shops below are seeded by `DemoAfricaSeeder`, `UserSeeder`, and `DemoExpansionSeeder` (Part 7).

## Platform-wide roles

| Role | Email | Password |
|---|---|---|
| Regular user | user@githubit.com | user123 |
| Admin | owner@githubit.com | githubit |
| Manager | manager@githubit.com | manager |
| Moderator | moderator@githubit.com | moderator |
| Deliveryman | delivery@githubit.com | delivery |
| Main Accountant (country_role, platform-wide) | main-accountant@githubit.com | accountant |

## Cameroon — Douala + Yaoundé (multi-branch, shop 501, "Le Sawa Beauty Studio")

| Role | Email | Password |
|---|---|---|
| Seller | sellers@githubit.com | seller |
| Master | master@githubit.com | master |
| Staff (Branch Manager, both Douala locations) | branch-manager@githubit.com | branchmanager |

## Burkina Faso — Ouagadougou (single-branch, shop 502, "Ouaga Éclat Beauté")

| Role | Email | Password |
|---|---|---|
| Seller | sellers-bf@githubit.com | sellerbf |
| Master | master-bf@githubit.com | masterbf |

## Nigeria — Lagos (single-branch, shop 503, "Lagos Glow Studio")

| Role | Email | Password |
|---|---|---|
| Seller | sellers-ng@githubit.com | sellerng |
| Master | master-ng@githubit.com | masterng |
| Staff (Branch Manager) | branch-manager-ng@githubit.com | branchmanagerng |

## Ghana — Accra (single-branch, shop 504, "Accra Radiance Salon")

| Role | Email | Password |
|---|---|---|
| Seller | sellers-gh@githubit.com | sellergh |
| Master | master-gh@githubit.com | mastergh |
| Staff (Branch Manager) | branch-manager-gh@githubit.com | branchmanagergh |

## Cameroon — Douala (single-branch, shop 505, "Wouri Beauty Bar")

| Role | Email | Password |
|---|---|---|
| Seller | sellers-cm2@githubit.com | sellercm2 |
| Master | master-cm2@githubit.com | mastercm2 |
| Staff (Branch Manager) | branch-manager-cm2@githubit.com | branchmanagercm2 |

## Cameroon — Yaoundé (single-branch, shop 506, "Mfoundi Style House")

| Role | Email | Password |
|---|---|---|
| Seller | sellers-cm3@githubit.com | sellercm3 |
| Master | master-cm3@githubit.com | mastercm3 |
| Staff (Branch Manager) | branch-manager-cm3@githubit.com | branchmanagercm3 |

## Cameroon — Bafoussam (single-branch, shop 507, "Bafoussam Belle Époque")

| Role | Email | Password |
|---|---|---|
| Seller | sellers-cm4@githubit.com | sellercm4 |
| Master | master-cm4@githubit.com | mastercm4 |
| Staff (Branch Manager) | branch-manager-cm4@githubit.com | branchmanagercm4 |

## Notes

- Every seller above is single-branch except shop 501 (Cameroon), which intentionally spans both Douala and Yaoundé — the platform's original demo shop, kept as-is.
- Cameroon now has 4 sellers total (shops 501, 505, 506, 507) across its 3 seeded cities (Douala, Yaoundé, Bafoussam), per Part 7 of the September demo-data expansion.
- No shop/master photo URLs are set for the 5 new shops added in Part 7 (Lagos, Accra, and the 3 new Cameroon branches) — this sandbox can't verify a working photo URL against unsplash.com (blocked by its own egress proxy), and an unverified or invented URL would be worse than an honest empty one. `ImageWithFallBack` (see PR #74) covers the storefront rendering of a missing image the same way it already does elsewhere.
- All 5 new shops (Part 7) offer the same 6-service catalog as shops 501/502 (Haircut, Hair Coloring, Manicure, Massage Therapy, Beard Trim, Bridal Makeup) — chosen for consistency and lower risk over inventing a new catalog per shop.
