<?php
declare(strict_types=1);

namespace App\Traits;

use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait OnResponse
{
    /**
     * @param array $result = ['code' => 200]
     * @return JsonResponse
     */
    public function onErrorResponse(array $result = []): JsonResponse
    {
        $code = $result['code'] ?? ResponseError::ERROR_101;

        $httpDefault = $code === ResponseError::ERROR_404 ? Response::HTTP_NOT_FOUND : Response::HTTP_BAD_REQUEST;

        $http = $result['http'] ?? $httpDefault;

        $data = $result['data'] ?? [];

        $locale = property_exists($this, 'language') ? $this->language : 'en';
        // Validation/detail payloads are not translation replacements.
        // Laravel's string replacer cannot accept nested arrays.
        $replacements = is_array($data)
            ? array_filter($data, static fn ($value) => is_scalar($value))
            : [];

        return $this->errorResponse(
            (string)$code,
            (string)($result['message'] ?? __("errors.$code", $replacements, locale: $locale)),
            (int)$http
        );
    }
}
