<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Helpers\CountryContext;
use App\Helpers\ResponseError;
use App\Models\User;
use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fine-grained authorization on top of the coarse `role:admin|manager`
 * route-group gate: does this user hold a permission (e.g. 'orders.view')
 * for the country they're operating on? Independent of Spatie's
 * role/permission tables entirely — mirrors CheckShopPermission exactly,
 * one level up (country_roles/country_permissions instead of shop_roles/
 * shop_permissions).
 *
 * A global superadmin always passes, unconditionally — see
 * User::isSuperAdmin()/hasCountryPermission() for why.
 */
class CheckCountryPermission
{
    use ApiResponse;

    public function handle(Request $request, Closure $next, string $permission): mixed
    {
        /** @var User|null $user */
        $user = auth('sanctum')->user();

        if ($user?->isSuperAdmin()) {
            return $next($request);
        }

        // Resolves both the assigned country admin and their accepted
        // staff — a plain `$user->countryAdmin?->country_id` lookup would
        // only cover the admin themselves and 403 every invited staff
        // member outright. See CountryContext for why.
        $countryId = CountryContext::restrictedCountryId();

        if (!$countryId || !$user->hasCountryPermission($countryId, $permission)) {
            return $this->errorResponse(
                ResponseError::ERROR_101,
                __('errors.' . ResponseError::ERROR_101, locale: request('lang', 'en')),
                Response::HTTP_FORBIDDEN
            );
        }

        return $next($request);
    }
}
