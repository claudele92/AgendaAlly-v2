# Stories UI integration

## Scope and files

Implements the approved Part C/D Stories UI proposal in the original Web and
Vendor portal only. No Laravel/backend, Calendar, payment, seed, media, or
accepted-data changes were made.

- Web homepage: `(store)/(booking)/(with-footer)/(home)/page.tsx`
- Web rail and viewer: `components/stories/stories.tsx`,
  `story-bubble-ui-1.tsx`, `stories-portal.tsx`, `sub-stories.tsx`,
  `story.tsx`, and `story-header.tsx`
- Vendor portal: `admin/src/views/seller-views/story/index.jsx` and
  `components/form/form.jsx`

The existing warm ivory, charcoal/neutral, and bronze Stage 2 language is
retained. Business updates remain secondary, between discovery paths and
service categories. Business cards use actual Story media and business identity
from the feed: 112 × 168px below 1024px, 168 × 266px at 1024px and above,
with horizontal overflow contained by the rail. Missing logos fall back to a
business initial, not substitute imagery. The viewer contains actual media,
offers only shop/product/service destinations when their identifiers exist,
closes on Escape, restores dialog focus through Headless UI, supports arrows
while open, and disables timed advance/transitions when reduced motion is
preferred.

An empty successful Web response keeps the rail absent. A failed homepage Story
request (`undefined`, from the existing server request catch) shows a distinct
unavailable message; no retry is offered because this request is not client
retryable. The lazy component's placeholder uses fixed-size, neutral cards.
The Vendor list retains its existing request/loading behavior, presents a
separate error with retry, and distinguishes an empty list. The Vendor form
reports detail-load errors with retry and submission errors inline. Vendor UI
does not claim status, expiry, views, or engagement.

## Existing API contracts retained

### Public Web

- Service: `web/services/story.ts`, `storyService.getAll({ lang })`
- Route: `GET v1/rest/stories/paginate`
- Response: `Story[][]`, already grouped by `shop_id`; each media entry uses
  `url`, `title`, `logo_img`, `shop_slug`, `model_type`, `model_uuid`,
  optional `model_title`, and the existing timestamps.
- Destination mapping is unchanged in meaning: shop to
  `/shops/{shop_slug}`, product to `/products/{model_uuid}`, and service to
  `/shops/{shop_slug}/booking?serviceId={model_uuid}`. Unknown/missing linked
  identities receive no invented destination.

### Authenticated Vendor portal

Existing service: `admin/src/services/seller/storeis.js`; requests use the
portal's existing authenticated Axios client.

- `GET dashboard/seller/stories` with pagination and `shop_id` query
  parameters; Redux consumes `{ data, meta }`.
- `GET dashboard/seller/stories/{id}`; edit initialization consumes
  `data.model_type`, `data.model`, and `data.file_urls`.
- `POST dashboard/seller/stories` and `PUT dashboard/seller/stories/{id}` with
  the existing form body: `{ model_type, model_id, file_urls: [imagePath] }`.
  The shop relation continues to use the current shop ID; no new ownership
  field or lifecycle field is sent.
- `DELETE dashboard/seller/stories/delete` with indexed `ids[0]`, `ids[1]`,
  etc. in query parameters.
- Existing image upload: `POST dashboard/seller/stories/upload` with
  `FormData` field `files[0]`; the existing returned path is retained in the
  one-entry `file_urls` array. No video support is implied.

The UI preserves the current dashboard seller routes and service wrappers.
List scope uses the authenticated portal's `myShop` ID. The audit identified
route-bound seller `show`/`update` ownership checks as a backend gap; UI changes
do not assert or replace server-side authorization. Backend must enforce the
current-shop gate for detail and update requests.

## Narrow source check

No app build, browser test, screenshot, workflow restart, external API call, or
database operation was run. A focused parser check passed: 7 modified Web TSX
files parsed with the TypeScript TSX parser and both modified Vendor JSX files
parsed with Babel; no syntax diagnostics were reported. This was not a semantic
typecheck or an app build. The main agent is responsible for the one final
affected-surface test.