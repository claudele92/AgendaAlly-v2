<?php
declare(strict_types=1);

namespace App\Services\SMSGatewayService;

use App\Helpers\EnvironmentPolicy;
use App\Models\SmsPayload;
use App\Services\CoreService;
use Exception;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class TwilioService extends CoreService
{
    protected function getModelClass(): string
    {
        return SmsPayload::class;
    }

    /**
     * @param $phone
     * @param $otp
     * @param SmsPayload $smsPayload
     * @return array|bool[]
     */
    public function sendSms($phone, $otp, SmsPayload $smsPayload): array
    {
        if (EnvironmentPolicy::smsMode() !== 'provider') {
            Log::info('SMS delivery suppressed; the generated challenge was not sent.', [
                'phone_hash' => hash('sha256', preg_replace('/\D/', '', (string) $phone)),
                'mode' => EnvironmentPolicy::smsMode(),
            ]);

            return [
                'status' => false,
                'message' => 'SMS delivery is disabled for this environment. No verification code was sent.',
            ];
        }

        try {
            $accountId      = data_get($smsPayload->payload, 'twilio_account_id') ?: config('services.twilio.account_id');
            $authToken      = data_get($smsPayload->payload, 'twilio_auth_token') ?: config('services.twilio.auth_token');
            $otpCode        = data_get($otp, 'otpCode');
            $twilioNumber   = data_get($smsPayload->payload, 'twilio_number') ?: config('services.twilio.number');

            if (strlen($phone) < 7) {
                throw new Exception('Invalid phone number', 400);
            }

            $client = new Client($accountId, $authToken);
            $client->messages->create("+$phone", [
                'from' => $twilioNumber,
                'body' => "Confirmation code $otpCode"
            ]);

            return ['status' => true, 'message' => 'success'];

        } catch (Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

}
