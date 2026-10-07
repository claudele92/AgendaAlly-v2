<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\PrivacyPolicy;
use App\Models\TermCondition;
use Illuminate\Database\Seeder;

class ContentPagesSeeder extends Seeder
{
    /**
     * Terms & Conditions, Privacy Policy, and the "About Us" pages had no
     * seeder at all - a fresh install had zero rows for any of them, so
     * every footer link to /terms and /privacy 404ed (rendering as Next's
     * generic "page doesn't exist" screen on a page that isn't actually
     * missing, just empty) and /about rendered blank. Real, readable
     * placeholder copy pending an actual legal review of terms/privacy.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call(LegalPoliciesSeeder::class);

        foreach ($this->aboutPages() as $data) {
            if (Page::where('type', $data['type'])->exists()) {
                continue;
            }

            $page = Page::create([
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
        }
    }

    private function aboutPages(): array
    {
        return [
            [
                'type' => Page::ABOUT,
                // Unsplash - already on next.config.js's remotePatterns
                // allowlist. Purpose-fitting photos (booking on a phone;
                // using a laptop for business; a small shop owner),
                // deliberately sourced from Africa-based photographers/
                // libraries (Ninthgrid/Lagos, Oluwatobi Fasipe/Lagos, Ali
                // Mkumbwa/Dar es Salaam) to match the platform's own
                // Africa-first positioning - the original picks here were
                // generic stock that happened to feature white subjects,
                // inconsistent with the About Us copy above.
                'img' => 'https://images.unsplash.com/photo-1739271933163-8dcc7c8e8a3e?auto=format&fit=crop&w=1200&h=600&q=80',
                'title' => 'About AgendaAlly',
                'description' => "<p>In much of Africa, booking an appointment still means phone calls, guesswork, and hoping someone picks up. Many talented businesses and professionals don't have the online presence they need to be found, manage their schedules, or grow beyond word of mouth.</p><p>AgendaAlly is a marketplace booking platform built to close that gap. We give businesses and independent professionals an easy way to build a professional online presence - no website required - and give customers a simple, reliable way to find them and book, with real availability, secure payments, and everything in between.</p>",
            ],
            [
                'type' => Page::ABOUT_SECOND,
                'img' => 'https://images.unsplash.com/photo-1594077810908-9ffd89d704ac?auto=format&fit=crop&w=1200&h=600&q=80',
                'title' => 'Built to work for everyone',
                'description' => "<p>AgendaAlly is designed to be genuinely easy to use, whatever your comfort level with technology - a booking platform should remove friction, not add it.</p><p>Customizable profiles let any business build a professional presence in minutes. Efficient booking means customers see real availability and book in a few taps, no back-and-forth. Secure payments, adapted to local currencies and methods, mean businesses get paid the way that works for them. Automated reminders and notifications cut down on missed appointments for everyone. Analytics give businesses real insight into booking patterns and customer behavior. And beyond appointments, sellers can list products and schedule deliveries - all from the same platform.</p>",
            ],
            [
                'type' => Page::ABOUT_THREE,
                'img' => 'https://images.unsplash.com/photo-1687422808311-a776f467a468?auto=format&fit=crop&w=1200&h=600&q=80',
                'title' => 'Built for Africa, ready for the world',
                'description' => "<p>AgendaAlly was built with Africa first in mind - starting with Cameroon, Ivory Coast, Nigeria, Burkina Faso, Ghana, Senegal, and Kenya - regions where accessible, affordable digital tools for booking and scheduling are still hard to come by. Every part of the platform, from its interface to its payment options, is designed to work here first.</p><p>At the same time, the problems AgendaAlly solves aren't unique to one region. Anywhere a business needs a simple way to manage bookings and reach customers online, AgendaAlly is built to help - which is why the platform is designed for a global audience from day one.</p>",
            ],
        ];
    }

    /** Historical placeholder retained only for exact managed-default recognition. */
    public static function legacyTermsHtml(): string
    {
        return <<<'HTML'
<p>These Terms & Conditions ("Terms") govern your use of AgendaAlly and the booking services we provide. By creating an account or making a booking through our platform, you agree to these Terms.</p>

<h2>Using AgendaAlly</h2>
<p>AgendaAlly connects customers with independent beauty and wellness professionals ("masters") and salons ("shops"). We provide the booking platform; the services themselves are performed by the master or shop you book with, who are responsible for the quality and safety of the service provided.</p>

<h2>Bookings & Payments</h2>
<p>When you book a service, you agree to pay the price shown at checkout, plus any applicable service fees. Payment can be made online or, where offered, directly at the salon. Prices, availability, and service details are set by each individual master or shop.</p>

<h2>Cancellations & Rescheduling</h2>
<p>Each booking is subject to the cancellation and rescheduling policy shown on that booking's details page. Cancelling or rescheduling after the stated deadline may incur a fee, which is disclosed to you before you confirm your booking.</p>

<h2>Your Account</h2>
<p>You are responsible for keeping your account credentials secure and for all activity that occurs under your account. Please notify us promptly if you suspect unauthorized use of your account.</p>

<h2>Conduct</h2>
<p>We expect all customers, masters, and shops to treat each other with respect. We may suspend or remove accounts that violate these Terms, engage in fraud, or behave abusively toward other users.</p>

<h2>Changes to These Terms</h2>
<p>We may update these Terms from time to time. Continued use of AgendaAlly after an update means you accept the revised Terms.</p>

<h2>Contact Us</h2>
<p>Questions about these Terms? Reach out through our <a href="/contact">Contact page</a>.</p>

<p><em>Note: this is placeholder content pending a full legal review, not final legal terms.</em></p>
HTML;
    }

    /** Historical placeholder retained only for exact managed-default recognition. */
    public static function legacyPrivacyHtml(): string
    {
        return <<<'HTML'
<p>This Privacy Policy explains what information AgendaAlly collects, how we use it, and the choices you have.</p>

<h2>Information We Collect</h2>
<p>We collect information you provide directly, such as your name, email address, phone number, and payment details, as well as information generated by using the service, like your booking history and location (when you allow it, to show nearby salons and masters).</p>

<h2>How We Use Your Information</h2>
<p>We use your information to create and manage your bookings, process payments, send booking confirmations and reminders, provide customer support, and improve our platform. We may also use it to send you offers or updates, which you can opt out of at any time.</p>

<h2>Sharing Your Information</h2>
<p>We share the details needed to fulfil a booking (such as your name and contact information) with the master or shop you book with. We also work with payment processors to handle transactions securely. We do not sell your personal information to third parties.</p>

<h2>Cookies</h2>
<p>We use cookies to keep you signed in, remember your preferences (like language and currency), and understand how our platform is used so we can improve it.</p>

<h2>Your Rights</h2>
<p>You can access, update, or delete your account information at any time from your profile settings, or by contacting us. You may also request a copy of the data we hold about you.</p>

<h2>Data Retention</h2>
<p>We keep your information for as long as your account is active, or as needed to provide our services and meet legal obligations.</p>

<h2>Contact Us</h2>
<p>Questions about this Privacy Policy? Reach out through our <a href="/contact">Contact page</a>.</p>

<p><em>Note: this is placeholder content pending a full legal review, not a final privacy policy.</em></p>
HTML;
    }
}
