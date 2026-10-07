<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Models\Settings;
use App\Support\EmailPresentation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\Filesystem;
use PHPMailer\PHPMailer\PHPMailer;
use Illuminate\Support\Facades\View;

final class EmailPresentationTest extends IsolatedTestCase
{
    private string $compiled;

    protected function setUp(): void
    {
        parent::setUp();
        $this->compiled = sys_get_temp_dir() . '/agendaally-email-views-' . bin2hex(random_bytes(8));
        mkdir($this->compiled, 0700);
        $this->app->instance('files', new Filesystem);
        $this->app['config']->set('view', [
            'paths' => [resource_path('views')], 'compiled' => $this->compiled,
        ]);
        $this->app->register(\Illuminate\View\ViewServiceProvider::class);
        $this->database->schema()->create('settings', function (Blueprint $t): void {
            $t->id(); $t->string('key'); $t->text('value')->nullable(); $t->timestamps();
        });
    }

    protected function tearDown(): void
    {
        (new Filesystem)->deleteDirectory($this->compiled);
        parent::tearDown();
    }

    private function mail(): PHPMailer
    {
        // Build MIME only. There is deliberately no send()/postSend() call.
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('fixture@example.invalid', 'AgendaAlly');
        $mail->addAddress('recipient@example.invalid');
        $mail->Subject = 'AgendaAlly local presentation proof — NOT SENT';
        $mail->AltBody = 'Local presentation proof. No email was sent.';
        return $mail;
    }

    private function fixture(): array
    {
        return [
            'logo' => 'http://localhost:8000/storage/images/settings/agendaally-platform-logo.png',
            'title' => 'AgendaAlly', 'footer_text' => '© 2026 AgendaAlly. All rights reserved.',
            'instagram' => 'https://www.instagram.com/agendaally',
            'facebook' => 'https://www.facebook.com/AgendaAlly/',
            'linkedin' => 'https://www.linkedin.com/company/agendaally', 'twitter' => '',
        ];
    }

    public function test_real_blade_and_mime_embed_every_image_and_preserve_normalized_destinations(): void
    {
        foreach ($this->fixture() as $key => $value) {
            Settings::create(['key' => $key, 'value' => $value]);
        }
        $mail = $this->mail();
        $mail->Body = View::make('emails.layout', [
            'mailer' => $mail, 'title' => $mail->Subject,
            'content' => '<p>This is a local-only AgendaAlly email presentation check.</p>',
        ])->render();
        self::assertTrue($mail->preSend());
        $mime = $mail->getSentMIMEMessage();
        self::assertStringContainsString('multipart/related', $mime);
        self::assertCount(4, $mail->getAttachments());
        preg_match_all('/<img[^>]+src="([^"]+)"/', $mail->Body, $images);
        self::assertCount(4, $images[1]);
        foreach ($images[1] as $src) {
            self::assertStringStartsWith('cid:', $src);
            self::assertContains(substr($src, 4), array_column($mail->getAttachments(), 7));
            self::assertStringContainsString('Content-ID: <' . substr($src, 4) . '>', $mime);
        }
        foreach (['instagram', 'facebook', 'linkedin'] as $key) {
            self::assertStringContainsString('href="' . $this->fixture()[$key] . '"', $mail->Body);
        }
        self::assertStringNotContainsString('https://https://', $mail->Body);
        self::assertStringNotContainsString('localhost', $mail->Body);
        self::assertStringNotContainsString('<svg', $mail->Body);
        self::assertStringNotContainsString('url(', $mail->Body);
        self::assertStringNotContainsString('cid:agendaally-twitter', $mail->Body);

        // Browser proof substitutes the exact MIME-attached bytes, not mock icons.
        $preview = $mail->Body;
        foreach ($mail->getAttachments() as $attachment) {
            $bytes = file_get_contents($attachment[0]);
            self::assertNotFalse($bytes);
            self::assertStringContainsString(chunk_split(base64_encode($bytes), 76, "\r\n"), $mime);
            $preview = str_replace('cid:' . $attachment[7],
                'data:' . $attachment[4] . ';base64,' . base64_encode($bytes), $preview);
        }
        $proof = getenv('AGENDAALLY_EMAIL_PRESENTATION_PROOF_DIR');
        if ($proof) {
            if (!is_dir($proof)) mkdir($proof, 0700, true);
            file_put_contents($proof . '/email-presentation-local-proof.html', $preview);
            file_put_contents($proof . '/email-presentation-local-proof.eml', $mime);
        }
    }

