<?php
declare(strict_types=1);

namespace App\Services\EmailSettingService;

use App\Helpers\EnvironmentPolicy;
use App\Helpers\AdminSmtpTestPolicy;
use App\Helpers\SelectedAccountEmailPolicy;
use App\Helpers\ResponseError;
use App\Models\EmailSetting;
use App\Models\EmailSubscription;
use App\Models\EmailTemplate;
use App\Support\ManagedEmailPresentation;
use App\Support\SystemEmailTemplates;
use App\Models\Gallery;
use App\Models\Order;
use App\Models\Settings;
use App\Models\Translation;
use App\Models\User;
use App\Mail\DeliveryDriverInvitationMail;
use App\Services\CoreService;
use App\Support\EmailPresentation;
use App\Support\EmailPlainText;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Illuminate\Support\Facades\Storage;
use Throwable;
use Illuminate\Support\Facades\View;

class EmailSendService extends CoreService
{
    /**
     * @return string
     */
    protected function getModelClass(): string
    {
        return EmailSetting::class;
    }

    /**
     * Port 465 is implicit TLS (SMTPS) - STARTTLS on that port never
     * completes the handshake and PHPMailer just times out with a
     * generic "Could not connect to SMTP host". Every other port (25,
     * 587, 2525, ...) expects the STARTTLS upgrade instead. PHPMailer's
     * own SMTPDebug only echoes to stdout, which is discarded in a
     * web/API request, so route it into Laravel's log instead whenever
     * the setting's smtp_debug flag is on.
     */
    private function configureSmtpSecurity(PHPMailer $mail, EmailSetting $emailSetting): void
    {
        if (app()->bound('agendaally.selected_account_delivery')
            && \App\Support\AccountEmailEvidence::selected(app('agendaally.selected_account_delivery'))
            && SelectedAccountEmailPolicy::permitsRow(app('agendaally.selected_account_delivery'))) {
            $mail->setSMTPInstance(new \App\Support\AccountEmailEvidenceSmtp);
        }
        $mail->SMTPSecure = (int) $emailSetting->port === 465
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;

        if ($emailSetting->smtp_debug) {
            $mail->SMTPDebug   = SMTP::DEBUG_CONNECTION;
            $mail->Debugoutput = function (string $str, int $level) {
                // SMTP debug lines can contain AUTH material and account
                // challenges in DATA. Retain activity, never raw wire content.
                Log::channel('single')->debug('SMTP transport activity.', ['level' => $level]);
            };
        }
    }

    /**
     * Return a non-success response when the selected development transport
     * is log-only. Never include recipients, message bodies, reset tokens, or
     * verification codes in the log record.
     */
    private function suppressEmailDelivery(string $operation): ?array
    {
        if (EnvironmentPolicy::emailMode() === 'smtp' || SelectedAccountEmailPolicy::permitsSender($operation)) {
            return null;
        }

        Log::info('Email delivery suppressed; no email was sent.', [
            'operation' => $operation,
            'mode' => EnvironmentPolicy::emailMode(),
        ]);

        return [
            'message' => 'Email delivery is disabled for this environment. No email was sent.',
            'status' => false,
            'code' => ResponseError::ERROR_503,
        ];
    }

    /**
     * Send a Driver invitation only through the environment-policy-gated
     * native email service. Never log a recipient, fragment URL, or token.
     */
    public function sendDeliveryDriverInvitation(
        string $recipient,
        string $shopName,
        string $fragmentLink,
        string $expiresAt
    ): array {
        if ($this->suppressEmailDelivery(__FUNCTION__)) {
            return ['status' => false, 'delivery_status' => 'not_delivered'];
        }

        try {
            Mail::to($recipient)->send(new DeliveryDriverInvitationMail($shopName, $fragmentLink, $expiresAt));

            return ['status' => true, 'delivery_status' => 'sent'];
        } catch (Throwable) {
            // Transport exceptions can include the recipient or full URL.
            return ['status' => false, 'delivery_status' => 'failed'];
        }
    }

