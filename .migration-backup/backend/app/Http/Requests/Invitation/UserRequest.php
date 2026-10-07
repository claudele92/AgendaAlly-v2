<?php
declare(strict_types=1);

namespace App\Http\Requests\Invitation;

use App\Models\Shop;
use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UserRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $shopId = Shop::query()->where('uuid', $this->route('uuid'))->value('id');

        return [
            'role' => [
                'required_without:shop_role_id',
                'string',
                Rule::in(['moderator', 'master', 'deliveryman'])
            ],
            'shop_role_id' => [
                'required_without:role',
                'integer',
                Rule::exists('shop_roles', 'id')->where('shop_id', $shopId),
            ],
        ];
    }
}
