<?php
declare(strict_types=1);
namespace App\Support;

use PHPMailer\PHPMailer\SMTP;

/** Observe the DATA acknowledgement, without retaining DATA or AUTH content. */
final class AccountEmailEvidenceSmtp extends SMTP
{
    public function data($msg_data)
    {
        $accepted = parent::data($msg_data);
        $row = app('agendaally.selected_account_delivery');
        $code = preg_match('/^([0-9]{3})[ -]/', $this->getLastReply(), $match) ? (int)$match[1] : null;
        AccountEmailEvidence::append('smtp_data_acknowledgement', [
            'delivery_id'=>$row->id,'accepted'=>$accepted,'smtp_status'=>$code,
        ]);
        return $accepted;
    }
}
