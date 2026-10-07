<?php
declare(strict_types=1);

namespace App\Services\SMSGatewayService;

use App\Helpers\EnvironmentPolicy;
use App\Models\SmsGateway;
use App\Services\CoreService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;
use Vonage\Client;
use Vonage\Client\Credentials\Basic;
use Vonage\SMS\Message\SMS;

class NexmoService extends CoreService
{

    protected function getModelClass(): string
    {
        return SmsGateway::class;
    }

    public function sendSms($gateway, $phone, $otp): array
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

        $apiKey = $gateway->api_key ?: config('services.vonage.api_key');
        $apiSecret = $gateway->secret_key ?: config('services.vonage.api_secret');
        $from = $gateway->from ?: config('services.vonage.from');

        if (!$apiKey || !$apiSecret || !$from) {
            return [
                'status' => false,
                'message' => 'SMS provider configuration is unavailable.',
            ];
        }

        $basic  = new Basic($apiKey, $apiSecret);
        $client = new Client($basic);
        $text   = Str::replace('#OTP#', $otp['otpCode'], $gateway->text);

        try {
            $response = $client
                ->sms()
                ->send(new SMS($phone, $from, $text))
                ->current();

            $status = $response->getStatus();

            return ['status' => $status == 0, 'message' => $status];
        } catch (Throwable $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }

    }
}