    public function sendSubscriptions(EmailTemplate $emailTemplate): array
    {
        if ($suppressed = $this->suppressEmailDelivery(__FUNCTION__)) {
            return $suppressed;
        }

        $sent = 0;
        $failed = 0;
        EmailSubscription::where('active', true)->with('user')->chunkById(100, function ($subscriptions) use ($emailTemplate, &$sent, &$failed): void {
            foreach ($subscriptions as $subscription) {
                $user = $subscription->user;
                if (!$user || !$user->email) continue;
                try {
                    // Fresh envelope/assets per recipient; never giant To/CC/BCC.
                    $mail = $this->emailBaseAuth($emailTemplate->emailSetting, $user);
                    $mail->Subject = $emailTemplate->subject;
                    $mail->Body = View::make('emails.layout', [
                        'mailer' => $mail, 'title' => $mail->Subject, 'content' => $emailTemplate->body,
                    ])->render();
                    $mail->AltBody = $emailTemplate->alt_body
                        ?: EmailPlainText::fromHtml($mail->Body, $mail->Subject);
                    foreach ($emailTemplate->galleries as $gallery) {
                        try {
                            EmailPresentation::attachPublicFile($mail, $gallery->path);
                        } catch (Throwable) {
                            Log::warning('Email template attachment unavailable; details redacted.');
                        }
                    }
                    if (!$mail->send()) throw new \RuntimeException('Delivery unconfirmed.');
                    $sent++;
                } catch (Throwable) {
                    $failed++;
                    Log::warning('Subscription delivery unconfirmed; recipient and transport details redacted.');
                }
            }
        });
        return [
            'status' => $sent > 0 && $failed === 0,
            'code' => $sent > 0 && $failed === 0 ? ResponseError::NO_ERROR : ResponseError::ERROR_504,
            'sent_count' => $sent, 'failed_count' => $failed,
        ];
    }

    public function sendVerify(User $user, ?string $challenge = null): array
    {
        if ($suppressed = $this->suppressEmailDelivery(__FUNCTION__)) {
            return $suppressed;
        }

        $emailTemplate = EmailTemplate::where('type', EmailTemplate::TYPE_VERIFY)->first();

        $mail = $this->emailBaseAuth($emailTemplate?->emailSetting, $user);

        try {

            $default = SystemEmailTemplates::definitions()[EmailTemplate::TYPE_VERIFY];
            ManagedEmailPresentation::render($mail, [
                'type' => EmailTemplate::TYPE_VERIFY,
                'subject' => $emailTemplate?->subject ?? $default['subject'],
                'body' => $emailTemplate?->body ?? $default['body'],
                'alt_body' => $emailTemplate?->alt_body ?: ($emailTemplate?->body ?? $default['body']),
            ], $challenge ?? $user->verify_token);

            if (!empty(data_get($emailTemplate, 'galleries'))) {
                foreach ($emailTemplate->galleries as $gallery) {
                    /** @var Gallery $gallery */
                    try {
                        EmailPresentation::attachPublicFile($mail, $gallery->path);
                    } catch (Throwable) {
                        Log::warning('Email template attachment unavailable; details redacted.');
                    }
                }
            }

            if (!$mail->send()) throw new \RuntimeException('Delivery acknowledgement unavailable.');

            return [
                'status' => true,
                'code' => ResponseError::NO_ERROR,
            ];
        } catch (Throwable) {
            Log::warning('Selected verification email transport failed; details redacted.');
            return [
                'message'   => 'Selected email delivery was not confirmed.',
                'status'    => false,
                'code'      => ResponseError::ERROR_504,
            ];
        }
    }

