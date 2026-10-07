<?php
declare(strict_types=1);

namespace App\Http\Requests\Invitation;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class InviteUpdateRequest extends BaseRequest
{
    /**
     * Editing an existing invitation only ever changes which shop_role or
     * branch(es) (shop_locations) it's tied to - user_id and the platform
     * role are not editable here. See Seller\InviteController::update().
     *
     * @return array
     */
    public function rules(): array
    {
        $user   = auth('sanctum')->user();
        $shopId = $user?->shop?->id ?? $user?->moderatorShop?->id;

        return [
            'shop_role_id' => [
                'nullable',
                'integer',
                Rule::exists('shop_roles', 'id')->where('shop_id', $shopId),
            ],
            // An array since one invitation can hold more than one branch
            // (e.g. the PRODUCT and SERVICE ShopLocation for the same city).
            'shop_location_ids'   => ['nullable', 'array'],
            'shop_location_ids.*' => [
                'integer',
                Rule::exists('shop_locations', 'id')->where('shop_id', $shopId),
            ],
            'shop_id' => ['prohibited'],
            'user_id' => ['prohibited'],
            'invitation_id' => ['prohibited'],
            'role' => ['prohibited'],
            'password' => ['prohibited'],
            'email_verified_at' => ['prohibited'],
            'phone_verified_at' => ['prohibited'],
        ];
    }
}
