<?php
declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class DeliveryDriverInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $shopName,
        public string $invitationLink,
        public string $expiresAt
    ) {
    }

    public function build(): self
    {
        return $this->subject('AgendaAlly delivery-driver invitation')
            ->view('emails.delivery-driver-invitation');
    }
}