    public function sendEmailPasswordReset(User $user, $str): array
    {
        if ($suppressed = $this->suppressEmailDelivery(__FUNCTION__)) {
            return $suppressed;
        }

        $emailTemplate = EmailTemplate::where('type', EmailTemplate::TYPE_RESET)->first();

        $mail = $this->emailBaseAuth($emailTemplate?->emailSetting, $user);

        try {

            $default = SystemEmailTemplates::definitions()[EmailTemplate::TYPE_RESET];
            ManagedEmailPresentation::render($mail, [
                'type' => EmailTemplate::TYPE_RESET,
                'subject' => $emailTemplate?->subject ?? $default['subject'],
                'body' => $emailTemplate?->body ?? $default['body'],
                'alt_body' => $emailTemplate?->alt_body ?: ($emailTemplate?->body ?? $default['body']),
            ], $str);

            if (!empty(data_get($emailTemplate, 'galleries'))) {
                foreach ($emailTemplate->galleries as $gallery) {
                    /** @var Gallery $gallery */
                    try {
                        EmailPresentation::attachPublicFile($mail, $gallery->path);
                    } catch (Throwable) {
                        Log::warning('Email template attachment unavailable; details redacted.');
                    }
                }
            }

            if (!$mail->send()) throw new \RuntimeException('Delivery acknowledgement unavailable.');

            return [
                'status' => true,
                'code' => ResponseError::NO_ERROR,
            ];
        } catch (Throwable) {
            Log::warning('Selected reset email transport failed; details redacted.');
            return [
                'message'   => 'Selected email delivery was not confirmed.',
                'status'    => false,
                'code'      => ResponseError::ERROR_504,
            ];
        }
    }

    /**
     * @param Order $order
     * @return array
     */
    public function sendOrder(Order $order): array
    {
        if ($suppressed = $this->suppressEmailDelivery(__FUNCTION__)) {
            return $suppressed;
        }

        $order = $order->fresh([
            'user:id,firstname,lastname,phone,email',
            'currency:id,position,symbol',
            'shop:id,uuid,slug,phone',
            'shop.translation' => fn($q) => $q->select([
                'id',
                'shop_id',
                'locale',
                'title',
                'address',
            ])->where('locale', $this->language),
            'orderDetails' => fn($q) => $q->with([
                'stock.stockExtras.value',
                'stock.product.translation' => fn($q) => $q
                    ->select([
                        'id',
                        'product_id',
                        'locale',
                        'title',
                    ])
                    ->where('locale', $this->language),
                'stock.stockExtras.group.translation' => function ($q) {
                    $q
                        ->select('id', 'extra_group_id', 'locale', 'title')
                        ->where('locale', $this->language);
                },
            ]),
            'transactions.paymentSystem',
            'transactions.children',
            'coupon',
            'myAddress'
        ]);

        if (!$order->user->email) {
            return [
                'message' => 'email is empty', //$mail->ErrorInfo,
                'status'  => false,
                'code'    => ResponseError::ERROR_504,
            ];
        }

        Pdf::setOption(['dpi' => 150, 'defaultFont' => 'sans-serif']);

        $titleKey = "order.email.invoice.$order->status.title";
        $title    = Translation::where(['locale' => $this->language, 'key' => $titleKey])->first()?->value ?? $titleKey;
        $logo     = Settings::where('key', 'logo')->first()?->value;
        $fileName = null;

        try {
            $mail           = $this->emailBaseAuth(null, $order->user);
            $mail->Subject  = $title;
            $mail->Body     = View::make('order-email-invoice', [
                'mailer' => $mail, 'order' => $order, 'lang' => $this->language,
                'title' => $title, 'logo' => $logo,
            ])->render();
            // PHPMailer owns multipart/related headers for the embedded assets.
            $mail->AltBody = EmailPlainText::fromHtml($mail->Body, $mail->Subject);
            $mail->send();

            Storage::delete(storage_path("images/$fileName"));

            return [
                'status' => true,
                'code'   => ResponseError::NO_ERROR,
            ];
        } catch (Exception $e) {
            $this->error($e);
            return [
                'message' => $e->getMessage(), //$mail->ErrorInfo,
                'status'  => false,
                'code'    => ResponseError::ERROR_504,
            ];
        }
    }

