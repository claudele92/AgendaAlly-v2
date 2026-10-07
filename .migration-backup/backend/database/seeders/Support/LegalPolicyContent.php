<?php
declare(strict_types=1);

namespace Database\Seeders\Support;

use Database\Seeders\DevelopmentPreviewContentSeeder;

/** Product-policy drafts, not legal certification or runtime financial authority. */
final class LegalPolicyContent
{
    public static function terms(): string
    {
        $original = DevelopmentPreviewContentSeeder::legacyLegalDrafts()['terms'];
        return str_replace([
            'and browse or order products where those features are available.',
            'Customers may use available features to view listings, manage favorites or a cart, and submit booking or order requests.',
            '<h2>6. AgendaAlly’s marketplace role</h2>',
            '<h2>9. Product orders</h2>',
            '<p>Customers should verify the product, selected options or variants, quantity, delivery or collection details where shown, and displayed total before submitting an order. Product listings and order status are managed through the platform’s commerce features. The seller is responsible for product descriptions, fulfilment, and applicable product obligations unless a clearly presented transaction arrangement states otherwise.</p>',
            '<h2>11. Cancellations, changes, and refunds</h2>',
            'The owner must publish transaction-specific policies and reconcile them with the actual platform and provider workflows before production.',
        ], [
            'and browse product information. The current MVP is service-booking-first; product purchasing and additional payment integrations are not enabled by these Terms.',
            'Customers may use available features to view listings, manage favorites, and submit service-booking requests.',
            '<p>Specialists and staff provide services through the relevant Vendor/Shop relationship. Their compensation is Shop-funded and Shop-controlled under that relationship; AgendaAlly is not the payer of Specialist compensation. A platform Vendor payout workflow does not create a Specialist payout entitlement.</p>' . "\n\n<h2>6. AgendaAlly’s marketplace role</h2>",
            '<h2>9. Product information and deferred purchasing</h2>',
            '<p>Product information may be browsed where available. Product purchasing, delivery and multi-Shop checkout are outside the current accepted MVP. A listing or legacy order record is not a promise of an available purchasing or fulfilment service. Any later release of those features requires its own supported transaction terms; these Terms do not activate them.</p>',
            self::financialTerms() . "\n\n<h2>11. Cancellations, changes, and refunds</h2>",
            'See the separate <a href="/refund-cancellation">Refund &amp; Cancellation Policy</a> for the supported cancellation, eligibility, request, review and completion distinctions. That policy does not create new calculation rules or override the authoritative amount shown for an eligible request.',
        ], $original);
    }

    private static function financialTerms(): string
    {
        return <<<'HTML'
<h3>Payment selection, collection and booking status</h3>
<p>Choosing a payment method is not confirmation that payment has been collected. Selecting Cash does not establish that Cash was paid or received. Booking state and financial state are separate: a cancelled booking is not, by itself, a completed refund.</p>
<h3>Refunds, Vendor payouts and unresolved operations</h3>
<p>A refund request is a request for review, not returned money. Refund approval does not mean money has moved; Vendor payout approval does not mean the Vendor has been paid. Where controlled manual financial execution is available, an authorized operator records the supported completion evidence after external execution. Only authoritative financial completion may be shown as a completed refund or payout. An operation requiring review or reconciliation remains unresolved, not completed; approval, an operator note or an uncertain response must not be treated as a new payment authorization.</p>
<p>Platform payout workflows concern eligible original obligations to Vendors, not Specialist compensation or an arbitrary account balance. These Terms do not promise automatic provider refunds, automatic payouts, arbitrary partial refunds, or completion within a specified period.</p>
<h3>Wallet and custody boundaries</h3>
<p>Legitimately funded Wallet transactions use the supported internal Wallet accounting and return mechanisms where applicable. Wallet entries do not establish external bank or provider custody. AgendaAlly does not offer Wallet-to-cash redemption. Where a separately supported transaction is paid directly to a Vendor, AgendaAlly must not be treated as holding money it did not collect. Vendor-direct collection and execution must be available and authorized for that transaction; these Terms do not make a disabled route available.</p>
HTML;
    }