    public function test_social_urls_accept_supported_shapes_and_reject_private_placeholder_or_malformed_targets(): void
    {
        $hosts = ['instagram.com', 'www.instagram.com'];
        foreach (['instagram.com/agendaally', '//instagram.com/agendaally',
            'http://instagram.com/agendaally', 'https://instagram.com/agendaally'] as $url) {
            self::assertSame('https://instagram.com/agendaally', EmailPresentation::socialUrl($url, $hosts));
        }
        foreach (['', 'https://https://instagram.com/agendaally', 'javascript:alert(1)',
            'http://localhost/agendaally', 'https://example.com/agendaally',
            'https://instagram.com', 'https://instagram.com/your-profile',
            'https://instagram.com.evil.invalid/agendaally', 'https://user@instagram.com/agendaally',
            'https://instagram.com:465/agendaally', 'https://facebook.com/AgendaAlly',
            "https://instagram.com/agendaally\nbad"] as $url) {
            self::assertNull(EmailPresentation::socialUrl($url, $hosts), $url);
        }
    }

    public function test_missing_or_private_logo_cannot_be_fetched_or_embedded_and_cids_are_idempotent(): void
    {
        $mail = $this->mail();
        foreach (['http://localhost:8000/storage/../../.env',
            'https://private.invalid/unavailable.png', '/storage/missing.png',
            '/storage/../../../resources/email/icons/facebook.svg'] as $logo) {
            $data = EmailPresentation::prepare($mail, ['logo' => $logo]);
            self::assertNull($data['logo']);
            self::assertSame('unavailable', $data['logo_status']);
        }
        self::assertCount(0, $mail->getAttachments());
        $first = EmailPresentation::prepare($mail, $this->fixture());
        $second = EmailPresentation::prepare($mail, $this->fixture());
        self::assertSame('embedded', $first['logo_status']);
        self::assertSame($first, $second);
        self::assertCount(4, $mail->getAttachments());
        $textOnly = EmailPresentation::prepare(null, $this->fixture());
        self::assertNull($textOnly['logo']);
        self::assertCount(3, $textOnly['socials']);
    }

    public function test_invoice_uses_the_same_safe_asset_preparation_and_mailer_controls_mime_headers(): void
    {
        $view = file_get_contents(resource_path('views/order-email-invoice.blade.php'));
        self::assertStringContainsString('EmailPresentation::prepare($mailer', $view);
        self::assertStringNotContainsString('href="https://{{', $view);
        self::assertStringNotContainsString('src="{{$logo}}"', $view);
        $sender = file_get_contents(app_path('Services/EmailSettingService/EmailSendService.php'));
        // Account templates share ManagedEmailPresentation now; invoice still
        // passes the same mailer directly. Verify both actual rendering paths.
        self::assertSame(3, substr_count($sender, "'mailer'"));
        self::assertSame(2, substr_count($sender, 'ManagedEmailPresentation::render'));
        self::assertStringContainsString("'mailer' => \$mail",
            file_get_contents(app_path('Support/ManagedEmailPresentation.php')));
        self::assertStringNotContainsString("addCustomHeader('Content-type'", $sender);
        self::assertStringNotContainsString("addCustomHeader('MIME-Version'", $sender);
    }

    public function test_gallery_attachments_are_owned_local_files_not_http_paths(): void
    {
        $mail = $this->mail();
        EmailPresentation::attachPublicFile($mail, 'images/settings/agendaally-platform-logo.png');
        self::assertCount(1, $mail->getAttachments());
        self::assertSame('attachment', $mail->getAttachments()[0][6]);
        self::assertSame(realpath(storage_path('app/public/images/settings/agendaally-platform-logo.png')),
            $mail->getAttachments()[0][0]);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('EMAIL_TEMPLATE_ATTACHMENT_UNAVAILABLE');
        EmailPresentation::attachPublicFile($mail, '../../.env');
    }
}
