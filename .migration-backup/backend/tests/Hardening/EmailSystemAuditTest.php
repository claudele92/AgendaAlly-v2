<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Mail\DeliveryDriverInvitationMail;
use App\Models\Settings;
use App\Support\EmailPlainText;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\View;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Audit evidence only: in-memory database, synthetic recipients, actual Blade.
 * Never calls application sender methods, send, postSend, or a network method.
 */
final class EmailSystemAuditTest extends IsolatedTestCase
{
    private string $compiled;
    private array $evidence = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->compiled = sys_get_temp_dir() . '/agendaally-email-audit-' . bin2hex(random_bytes(8));
        mkdir($this->compiled, 0700);
        $this->app->instance('files', new Filesystem);
        $this->app['config']->set('view', ['paths' => [resource_path('views')], 'compiled' => $this->compiled]);
        $this->app->register(\Illuminate\View\ViewServiceProvider::class);
        $this->database->schema()->create('settings', function (Blueprint $t): void {
            $t->id(); $t->string('key'); $t->text('value')->nullable(); $t->timestamps();
        });
        $this->database->schema()->create('translations', function (Blueprint $t): void {
            $t->id(); $t->string('locale'); $t->string('key'); $t->text('value'); $t->timestamps();
        });
        foreach ([
            'logo' => 'http://localhost:8000/storage/images/settings/agendaally-platform-logo.png',
            'title' => 'AgendaAlly',
            'footer_text' => '© 2026 AgendaAlly. All rights reserved.',
            'instagram' => 'https://www.instagram.com/agendaally',
            'facebook' => 'https://www.facebook.com/AgendaAlly/',
            'linkedin' => 'https://www.linkedin.com/company/agendaally',
            'twitter' => '',
        ] as $key => $value) Settings::create(['key' => $key, 'value' => $value]);
        $this->database->table('translations')->insert([
            'locale' => 'en', 'key' => 'title', 'value' => 'AgendaAlly',
        ]);
    }

    protected function tearDown(): void
    {
        if ($this->evidence && ($dir = getenv('AGENDAALLY_EMAIL_AUDIT_PROOF_DIR'))) {
            if (!is_dir($dir)) mkdir($dir, 0700, true);
            foreach ($this->evidence as $id => $entry) {
                file_put_contents("$dir/$id.json", json_encode($entry, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n");
            }
        }
        (new Filesystem)->deleteDirectory($this->compiled);
        parent::tearDown();
    }

    private function mail(string $subject): PHPMailer
    {
        $mail = new PHPMailer(true);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('fixture@example.invalid', 'AgendaAlly');
        $mail->addAddress('recipient@example.invalid');
        $mail->Subject = $subject;
        return $mail;
    }

    private function proof(string $id, string $template, PHPMailer $mail): void
    {
        self::assertTrue($mail->preSend());
        $mime = $mail->getSentMIMEMessage();
        self::assertStringContainsString('multipart/related', $mime);
        self::assertCount(4, $mail->getAttachments());
        preg_match_all('/<img[^>]+src="([^"]+)"/', $mail->Body, $images);
        self::assertCount(4, $images[1]);
        $preview = $mail->Body;
        foreach ($mail->getAttachments() as $attachment) {
            self::assertSame('image/png', $attachment[4]);
            self::assertSame('inline', $attachment[6]);
            self::assertContains('cid:' . $attachment[7], $images[1]);
            self::assertStringContainsString('Content-ID: <' . $attachment[7] . '>', $mime);
            $bytes = file_get_contents($attachment[0]);
            self::assertStringContainsString(chunk_split(base64_encode($bytes), 76, "\r\n"), $mime);
            $preview = str_replace('cid:' . $attachment[7], 'data:image/png;base64,' . base64_encode($bytes), $preview);
        }
        foreach (['localhost', 'https://https://', '<svg', 'fonts.googleapis', 'cid:agendaally-twitter'] as $bad) {
            self::assertStringNotContainsString($bad, $mail->Body);
        }
        self::assertStringNotContainsString('Content-Disposition: attachment;', $mime);
        $this->evidence[$id] = [
            'template' => $template, 'render' => 'PASS', 'mime' => 'PASS',
            'cidImages' => 4, 'imageMime' => 'image/png', 'exactEmbeddedBytes' => true,
            'ordinaryAttachments' => 0, 'addresses' => 'synthetic .invalid only',
            'sendCalled' => false, 'postSendCalled' => false,
        ];
        if ($dir = getenv('AGENDAALLY_EMAIL_AUDIT_PROOF_DIR')) {
            if (!is_dir($dir)) mkdir($dir, 0700, true);
            file_put_contents("$dir/$id.html", $preview);
            file_put_contents("$dir/$id.eml", $mime);
        }
    }

    public function test_actual_default_account_content_renders_with_shared_cid_mime(): void
    {
        foreach ([
            'verification-default' => 'verify',
            'reset-default' => 'reset',
        ] as $id => $type) {
            $definition = \App\Support\SystemEmailTemplates::definitions()[$type];
            $mail = $this->mail($definition['subject']);
            \App\Support\ManagedEmailPresentation::render($mail, [
                'type' => $type, 'subject' => $definition['subject'],
                'body' => $definition['body'], 'alt_body' => $definition['body'],
            ], '000000');
            self::assertStringContainsString('000000', $mail->Body);
            self::assertStringNotContainsString('$verify_code', $mail->Body);
            $this->proof($id, 'actual managed presentation + canonical default body', $mail);
        }
    }

    public function test_existing_layout_long_content_and_cta_stress_without_creating_a_booking_template(): void
    {
        foreach (['spaced', 'unbroken'] as $style) {
            $text = $style === 'spaced'
                ? str_repeat('Long synthetic name ', 6)
                : 'SYNTHETIC_' . str_repeat('X', 96);
            $fields = ['Customer', 'Shop', 'Service', 'Specialist', 'Reference'];
            $content = '<p>LAYOUT STRESS FIXTURE ONLY — not a booking email or saved booking.</p>';
            foreach ($fields as $field) $content .= '<p><strong>' . $field . '</strong>: ' . e($text) . '</p>';
            $content .= '<p>Date: 2030-01-07. Time: 09:00–10:00 UTC. Duration: 60 minutes.</p>';
            $content .= '<p>Booking status: synthetic example. Payment: UNPAID / UNCOLLECTED.</p>';
            $content .= '<p><a href="https://example.invalid">Open the synthetic appointment information and review all details</a></p>';
            $content .= '<p>Displayed URL: https://example.invalid/' . str_repeat('unbroken-reference', 18) . '</p>';
            $mail = $this->mail('Shared layout stress fixture — NOT SENT');
            $mail->AltBody = strip_tags($content);
            $mail->Body = View::make('emails.layout', [
                'mailer' => $mail, 'title' => $mail->Subject, 'content' => $content,
            ])->render();
            $this->proof("layout-long-$style", 'existing emails.layout with synthetic stress content, not a new template', $mail);
        }
    }

    public function test_actual_order_email_renders_escapes_customer_fields_and_has_no_pdf_attachment(): void
    {
        foreach (['spaced', 'unbroken'] as $style) {
            $long = $style === 'spaced' ? str_repeat('Long synthetic name ', 5) : 'SYNTHETIC_' . str_repeat('X', 75);
            $userName = '<b data-audit="customer">' . $long . '</b>';
            $stock = (object) [
                'product' => (object) ['translation' => (object) ['title' => $long]],
                'stockExtras' => collect([]),
            ];
            $order = (object) [
                'id' => 999999, 'username' => $userName, 'phone' => 'synthetic phone',
                'user' => (object) ['firstname' => $long, 'lastname' => 'Fixture', 'phone' => 'synthetic phone'],
                'currency' => (object) ['position' => 'before', 'symbol' => 'XAF'],
                'status' => 'new', 'delivery_type' => 'delivery',
                'shop' => (object) ['phone' => 'synthetic phone',
                    'translation' => (object) ['title' => $long, 'address' => str_repeat('Synthetic street ', 6)]],
                'delivery_date' => '2030-01-07', 'delivery_time' => '09:00',
                'created_at' => '2030-01-01 10:00:00',
                'address' => ['address' => str_repeat('Synthetic address ', 6)],
                'myAddress' => null, 'pickup' => null, 'coupon' => null,
                'transaction' => null, 'transactions' => collect([]),
                'rate_total_price' => 1000, 'rate_total_discount' => 0, 'rate_tax' => 0,
                'rate_delivery_fee' => 0, 'rate_coupon_sum_price' => 0,
                'orderDetails' => collect([(object) [
                    'stock' => $stock, 'children' => collect([]), 'quantity' => 1, 'rate_total_price' => 1000,
                ]]),
            ];
            $mail = $this->mail('Order summary fixture — NOT A PAYMENT RECEIPT');
            $mail->Body = View::make('order-email-invoice', [
                'mailer' => $mail, 'order' => $order, 'lang' => 'en',
                'title' => $mail->Subject,
                'logo' => 'http://localhost:8000/storage/images/settings/agendaally-platform-logo.png',
            ])->render();
            $mail->AltBody = EmailPlainText::fromHtml($mail->Body, $mail->Subject);
            self::assertStringContainsString('&lt;b data-audit=', $mail->Body);
            self::assertStringNotContainsString('<b data-audit=', $mail->Body);
            $this->proof("order-long-$style", 'actual order-email-invoice Blade, synthetic graph', $mail);
            $this->evidence["order-long-$style"]['altBody'] = 'PRESENT — exact rendered HTML projection in native sendOrder';
            $this->evidence["order-long-$style"]['invoicePdfAttachment'] = 'ABSENT in native sendOrder';
        }
    }

    public function test_driver_mailable_actual_blade_and_framework_mime_are_local_only(): void
    {
        $mailable = new DeliveryDriverInvitationMail(
            str_repeat('Synthetic Shop ', 6),
            'https://example.invalid/delivery-driver-invitation#token=synthetic-do-not-use',
            'January 7, 2030 10:00 UTC'
        );
        $mailable->build();
        self::assertSame('emails.delivery-driver-invitation', $mailable->view);
        $html = View::make($mailable->view, [
            'shopName' => $mailable->shopName, 'invitationLink' => $mailable->invitationLink,
            'expiresAt' => $mailable->expiresAt,
        ])->render();
        $mime = (new \Symfony\Component\Mime\Email())
            ->from('fixture@example.invalid')->to('recipient@example.invalid')
            ->subject($mailable->subject)->html($html)->toString();
        self::assertStringContainsString('Content-Type: text/html', $mime);
        self::assertStringContainsString('delivery-driver-invitation#token=', $html);
        self::assertStringNotContainsString('<img', $html);
        self::assertStringNotContainsString('localhost', $html);
        $this->evidence['driver-invitation'] = [
            'template' => $mailable->view, 'render' => 'PASS', 'mime' => 'PASS — local Symfony MIME construction',
            'limits' => 'Not Laravel transport/configuration or invitation acceptance proof',
            'sharedLayout' => false, 'cidImages' => 0, 'sendCalled' => false, 'postSendCalled' => false,
        ];
        if ($dir = getenv('AGENDAALLY_EMAIL_AUDIT_PROOF_DIR')) {
            if (!is_dir($dir)) mkdir($dir, 0700, true);
            file_put_contents("$dir/driver-invitation.html", $html);
            file_put_contents("$dir/driver-invitation.eml", $mime);
        }
    }

    public function test_local_header_and_address_validation_without_transport(): void
    {
        $mail = $this->mail("Fixture\r\nBcc: other@example.invalid");
        $mail->Body = '<p>Local header validation fixture only.</p>';
        self::assertTrue($mail->preSend());
        self::assertDoesNotMatchRegularExpression('/^Bcc:/mi', $mail->getSentMIMEMessage());
        self::assertFalse(PHPMailer::validateAddress("fixture@example.invalid\r\nBcc: other@example.invalid"));
    }
}