    public static function privacy(): string
    {
        $original = DevelopmentPreviewContentSeeder::legacyLegalDrafts()['privacy'];
        return str_replace([
            'The exact production fields and their purpose must be confirmed before launch.',
            '<h2>2. Location and marketplace activity</h2>',
            'Information submitted to the platform may be used to operate the feature requested, including account access, displaying or managing listings, handling booking and order workflows, maintaining transaction status, and providing platform administration.',
            'What information is visible to each participant depends on the feature, account permissions, and transaction. The owner must verify the exact fields and recipients before production.',
            '<p>The platform contains communication and notification features, but this draft does not establish which channels are enabled for a particular release or what delivery providers receive. Technical logs, device identifiers, cookies, and similar information are not described here because their production collection and use have not been established. The owner must audit those systems and add accurate details if applicable.</p>',
            'Before production, the owner must document actual retention and deletion practices and describe security measures accurately without implying that any system is risk-free.',
        ], [
            'Information is limited by the features actually used. Product and provider records present in the data model do not mean a deferred transaction feature is active.',
            '<p>Supported financial records include payment method and collection status, transaction amounts/currencies/references, Wallet movements, cancellation and refund requests, and eligible Vendor payout workflow records. Financial recording may include operator actions, review reasons, completion references, receipt files and associated file/type/size/integrity and access metadata. Receipt files can contain personal information; do not submit unrelated personal information as financial evidence.</p>' . "\n\n<h2>2. Location and marketplace activity</h2>",
            'Information submitted to the platform is used to operate supported requested features: account access and recovery, profiles and listings, service bookings, transaction and Wallet status, cancellation/refund and Vendor payout review and recording, authorized administration, security and audit. Financial evidence supports review, reconciliation and completion recording; it does not prove that a provider integration is enabled.',
            'What information is visible depends on the feature, ownership, account permissions and transaction. Customers and Vendors receive their own supported status and transaction projections; private financial evidence is reserved for authorized financial access, rather than published with those projections. Authorized staff may see booking information needed for their assigned work. The owner must confirm production recipients and any disclosure outside these supported roles before publication.',
            '<p>Account verification and password-reset features process account contact details, recovery/verification challenges, delivery status and security records. Selected account email delivery is supported where explicitly enabled; a notification record is not a guarantee that email, SMS or push was delivered. Configured delivery services may receive the recipient and message information needed for permitted sends; actual production processors must be identified before launch.</p><p>The Customer web application uses cookies and browser storage for sign-in, preferences and supported saved request/retry information. Application requests and administration can generate technical and security logs, request timing/status, network information and audit records. These records support operation, troubleshooting and abuse prevention. This draft does not assert that optional analytics, marketing, precise device location or every device identifier is enabled. Production cookie/analytics choices and processor details require review.</p>',
            'Access controls restrict supported account and financial information, including private receipt access, by ownership and permissions. No system is risk-free. Financial, completion-evidence and audit records have integrity/retention controls; closing an account is not a guarantee that every related transaction or audit record is erased. Before production, the owner must document actual retention, deletion exceptions and security practices. This draft promises no exact retention period, universal deletion, encryption coverage or security certification.',
        ], $original);
    }

