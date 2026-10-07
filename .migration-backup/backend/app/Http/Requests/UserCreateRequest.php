<?php
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UserCreateRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'email' => [
                'email',
                Rule::unique('users', 'email')->ignore(request()->route('user'), 'uuid')
            ],
            'phone' => [
                'numeric',
                Rule::unique('users', 'phone')->ignore(request()->route('user'), 'uuid')
            ],
            'shop_id'                         => 'array',
            'shop_id.*'                       => 'integer|exists:shops,id',
            'role'                            => ['string', 'exists:roles,name'],
            'shop_role_id'                    => [
                'nullable',
                'integer',
                Rule::exists('shop_roles', 'id')->where(
                    'shop_id',
                    auth('sanctum')->user()?->shop?->id ?? auth('sanctum')->user()?->moderatorShop?->id
                ),
            ],
            // Same optional, organizational-only branch assignment as
            // Invitation\SellerRequest — this request creates the master's
            // invitation inline too (see UserService::create()), so it
            // needs the identical rule to actually persist one. An array
            // since one invitation can hold more than one branch (e.g. the
            // PRODUCT and SERVICE ShopLocation for the same city).
            'shop_location_ids'                => ['nullable', 'array'],
            'shop_location_ids.*'              => [
                'integer',
                Rule::exists('shop_locations', 'id')->where(
                    'shop_id',
                    auth('sanctum')->user()?->shop?->id ?? auth('sanctum')->user()?->moderatorShop?->id
                ),
            ],
            'lastname'                        => ['string'],
            'birthday'                        => ['date_format:Y-m-d'],
            'firebase_token'                  => ['string'],
            'firstname'                       => ['required', 'string', 'min:2', 'max:100'],
            'gender'                          => ['string', Rule::in('male','female')],
            'active'                          => ['numeric', Rule::in(1,0)],
            'subscribe'                       => 'boolean',
            'notifications'                   => 'array',
            'notifications.*.notification_id' => ['required', 'int', Rule::exists('notifications', 'id')],
            'notifications.*.active'          => 'boolean',
            'password'                        => ['min:6', 'confirmed'],
            'referral'                        => 'string|exists:users,my_referral|max:255',
            'images'                          => 'array',
            'images.*'                        => 'string',
            'currency_id'                     => 'integer|exists:currencies,id',
            'lang'                            => 'string',
            'title'                           => 'array',
            'title.*'                         => 'string|min:2|max:191',
            'description'                     => 'array',
            'description.*'                   => 'string|min:3',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $actor = auth('sanctum')->user();
            if (!$actor || $actor->hasRole('admin')) {
                return;
            }

            $shopId = $actor->shop?->id ?? $actor->moderatorShop?->id;
            if ($this->input('role') === 'deliveryman') {
                $validator->errors()->add('role', 'Use the dedicated delivery-driver invitation flow.');
                foreach (['password', 'email_verified_at', 'phone_verified_at', 'verified'] as $field) {
                    if ($this->exists($field)) {
                        $validator->errors()->add($field, 'Vendor-created Driver credentials and verification are prohibited.');
                    }
                }
            }

            if ($this->exists('shop_id')) {
                $submitted = $this->input('shop_id');
                $submittedIds = is_array($submitted) ? $submitted : [$submitted];
                if (!$shopId || count($submittedIds) !== 1 || (int) $submittedIds[0] !== (int) $shopId) {
                    $validator->errors()->add('shop_id', 'A Vendor may only submit their own shop identifier.');
                }
            }
        });
    }
}
