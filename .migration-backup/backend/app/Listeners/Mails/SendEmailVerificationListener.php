<?php
declare(strict_types=1);

namespace App\Listeners\Mails;

use App\Events\Mails\SendEmailVerification;
use App\Services\DeliveryDriver\DriverInvitationService;
use App\Services\AuthService\EmailVerificationService;
use App\Models\User;
use App\Services\EmailSettingService\EmailSendService;
use App\Traits\Loggable;
use Exception;

class SendEmailVerificationListener
{
    use Loggable;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event
     * @param SendEmailVerification $event
     * @return void
     */
    public function handle(SendEmailVerification $event): void
    {
        try {

            if (!empty($event->user)) {
                $deliveryInvitations = app(DriverInvitationService::class);
                $isDriverContact = $deliveryInvitations->hasDedicatedContactInvitation($event->user);
                $token = $isDriverContact
                    ? $deliveryInvitations->issueNativeVerificationToken($event->user)
                    : app(EmailVerificationService::class)->issue($event->user);

                if (!$isDriverContact) {
                    app(\App\Services\EmailSettingService\SelectedEmailDelivery::class)
                        ->enqueue(User::findOrFail($event->user->id),'verify',$token);
                    $event->deliveryStatus = 'queued';
                    return;
                }
                $result = (new EmailSendService)->sendVerify(User::find($event->user->id), $token);
                $event->deliveryStatus = !empty($result['status'])
                    ? 'sent'
                    : (($result['code'] ?? null) === \App\Helpers\ResponseError::ERROR_503
                        ? 'not_delivered'
                        : 'failed');

            }

        } catch (Exception $e) {
            // Delivery-driver verification secrets and recipient-specific
            // transport diagnostics must never enter application logs.
            if (empty($event->user)
                || !app(DriverInvitationService::class)->hasDedicatedContactInvitation($event->user)
            ) {
                $this->error($e);
            }
        }
    }
}