    public function emailBaseAuth(?EmailSetting $emailSetting, User $user): PHPMailer
    {
        if (EnvironmentPolicy::emailMode() !== 'smtp'
            && !SelectedAccountEmailPolicy::permitsSender(__FUNCTION__, $user)) {
            throw new \RuntimeException(
                'Email delivery is disabled for this environment. No email was sent.'
            );
        }

        if (empty($emailSetting)) {
            $emailSetting = EmailSetting::where('active', true)->latest('updated_at')->first();
        }

        if (empty($emailSetting)) {
            throw new Exception('No active email provider is configured');
        }

        $mail = app()->bound(PHPMailer::class) ? app(PHPMailer::class) : new PHPMailer(true);
        $mail->isHTML();
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->SMTPAuth     = $emailSetting->smtp_auth;
        $mail->Host         = $emailSetting->host;
        $mail->Port         = $emailSetting->port;
        $mail->Username     = $emailSetting?->from_to;
        $mail->Password     = $emailSetting?->password;
        $this->configureSmtpSecurity($mail, $emailSetting);
        $mail->SMTPOptions  = [
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false
            ]
        ];

        try {

            $mail->setFrom($emailSetting->from_to, $emailSetting->from_site);
            $mail->addAddress($user->email, $user->name_or_email);

        } catch (Throwable) {
            Log::warning('Email envelope invalid; recipient and transport details redacted.');
            throw new \RuntimeException('Email envelope is invalid.');
        }

        return $mail;
    }

    /**
     * Sends a plain test message using this exact provider row's own
     * credentials, regardless of whether it's marked active - so an admin
     * can verify a new SMTP configuration before switching to it, rather
     * than only finding out it's broken once it becomes "the" active
     * provider (see emailBaseAuth()'s active-only fallback, which this
     * intentionally bypasses).
     */
    public function sendTest(EmailSetting $emailSetting, string $recipientEmail): array
    {
        if (!AdminSmtpTestPolicy::allowed()) {
            if ($suppressed = $this->suppressEmailDelivery(__FUNCTION__)) {
                return $suppressed;
            }
        }

        try {
            $mail = app()->bound(PHPMailer::class) ? app(PHPMailer::class) : new PHPMailer(true);
            $mail->isHTML();
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            // A bad host/port otherwise hangs for PHPMailer's default
            // ~5 minute timeout before this ever responds - unacceptable
            // for a "test this now" button an admin is actively waiting on.
            $mail->Timeout     = 15;
            $mail->SMTPAuth    = $emailSetting->smtp_auth;
            $mail->Host        = $emailSetting->host;
            $mail->Port        = $emailSetting->port;
            $mail->Username    = $emailSetting->from_to;
            $mail->Password    = $emailSetting->password;
            $this->configureSmtpSecurity($mail, $emailSetting);
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer'       => true,
                    'verify_peer_name'  => true,
                    'allow_self_signed' => false,
                ]
            ];

            $mail->setFrom($emailSetting->from_to, $emailSetting->from_site);
            $mail->addAddress($recipientEmail);
            $mail->Subject = 'Test email from ' . ($emailSetting->from_site ?: 'your platform');
            $mail->AltBody = 'This is a test email confirming your SMTP configuration works.';
            $mail->Body    = View::make('emails.layout', [
                'mailer'  => $mail,
                'title'   => $mail->Subject,
                'content' => '<p>' . $mail->AltBody . '</p>',
            ])->render();

            $mail->send();

            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        } catch (Throwable $e) {
            $classification = $e instanceof \RuntimeException
                && in_array($e->getMessage(), [
                    'SMTP_CREDENTIAL_REPLACEMENT_REQUIRED', 'SMTP_CREDENTIAL_UNAVAILABLE',
                ], true) ? 'credential_unavailable' : 'smtp_delivery_unconfirmed';
            Log::warning('Admin SMTP test was not confirmed.', ['classification' => $classification]);
            return [
                'status'  => false,
                'code'    => ResponseError::ERROR_504,
                'error_code' => $classification,
                'message' => $classification === 'credential_unavailable'
                    ? 'SMTP credential is unavailable. Enter and save a new password before testing.'
                    : 'SMTP test delivery was not confirmed. Check the saved host, port, sender and credentials. Do not retry automatically.',
            ];
        }
    }
}
