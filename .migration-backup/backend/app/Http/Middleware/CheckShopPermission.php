<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Helpers\ResponseError;
use App\Models\User;
use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fine-grained authorization on top of the coarse `role:seller|moderator|
 * shop_manager|admin` route-group gate: does this user hold a permission
 * (e.g. 'bookings.view') for the shop they're operating on? Independent of
 * Spatie's role/permission tables entirely — see the shop_roles/
 * shop_permissions tables and User::hasShopPermission().
 *
 * The shop's owner always passes, unconditionally — see
 * User::hasShopPermission() for why that can never lock the owner out.
 */
class CheckShopPermission
{
    use ApiResponse;

    public function handle(Request $request, Closure $next, string $permission): mixed
    {
        /** @var User|null $user */
        $user = auth('sanctum')->user();
        $shop = $user?->shop ?? $user?->moderatorShop;

        if (!$shop || !$user->hasShopPermission($shop->id, $permission)) {
            return $this->errorResponse(
                ResponseError::ERROR_101,
                __('errors.' . ResponseError::ERROR_101, locale: request('lang', 'en')),
                Response::HTTP_FORBIDDEN
            );
        }

        return $next($request);
    }
}
