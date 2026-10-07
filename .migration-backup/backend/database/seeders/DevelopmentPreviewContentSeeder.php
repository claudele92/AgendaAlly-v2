<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Console\Commands\DevelopmentDatabaseGuard;
use App\Models\Blog;
use App\Models\Faq;
use App\Models\Language;
use App\Models\Page;
use App\Models\PrivacyPolicy;
use App\Models\Settings;
use App\Models\TermCondition;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * Safe, reproducible content for the original public preview routes.
 *
 * It never calls legacy content seeders: those seeders use broad table-empty
 * checks and BlogStorySeeder also writes expiring shop stories. Preview rows
 * here use the original CMS/settings contracts and never overwrite existing
 * owner content or populate contact, app-store, merchant, or provider details.
 */
class DevelopmentPreviewContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->assertOwnedDevelopmentDatabase();

        DB::transaction(function (): void {
            $this->ensureEnglishEditorialLocale();
            $this->seedFooterSettings();
            $this->call(LegalPoliciesSeeder::class);
            $this->seedAboutPages();
            $this->seedFaqs();
            $this->seedBlogs();
        });
    }

    /** Bounded legal-only update using the same reviewed local database guard. */
    public function runLegalPoliciesOnly(): void
    {
        $this->assertOwnedDevelopmentDatabase();
        $this->call(LegalPoliciesSeeder::class);
    }

    /** Explicit, guarded footer-only update; no translations or other CMS content. */
    public function runFooterOnly(): void
    {
        $this->assertOwnedDevelopmentDatabase();
        DB::transaction(function (): void {
            $this->seedFooterSettings(true);
            $official = [
                'instagram' => 'https://www.instagram.com/agendaally',
                'facebook' => 'https://www.facebook.com/AgendaAlly/',
                'linkedin' => 'https://www.linkedin.com/company/agendaally',
            ];
            $ownedDefaults = [
                'instagram' => ['instagram.com/agendaally'],
                'facebook' => ['facebook.com/agendaally'],
                'linkedin' => ['linkedin.com/company/agendaally'],
            ];
            foreach ($official as $key => $url) {
                $setting = Settings::query()->firstOrCreate(['key' => $key], ['value' => $url]);
                if (trim((string) $setting->value) === '' || in_array($setting->value, $ownedDefaults[$key], true)) {
                    $setting->update(['value' => $url]);
                }
            }
            Settings::query()->firstOrCreate(['key' => 'tiktok'], ['value' => '']);
        });
    }

    private function ensureEnglishEditorialLocale(): void
    {
        if (Language::query()->where('locale', 'en')->exists()) {
            return;
        }

        Language::query()->create([
            'locale' => 'en',
            'title' => 'English',
            // Never steal the default from an existing owner-selected locale.
            'default' => !Language::query()->where('default', true)->exists(),
            'active' => true,
            'backward' => false,
        ]);
    }

    private function seedFooterSettings(bool $footerOnly = false): void
    {
        $settings = [
            'description' => 'Discover local services, specialists and products from businesses in your community.',
            'footer_text' => '© ' . date('Y') . ' AgendaAlly. All rights reserved.',
        ];

        foreach ($settings as $key => $value) {
            $setting = Settings::query()->firstOrCreate(['key' => $key], ['value' => $value]);
            $legacyValues = $key === 'description'
                ? [
                    'Development preview with synthetic listings for exploring supported AgendaAlly service and product flows.',
                    'Discover trusted local businesses, book services, and shop products with AgendaAlly.',
                    'The smart marketplace for seamless appointment booking! 📅 Connect with top service providers in healthcare, beauty, and other professional services effortlessly. Streamline scheduling, enhance client satisfaction & grow your business!',
                ]
                : ['Development preview · Synthetic content only · Owner review required before production.'];

            // Replace only known bootstrap placeholders. An owner-edited value,
            // including an intentionally blank one, remains authoritative.
            if (in_array($setting->value, $legacyValues, true)) {
                $setting->update(['value' => $value]);
            } elseif (
                $key === 'footer_text'
                && $setting->value !== $value
                && preg_match('/^© \d{4} AgendaAlly\. All rights reserved\.$/', (string) $setting->value)
            ) {
                // This exact pattern is the owned copyright default; preserve
                // custom footer text while keeping the seeded year current.
                $setting->update(['value' => $value]);
            }
        }

        // These values were seeded as plausible-looking but unofficial
        // destinations/contact details. Clear only those exact owned defaults;
        // retain any URL/details an owner has since configured.
        $unofficialValues = [
            'instagram' => ['example.com/development-preview/social/instagram'],
            'facebook' => ['example.com/development-preview/social/facebook'],
            'twitter' => ['example.com/development-preview/social/twitter', 'twitter.com/agendaally'],
            'customer_app_ios' => ['https://apps.apple.com/app/agendaally/id0000000000'],
            'customer_app_android' => ['https://play.google.com/store/apps/details?id=com.agendaally.app'],
            'phone' => ['+237 677 123 456'],
            'address' => ['123 Avenue Kennedy, Bastos, Yaoundé, Cameroon'],
        ];

        foreach ($unofficialValues as $key => $values) {
            if ($footerOnly && !in_array($key, ['instagram', 'facebook', 'twitter'], true)) {
                continue;
            }
            $setting = Settings::query()->firstOrCreate(['key' => $key], ['value' => '']);
            if (in_array($setting->value, $values, true)) {
                $setting->update(['value' => '']);
            }
        }
    }

    /** Retained original drafts; legal alignment preserves their useful sections. */
    public static function legacyLegalDrafts(): array
    {
        // This restrained review note belongs inside each legal document, not
        // in the ordinary customer-facing footer.
        $terms = <<<'HTML'
<p><strong>Draft for AgendaAlly owner and qualified legal review before production.</strong></p>
<p>These Terms &amp; Conditions describe the intended rules for using AgendaAlly. They are an original development draft, not legal advice. Before production, the owner must confirm the operating entity, applicable jurisdictions, mandatory consumer protections, and contact and notice details.</p>

<h2>1. About AgendaAlly</h2>
<p>AgendaAlly provides online tools through which customers can discover independent businesses and specialists, request or make service bookings, and browse or order products where those features are available. The platform also provides business accounts and tools for presenting listings and managing marketplace activity.</p>

<h2>2. Acceptance and changes</h2>
<p>Use of an account or marketplace feature is subject to these Terms and any additional terms shown for that feature. The final production version must explain how acceptance is obtained and how changes take effect. Updated terms should be dated and made available before they apply, subject to applicable law.</p>

<h2>3. Eligibility and accounts</h2>
<p>Use the platform only if you are legally able to do so under the rules that apply to you. The minimum age, requirements for a parent or guardian, and any location-specific eligibility rules must be confirmed by the owner before production. Keep account information accurate, protect your sign-in credentials, and use only accounts you are authorized to access. Contact details and account recovery procedures are subject to the options the platform makes available.</p>

<h2>4. Customer accounts</h2>
<p>Customers may use available features to view listings, manage favorites or a cart, and submit booking or order requests. A request is subject to the status and confirmation shown in the platform and to any terms presented for that transaction. Customers should review the business, service or product, location, schedule, options, and displayed total before confirming.</p>

<h2>5. Business and vendor accounts</h2>
<p>Businesses are responsible for maintaining accurate profiles, service and product descriptions, prices, availability, locations, and other information they submit. A business must be authorized to offer the listed services or products and must comply with laws and obligations that apply to its own operations, staff, customers, and transactions.</p>

<h2>6. AgendaAlly’s marketplace role</h2>
<p>AgendaAlly is the technology marketplace connecting customers and independent businesses; a listing does not by itself make AgendaAlly the provider or seller. The business offering a service or product is responsible for its description, delivery or performance, availability, and communications, except where a transaction screen or separate written terms expressly state a different arrangement. The owner must confirm any marketplace, agency, merchant-of-record, or other legal characterization for each market before production.</p>

<h2>7. Listings and availability</h2>
<p>Listings, prices, schedules, stock, and business information are supplied or managed through platform accounts and may change. A displayed listing is not a guarantee that a business will accept a request or that an item remains available. Report information that appears inaccurate through a support channel designated by the owner.</p>

<h2>8. Service bookings</h2>
<p>Customers must review the selected service, specialist or business, location, date, time, and displayed charges before submitting a booking. The platform may show a booking as pending, accepted, cancelled, or another status supported by the booking workflow. The business is responsible for performing the booked service and communicating material changes, subject to the final production terms and applicable law.</p>

<h2>9. Product orders</h2>
<p>Customers should verify the product, selected options or variants, quantity, delivery or collection details where shown, and displayed total before submitting an order. Product listings and order status are managed through the platform’s commerce features. The seller is responsible for product descriptions, fulfilment, and applicable product obligations unless a clearly presented transaction arrangement states otherwise.</p>

<h2>10. Prices and payments</h2>
<p>Prices and any fees or taxes should be shown in the applicable booking or checkout flow before confirmation. Payment arrangements may vary by transaction: a supported flow may direct payment through the platform and a payment provider, while another may describe payment directly to a business. Follow the arrangement shown for the specific transaction. AgendaAlly does not promise that a particular payment method, provider, currency, or collection route is available in every location. The final production terms must identify the relevant payment roles and applicable charges without conflicting with checkout disclosures.</p>

<h2>11. Cancellations, changes, and refunds</h2>
<p>Cancellation, rescheduling, order-change, and refund rules may depend on the transaction, the policy presented with it, the business, and applicable law. Review the details shown before confirming. These Terms do not create a universal cancellation window or refund entitlement. The owner must publish transaction-specific policies and reconcile them with the actual platform and provider workflows before production.</p>

<h2>12. Responsibilities and prohibited conduct</h2>
<p>Users must not misuse accounts, interfere with platform security or operation, submit unlawful or misleading content, infringe another person’s rights, abuse other users, or use the platform to facilitate fraud or unlawful activity. Businesses must not list offerings they are not authorized to provide. Nothing in this draft limits rights that cannot lawfully be limited.</p>

<h2>13. Reviews and submitted content</h2>
<p>Where reviews or other user content are supported, submit only content you are entitled to share and that accurately reflects your experience. Do not post unlawful, deceptive, threatening, discriminatory, or private information about another person. The final production terms must specify the content license, moderation process, and any appeal or removal procedure used by the platform.</p>

<h2>14. Intellectual property and third-party services</h2>
<p>AgendaAlly, businesses, and other contributors retain rights in materials they own. Do not copy or use material without permission or another lawful basis. The platform may depend on third-party services, including payment providers; their own terms and privacy notices may apply when you use them.</p>

<h2>15. Availability and account action</h2>
<p>Features may change or be unavailable from time to time. The final policy must describe the circumstances and process for restricting, suspending, or closing an account, including notice and review where required. Nothing here authorizes withholding a remedy or notice required by applicable law.</p>

<h2>16. Disclaimers and liability</h2>
<p>The marketplace is provided subject to the terms and consumer protections applicable to the relevant user and transaction. No statement in this development draft is a warranty about uninterrupted availability, a business’s conduct, or a particular outcome. Any limitations, exclusions, or allocation of liability must be reviewed and tailored by qualified counsel; no limitation applies to the extent prohibited by law.</p>

<h2>17. Governing terms and contact</h2>
<p>The operating legal entity, applicable law, dispute process, official notice address, and support contact have not been supplied for this draft. The owner must complete and legally review those details, along with any required local disclosures, before publication. Do not infer them from a user’s selected country or city.</p>
HTML;

        $privacy = <<<'HTML'
<p><strong>Draft for AgendaAlly owner and qualified legal review before production.</strong></p>
<p>This original development draft is not legal advice or a complete production privacy notice. The owner must verify actual production data flows, responsible legal entity, purposes, recipients, retention, user rights, applicable jurisdictions, and contact details before publication. It makes no claim of compliance with a particular privacy law.</p>

<h2>1. Information described by the application</h2>
<p>The application has account records, business profiles and listings, service booking and product order records, and customer address and marketplace-location data in its underlying data model. Depending on the feature you use and the information you submit, records may include account and contact details, business or specialist profile information, selected service or product details, booking or order status, and transaction-related references or amounts. The exact production fields and their purpose must be confirmed before launch.</p>

<h2>2. Location and marketplace activity</h2>
<p>Country and city are used by marketplace features to organize or filter supported discovery and listings. A customer may also provide an address or location details for a feature that requests them. This draft does not claim that every browsing location choice is stored on the server or that precise device location is collected.</p>

<h2>3. How information is used</h2>
<p>Information submitted to the platform may be used to operate the feature requested, including account access, displaying or managing listings, handling booking and order workflows, maintaining transaction status, and providing platform administration. The final notice should identify each production purpose and any optional use, such as marketing, only if it is actually enabled and describe the choices available.</p>

<h2>4. Businesses and transaction participants</h2>
<p>Information necessary to handle a booking or order may be made available to the relevant business or transaction participants through the platform workflow. What information is visible to each participant depends on the feature, account permissions, and transaction. The owner must verify the exact fields and recipients before production.</p>

<h2>5. Payment services and other providers</h2>
<p>Where a payment flow uses a third-party provider, payment-related information may be exchanged with that provider as needed for the selected flow. The provider may handle information under its own terms and privacy notice. This draft does not claim that AgendaAlly stores full payment-card credentials, or describe provider retention or security practices. The owner must identify the providers actually enabled and document their roles and data exchanges.</p>

<h2>6. Communications and technical information</h2>
<p>The platform contains communication and notification features, but this draft does not establish which channels are enabled for a particular release or what delivery providers receive. Technical logs, device identifiers, cookies, and similar information are not described here because their production collection and use have not been established. The owner must audit those systems and add accurate details if applicable.</p>

<h2>7. Retention and security</h2>
<p>This draft does not set a retention period or make a security guarantee. Retention may depend on the record, feature, operational needs, and legal requirements. Before production, the owner must document actual retention and deletion practices and describe security measures accurately without implying that any system is risk-free.</p>

<h2>8. Choices and requests</h2>
<p>Available account controls may allow users to review or change some information. The process for access, correction, deletion, objection, portability, or other requests—and any applicable exceptions—must be confirmed for the production service and described with the correct contact channel. This draft does not promise a particular request outcome or response period.</p>

<h2>9. Children and international operation</h2>
<p>The minimum age for the service and any rules for minors have not been established in this draft and must be confirmed by the owner. The application supports country- and city-aware marketplace data, but this alone does not establish where the service operates, where data is hosted, or whether information crosses a border. Confirm the actual markets, hosting, and transfer practices before publication.</p>

<h2>10. Updates and contact</h2>
<p>The owner should date and publish material updates and explain when they take effect. The responsible entity and privacy contact have not been supplied; they must be provided and reviewed before production. Do not use this draft as a substitute for a complete, accurate privacy notice.</p>
HTML;

        return ['terms' => $terms, 'privacy' => $privacy];
    }

    private function seedAboutPages(): void
    {
        $pages = [
            [
                'type' => Page::ABOUT,
                'title' => 'About AgendaAlly',
                'img' => 'https://images.unsplash.com/photo-1739271933163-8dcc7c8e8a3e?auto=format&fit=crop&w=1200&h=600&q=80',
                'description' => '<p>AgendaAlly brings local expertise and independent businesses into one marketplace. Customers can explore service listings, make bookings, and browse products through the features available in their market.</p>'
                    . '<p>Businesses can use the platform to present offerings and manage supported marketplace activity. Clear listing details help customers decide what is right for them.</p>',
            ],
            [
                'type' => Page::ABOUT_SECOND,
                'title' => 'Book services and browse products',
                'img' => 'https://images.unsplash.com/photo-1594077810908-9ffd89d704ac?auto=format&fit=crop&w=1200&h=600&q=80',
                'description' => '<p>Find service information and booking options, or explore product listings and the available cart and order features. The details shown for a particular service, business, or product guide the next step.</p>'
                    . '<p>Availability, prices, fulfilment options, and transaction methods can differ by listing and market. Review the information shown for your selection before proceeding.</p>',
            ],
            [
                'type' => Page::ABOUT_THREE,
                'title' => 'Discover businesses by location',
                'img' => 'https://images.unsplash.com/photo-1687422808311-a776f467a468?auto=format&fit=crop&w=1200&h=600&q=80',
                'description' => '<p>AgendaAlly supports marketplace discovery organized around the countries, cities, businesses, and offerings available in the application. Choose a location to explore the listings presented for that market.</p>'
                    . '<p>What is available depends on the selected location and the information maintained by each business. A location selection does not guarantee that every listing or transaction is available there.</p>',
            ],
        ];

        foreach ($pages as $data) {
            $page = Page::query()->where('type', $data['type'])->first();
            if (!$page) {
                $page = Page::query()->create([
                    'type' => $data['type'],
                    'active' => true,
                    'img' => $data['img'],
                    'bg_img' => $data['img'],
                    'buttons' => [],
                ]);
                $page->translations()->create([
                    'locale' => 'en',
                    'title' => $data['title'],
                    'description' => $data['description'],
                ]);
                continue;
            }

            $translation = $page->translations()->where('locale', 'en')->first();
            if (!$translation) {
                $page->translations()->create([
                    'locale' => 'en',
                    'title' => $data['title'],
                    'description' => $data['description'],
                ]);
                continue;
            }

            $description = (string) $translation->description;
            if (
                str_contains($description, 'Development preview')
                || str_contains($description, 'real availability, secure payments')
                || str_contains($description, 'Secure payments, adapted to local currencies')
                || str_contains($description, 'AgendaAlly was built with Africa first in mind')
            ) {
                $translation->update([
                    'title' => $data['title'],
                    'description' => $data['description'],
                ]);
            }
        }
    }

    private function seedFaqs(): void
    {
        $faqs = [
            [
                'uuid' => 'bd4a92f2-00de-4ac3-877d-ae0a6b4a0011',
                'question' => 'What can I do with AgendaAlly?',
                'answer' => 'Explore available businesses and specialists, review service listings, and use the booking features offered for a listing. You can also browse product listings and use the cart and order features that are available in your market.',
            ],
            [
                'uuid' => 'bd4a92f2-00de-4ac3-877d-ae0a6b4a0012',
                'question' => 'How do I find businesses and services?',
                'answer' => 'Choose a supported country and city, then explore the businesses and categories available for that location. Review each listing’s details and displayed availability before choosing what works for you.',
            ],
            [
                'uuid' => 'bd4a92f2-00de-4ac3-877d-ae0a6b4a0013',
                'question' => 'How do service bookings work?',
                'answer' => 'Select a service and follow the booking steps shown for that business. Check the selected service, location, date, time, price, and booking status in the platform. Availability and booking arrangements can differ by business.',
            ],
            [
                'uuid' => 'bd4a92f2-00de-4ac3-877d-ae0a6b4a0014',
                'question' => 'How do product orders work?',
                'answer' => 'Review the product, available options, quantity, and displayed price, then follow the cart and checkout steps offered for that item. Delivery or collection options and payment arrangements depend on the listing and the transaction flow.',
            ],
        ];

        foreach ($faqs as $data) {
            $faq = Faq::query()->where('uuid', $data['uuid'])->first();
            if ($faq) {
                $translation = $faq->translations()->where('locale', 'en')->first();
                $oldEntries = [
                    'Are businesses, products, and appointments in this preview live?' => 'No. Seeded listings are synthetic development examples. Do not treat their availability, prices, contact details, or images as live offers, and do not submit real personal or payment information.',
                    'What does the country and city selection do?' => 'The original marketplace uses the selected country and city to scope supported discovery and listings. This preview contains synthetic geography and business data; no Google Maps service is required to use its seeded location choices.',
                    'Which cancellation or refund terms apply?' => 'This sample does not set a cancellation deadline, refund entitlement, or dispute outcome. It is not an operative policy. Refer to final owner-approved terms and any applicable booking or order details before using a production release.',
                    'How can a business present its services and products?' => 'The existing business workspace supports service and product catalog workflows, subject to account permissions. This preview uses synthetic records to demonstrate those native screens; it does not provide a live storefront or guarantee a transaction.',
                ];
                if (
                    $faq->type === 'development-preview'
                    && $translation
                    && ($oldEntries[$translation->question] ?? null) === $translation->answer
                ) {
                    $translation->update([
                        'question' => $data['question'],
                        'answer' => $data['answer'],
                    ]);
                }
                continue;
            }

            $faq = Faq::query()->create([
                'uuid' => $data['uuid'],
                'type' => 'development-preview',
                'active' => true,
            ]);
            $faq->translations()->create([
                'locale' => 'en',
                'question' => $data['question'],
                'answer' => $data['answer'],
            ]);
        }
    }

    private function seedBlogs(): void
    {
        $author = User::query()->where('email', 'admin@agendaally.test')->first();

        if (!$author) {
            throw new RuntimeException(
                'Preview articles need the synthetic admin account. Run the owned development seed before --content-only.'
            );
        }

        $articles = [
            [
                'uuid' => 'ccf7fda8-c323-4a24-90ce-ae5e00000011',
                'title' => 'A clearer way to explore local services',
                'short_desc' => 'Use the marketplace’s country, city, business, and service details to make an informed appointment choice.',
                'img' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=1200&h=600&q=80',
                'description' => '<p>Start with a country and city, then explore the businesses and services listed for that market. The native marketplace shows service and provider details that can help you compare what is offered before proceeding.</p>'
                    . '<h2>Check the details</h2><p>Review the listed service, location, displayed availability, and price before continuing. The contents of this development catalog are examples, not live appointments or verified recommendations.</p>'
                    . '<p>Beauty and wellness are part of the original catalog, alongside other service types represented by existing development data. A location choice does not require Maps to load seeded city listings.</p>',
            ],
            [
                'uuid' => 'ccf7fda8-c323-4a24-90ce-ae5e00000012',
                'title' => 'Browse products as well as appointments',
                'short_desc' => 'AgendaAlly’s original customer storefront includes product cards, stock variants, and a cart alongside service booking.',
                'img' => 'https://images.unsplash.com/photo-1560066984-138dadb4c035?auto=format&fit=crop&w=1200&h=600&q=80',
                'description' => '<p>Appointments are only one part of AgendaAlly’s native customer storefront. A customer can also explore product listings, review the options and prices attached to a product, and use the existing cart workflow.</p>'
                    . '<h2>Product details matter</h2><p>Check the selected item, variant, and displayed stock and price before adding a product to a cart. Product records in this development preview are synthetic; they do not represent a real retailer or a promise that stock is available.</p>',
            ],
            [
                'uuid' => 'ccf7fda8-c323-4a24-90ce-ae5e00000013',
                'title' => 'A business profile can introduce more than one service',
                'short_desc' => 'Explore the existing business tools for service details, staff, branches, and product catalogs.',
                'img' => 'https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=1200&h=600&q=80',
                'description' => '<p>The original AgendaAlly business application already contains workflows for managing a business profile, branches, service offerings, specialists or staff, and product catalogs. Which controls a person can use depends on their account permissions.</p>'
                    . '<h2>Keep customer details current</h2><p>Clear service information and accurate branch context help customers understand what is listed. The native application provides its own booking and catalog workflows; this article does not imply that development sample businesses are taking real appointments or orders.</p>',
            ],
        ];

        foreach ($articles as $data) {
            if (Blog::query()->where('uuid', $data['uuid'])->exists()) {
                continue;
            }

            $blog = Blog::query()->create([
                'uuid' => $data['uuid'],
                'user_id' => $author->id,
                'type' => Blog::TYPES['blog'],
                'active' => true,
                'published_at' => now()->toDateString(),
                'img' => $data['img'],
            ]);
            $blog->translations()->create([
                'locale' => 'en',
                'title' => $data['title'],
                'short_desc' => $data['short_desc'],
                'description' => $data['description'],
            ]);
        }
    }

    private function assertOwnedDevelopmentDatabase(): void
    {
        $basePath = base_path();
        $databaseConfiguration = (array) config('database.connections.sqlite', []);

        DevelopmentDatabaseGuard::requireOptIn(
            (string) config('app.env'),
            env('AGENDAALLY_DEVELOPMENT_DATABASE'),
            env('DEVELOPMENT_MODE')
        );

        $manifest = DevelopmentDatabaseGuard::reviewedManifest($basePath);
        $path = DevelopmentDatabaseGuard::resolveSqlitePath(
            $basePath,
            (string) config('database.default'),
            $databaseConfiguration
        );

        if (!DevelopmentDatabaseGuard::ownsDatabase($path, $basePath, $manifest)) {
            throw new RuntimeException(
                'Preview content seeding requires the owned development SQLite marker. '
                . 'Bootstrap an isolated development database before seeding.'
            );
        }

        $relativePath = str_replace('\\', '/', substr($path, strlen(rtrim($basePath, DIRECTORY_SEPARATOR)) + 1));
        $row = DB::table('agendaally_development_environment')->where('database_path', $relativePath)->sole();

        if (
            config('database.default') !== 'sqlite'
            || $row->environment !== 'local'
            || (int) $row->schema_version !== (int) $manifest['schema_version']
            || (int) $row->demo_seed_version < 0
            || (int) $row->demo_seed_version > (int) $manifest['demo_seed_version']
            || !hash_equals((string) $manifest['migration_set_sha256'], (string) $row->migration_set_sha256)
            || !Schema::hasTable('blogs')
        ) {
            throw new RuntimeException(
                'Refusing preview content seed: the selected SQLite database does not match the reviewed local ownership marker.'
            );
        }
    }
}