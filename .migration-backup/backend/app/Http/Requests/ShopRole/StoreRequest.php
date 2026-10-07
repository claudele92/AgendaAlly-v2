<?php

declare(strict_types=1);

namespace App\Http\Requests\ShopRole;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends BaseRequest
{
    public function rules(): array
    {
        $user   = auth('sanctum')->user();
        $shopId = $user?->shop?->id ?? $user?->moderatorShop?->id;

        $roleId = $this->route('role')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:191',
                Rule::unique('shop_roles', 'name')
                    ->where('shop_id', $shopId)
                    ->ignore($roleId),
            ],
            'permission_ids'    => ['required', 'array', 'min:1'],
            'permission_ids.*'  => ['integer', Rule::exists('shop_permissions', 'id')],
        ];
    }
}
