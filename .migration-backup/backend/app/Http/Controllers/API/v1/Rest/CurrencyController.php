<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Rest;

use App\Helpers\ResponseError;
use App\Http\Resources\CurrencyResource;
use App\Models\Currency;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrencyController extends RestBaseController
{

    /**
     * Optional shop_id makes this shop-aware: the resolved currency for
     * the shop's own country (see Shop::displayCurrency()) is flagged as
     * the effective default in the response, purely in-memory — never
     * written back to the DB row or the shared currenciesList() cache —
     * so sellers/moderators see their shop's country currency instead of
     * the platform-wide default. No shop_id (or no resolvable currency)
     * falls back to the unchanged platform-wide response.
     */
    public function index(Request $request): JsonResponse
    {
        $currencies = Currency::currenciesList();

        $displayCurrency = $request->input('shop_id')
            ? Shop::find($request->input('shop_id'))?->displayCurrency()
            : null;

        if ($displayCurrency) {
            $currencies = $currencies->map(function (Currency $currency) use ($displayCurrency) {
                $clone = clone $currency;
                $clone->default = $clone->id === $displayCurrency->id;

                return $clone;
            });
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            CurrencyResource::collection($currencies)
        );
    }

    /**
     * Get all Active languages
     * @return JsonResponse
     */
    public function active(): JsonResponse
    {
        $currencies = Currency::currenciesList()->where('active', 1);

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            CurrencyResource::collection($currencies)
        );
    }
}