    public static function refund(): string
    {
        return <<<'HTML'
<p><strong>Draft for AgendaAlly owner and qualified legal review before production.</strong></p>
<p>This policy describes the supported service-booking and financial model. It is not legal advice, a guarantee of a refund or a replacement for mandatory rights that apply to a transaction. The operating entity, applicable law and official contact details require owner/legal review. Read it with the <a href="/terms">Terms of Service</a> and <a href="/privacy">Privacy Policy</a>.</p>
<h2>1. Cancelling a booking</h2>
<p>Use the cancellation option made available for your own booking. The platform checks the booking's current status, your authority and the supported cancellation rules; not every booking or state can be cancelled. Cancellation changes the booking lifecycle. It does not, by itself, mean that money has been returned. Cancelling a financial request is also different from cancelling a booking.</p>
<h2>2. Cancellation settings and fees</h2>
<p>The supported booking financial rules use configured platform cancellation/refund timing and fee settings in relation to the booking start. They are not a universal number of hours, fixed fee or percentage stated by this document, and this policy does not represent those settings as Shop-editable. The application evaluates the applicable rule and original transaction records. Check the details available for your booking and contact the relevant Shop or the platform's available <a href="/contact">Contact page</a> if a rule is unclear; do not infer a particular fee or refund from an example.</p>
<h2>3. Refund eligibility and amount</h2>
<p>An eligible financial return requires the supported original payment and booking records, not merely a selected payment method or a cancelled status. The application determines the eligible amount using the applicable cancellation rule, verified original collection, currency, amounts already returned and amounts still held for unresolved refund requests. An unavailable or unverified payment source, no remaining eligible amount, or an unresolved competing reservation cannot be treated as new refund entitlement.</p>
<p>For a supported manual refund request, the server-authoritative eligible amount governs. You cannot choose an arbitrary partial amount. An earlier request or approval does not create a second entitlement; repeating or replaying the same request does not increase the amount available. A valid held request continues to count while it is unresolved. This policy does not change the application's configured calculation or retained authority.</p>
<h2>4. Request, review and approval</h2>
<p>Where enabled for your account and original transaction, submit a request using the available refund-request page and its supported details. A requested refund has not been paid. Authorized review may approve or reject the request under the supported eligibility and evidence rules. Approval is not financial completion and does not mean money has moved. Cancelling a request before execution may release its hold where the application permits it; it does not itself return money.</p>
<h2>5. Execution, completion and unresolved review</h2>
<p>Where supported, external refund execution is controlled/manual: an authorized operator assumes execution responsibility and records authoritative completion evidence. The platform does not promise automatic provider-driven refunds. Only a recorded authoritative financial completion may be described as a completed refund. A completion reference identifies the recorded operation; the request or approval alone is not a receipt.</p>
<p>If an operation requires review or reconciliation, it remains unresolved rather than refunded. Do not treat an uncertain response as permission to repeat external payment or submit a new request for the same held amount. Use the existing request/status and supported retry or review path. No refund processing-day promise or completion deadline is created by this policy.</p>
<h2>6. Cash and Wallet</h2>
<p>Selecting Cash does not prove that Cash was collected. The manual electronic-refund path does not verify Cash receipts or automatically repay Cash. Where Cash was actually paid to the Shop, raise the payment and service issue with that Shop using the available contact channels; these words do not create new Cash custody or repayment obligations for AgendaAlly.</p>
<p>Legitimately funded Wallet transactions retain the supported internal Wallet return/accounting rules where applicable. A Wallet return is not an external cash refund. Wallet funds are not eligible for Wallet-to-cash redemption through the manual refund workflow, and this policy does not offer that capability.</p>
<h2>7. Platform collection and Vendor-direct transactions</h2>
<p>A supported manual electronic refund requires verified original collected funds and the supported original collection records. A platform-collected transaction must not be confused with a transaction paid directly to a Vendor. A Vendor-direct transaction is included only when its collection route and execution mandate are supported and authorized; AgendaAlly is not described as taking custody of funds it did not collect. Disabled providers or routes do not become available through this policy.</p>
<h2>8. Scope and questions</h2>
<p>This policy is for the current service-booking-first MVP. Product purchasing and additional payment integrations remain outside that accepted scope. It creates no automatic refund, arbitrary partial refund, new cancellation penalty or exemption, bank-transfer promise, or Specialist payout entitlement. Unclear legal rights, disclosures and disputes must be referred for owner/legal review; this draft does not select a jurisdiction or dispute rule.</p>
HTML;
    }
}
