# Stage 2 native graduation — AgendaAlly for Business

The approved Stage 2 business landing-page direction is implemented in the
native `/for-business` route. The route uses the existing customer/business
header and footer, their native country context, and the existing
authentication-aware call to action: signed-in users continue to `/be-seller`;
signed-out visitors go to `/login`.

The former hotel-room reservation graphic is replaced by a clearly labeled
schematic seller Calendar day view. Its sample service appointments, times,
status labels, and disabled-time block are illustrative only; there are no
real customer, staff, booking, branch, or availability records in the public
page. Branch/shop scope is described separately and remains permission-bound.
No promotional generated media was needed.

The public page reuses the native marketplace's shared semantic pictogram grid,
populated from all pages of the real service-category API for the active
language, and links to the native service marketplace. API error and genuinely
empty-category states remain visible; categories are not presented as proof of
seller supply. Product discovery is linked to the
native product route only when its existing `products_enabled` setting is on;
settings lookup failure and disabled product discovery have visible status
messages. Business app destinations use the same seeded-placeholder suppression
and reserved-example labeling policy as the native footer. Unsupported partner
marks, success metrics, and the old rental illustration are no longer presented.

No admin Ant Design provider/theme token change was made. The public
`/for-business` scope does not require changing the globally shared admin
provider, and doing so would affect the accepted login/authentication surface
as well as user-selected admin themes. Existing dark mode, RTL, and admin
theme behavior therefore remain untouched.

No backend, database, authentication/authorization, country/shop/branch guard,
booking, commerce, accounting, payment, API/proxy, publishing, Maps, or
portable-setup behavior was changed. Build, server, and test commands were not
run as requested.