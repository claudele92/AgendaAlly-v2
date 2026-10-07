<?php
declare(strict_types=1);

namespace App\Services\AiTranslationService;

use DB;
use Http;
use Exception;
use App\Models\Shop;
use App\Models\AILog;
use App\Models\Settings;
use App\Models\Language;
use Illuminate\Support\Str;
use App\Services\CoreService;
use App\Helpers\ResponseError;

class AiTranslationService extends CoreService
{
    protected string $apiUrl = 'https://api.openai.com/v1/chat/completions';

    protected function getModelClass(): string
    {
        return AILog::class;
    }

    /**
     * @param array $data
     * @param Shop|null $shop
     * @return array
     * @throws Exception
     */
    public function translate(array $data, ?Shop $shop = null): array
    {
        $aiActive = Settings::where('key', 'ai_active')->value('value');
        $modelType = $data['model_type'];

        if (!$aiActive || empty(config('app.open_ai_token'))) {
            return [
                'status' => false,
                'message' => __('errors.' . ResponseError::ERROR_438, locale: $this->language),
                'code' => ResponseError::ERROR_438
            ];
        }

        if (!class_exists('App\\Models\\' . Str::ucfirst($data['model_type']))) {
            return [
                'status' => false,
                'message' => __('errors.' . ResponseError::ERROR_437, locale: $this->language),
                'code' => ResponseError::ERROR_437
            ];
        }

        if (!empty($shop) && !$shop->ai_access) {
            return [
                'status' => false,
                'message' => __('errors.' . ResponseError::ERROR_438, locale: $this->language),
                'code' => ResponseError::ERROR_438
            ];
        }

        $text = $data['content'];

        $locale = $data['lang'];
        $locales = Language::where('active', true)->implode('locale', ', ');

        $prompt = <<<PROMPT
        You're a creative and professional copywriter.
        Transform "$text" into a concise, professional title and description for your $modelType in an online e-commerce, just like Shopify, Amazon, and Alibaba.

        CRITICAL INSTRUCTION:
        - The output must be 100% in $locale — this is mandatory.
        - The title should be catchy and engaging, minimum 5 characters and maximum 60 characters.
        - The description should be informative and persuasive, minimum 50 characters and maximum 260 characters.
        - Translate the title and description into ALL active languages in the system: $locales.
        - All language keys MUST exactly match: $locales.
        - Missing or extra keys are forbidden.

        OUTPUT FORMAT:
        - For EACH language, return a structured object in the following format:
        {
            "lang_code": {
                "title": "Translated Title",
                "description": "Translated Description"
            },
            ...
        }

        - Field keys must remain unchanged; only values are translated or rewritten.
        — No explanations, no comments, no metadata.— Output must be clean, valid JSON and ready for direct database insertion.
        PROMPT;

        if (!empty($shop) && $shop->ai_token_limit > 0) {

            $sumToken = DB::table('ai_logs')
                ->select([
                    DB::raw('sum(total_token) as total_token')
                ])
                ->where('user_id', $shop->user_id)
                ->value('total_token') + strlen($prompt);

            if ($sumToken >= $shop->ai_token_limit) {
                return [
                    'status' => false,
                    'message' => __('errors.' . ResponseError::ERROR_439, locale: $this->language),
                    'code' => ResponseError::ERROR_439
                ];
            }

        }

        $response = Http::withToken(config('app.open_ai_token'))
            ->post($this->apiUrl, [
                'model' => 'gpt-4o',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a JSON API. You return ONLY valid JSON. No markdown. No text.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ],
                ],
                'temperature' => 0.2,
                'response_format' => [
                    'type' => 'json_object'
                ],
            ]);

        $success = in_array($response->status(), [200, 201]);

        if (!$success) {
            return [
                'status' => false,
                'message' => $response->json('error.message'),
                'code' => ResponseError::ERROR_400
            ];
        }

        $totalToken = $response->json('usage.total_tokens');

        $result = $response->json('choices.0.message.content');

        if (!is_array($result)) {
            $result = json_decode($result, true);
        }

        AILog::create([
            'model_id'    => $data['model_id'] ?? null,
            'model_type'  => $data['model_type'],
            'user_id'     => auth('sanctum')->id(),
            'locale'      => $locale,
            'status'      => $response->status(),
            'total_token' => $totalToken,
            'content'     => [
                'body'     => $data['content'],
                'response' => $response->json(),
                'result'   => $result
            ]
        ]);

        return [
            'status' => true,
            'data'   => $result
        ];
    }
}
