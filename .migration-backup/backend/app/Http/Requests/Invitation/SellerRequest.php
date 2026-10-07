<?php
declare(strict_types=1);

namespace App\Http\Requests\Invitation;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class SellerRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $user   = auth('sanctum')->user();
        $shopId = $user?->shop?->id ?? $user?->moderatorShop?->id;

        return [
            'user_id' => [
                'required',
                'int',
                Rule::exists('users', 'id')
            ],
            // Fixed platform roles — unchanged, unrelated to the shop_roles
            // system below. 'shop_manager' is no longer a valid value here:
            // that category is now a seller-defined shop_role instead.
            'role' => [
                'required_without:shop_role_id',
                'string',
                Rule::in(['moderator', 'master'])
            ],
            // The seller's own previously-created role for this shop. Must
            // already exist — there is no default/auto-created role, so an
            // invite can't be sent for this category until the seller has
            // created at least one shop_role.
            'shop_role_id' => [
                'required_without:role',
                'integer',
                Rule::exists('shop_roles', 'id')->where('shop_id', $shopId),
            ],
            // Organizational/scheduling only - not wired into checkout
            // currency resolution (see Shop::checkoutCountry()). Optional:
            // not every staff member needs to be tied to a specific branch.
            // An array since one invitation can hold more than one branch
            // (e.g. the PRODUCT and SERVICE ShopLocation for the same city).
            'shop_location_ids'   => ['nullable', 'array'],
            'shop_location_ids.*' => [
                'integer',
                Rule::exists('shop_locations', 'id')->where('shop_id', $shopId),
            ],
            'shop_id' => ['prohibited'],
            'password' => ['prohibited'],
            'password_confirmation' => ['prohibited'],
            'email_verified_at' => ['prohibited'],
            'phone_verified_at' => ['prohibited'],
            'verified' => ['prohibited'],
        ];
    }
}
