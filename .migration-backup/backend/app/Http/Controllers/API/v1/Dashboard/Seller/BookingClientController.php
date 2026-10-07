<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Http\Resources\SellerBookingClientResource;
use App\Models\Invitation;
use App\Models\SellerBookingClient;
use App\Models\ShopLocation;
use App\Models\User;
use App\Support\SellerBookingClientIdentityMatcher;
use App\Support\SellerClientSaveIntent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingClientController extends SellerBaseController
{
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $limit = min(max((int) $request->query('perPage', 10), 1), 50);
        $scope = auth('sanctum')->user()->bookingBranchScope((int) $this->shop->id);
        $locationId = $request->query('shop_location_id')
            ? $this->authorizedLocation((int) $request->query('shop_location_id'), $scope)
            : null;

        $registered = $this->registeredClients($scope, $locationId)
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('firstname', 'like', "%{$search}%")
                    ->orWhere('lastname', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->orderBy('firstname')
            ->limit($limit)
            ->get(['users.id', 'users.firstname', 'users.lastname', 'users.phone', 'users.email']);

        $local = $this->localClients($scope, $locationId)
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name', 'phone', 'email', 'shop_location_id']);

        $results = $registered
            ->map(fn (User $user) => SellerBookingClientResource::make($user)->resolve())
            ->concat($local->map(fn (SellerBookingClient $client) => SellerBookingClientResource::make($client)->resolve()))
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->take($limit)
            ->values();

        return response()->json(['data' => $results]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'phone' => ['nullable', 'string', 'max:64'],
            'email' => ['nullable', 'email', 'max:191'],
            'shop_location_id' => ['nullable', 'integer'],
            'client_save_intent' => ['nullable', 'uuid'],
        ]);
        $data['name'] = SellerBookingClient::normalizeName($data['name']);

        $shopId = (int) $this->shop->id;
        $actor = auth('sanctum')->user();
        $scope = $actor->bookingBranchScope($shopId);
        $locationId = isset($data['shop_location_id']) ? (int) $data['shop_location_id'] : null;

        if ($locationId !== null) {
            $this->authorizedLocation($locationId, $scope);
        } elseif (!$scope['unrestricted']) {
            if (count($scope['location_ids']) !== 1) {
                throw ValidationException::withMessages([
                    'shop_location_id' => ['Select one of your assigned service branches before adding a client.'],
                ]);
            }
            $locationId = (int) $scope['location_ids'][0];
        }

        $email = SellerBookingClient::normalizeEmail($data['email'] ?? null);
        $phone = SellerBookingClient::normalizePhone($data['phone'] ?? null);

        $create = function () use ($data, $shopId, $locationId, $scope, $email, $phone) {
                $duplicates = $this->findDuplicates($scope, $email, $phone, $locationId);
                $duplicate = SellerBookingClientIdentityMatcher::singleOrConflict($duplicates);
                if ($duplicate !== null) {
                    return response()->json([
                        'data' => $duplicate['resource'],
                        'reused_existing' => true,
                    ]);
                }

                $client = SellerBookingClient::create([
                    'shop_id' => $shopId,
                    'shop_location_id' => $locationId,
                    'dedupe_scope' => $locationId === null ? 'shop' : "location:{$locationId}",
                    'name' => $data['name'],
                    'phone' => trim((string) ($data['phone'] ?? '')) ?: null,
                    'email' => $email,
                    'normalized_phone' => $phone,
                    'normalized_email' => $email,
                ]);

                return response()->json([
                    'data' => SellerBookingClientResource::make($client)->resolve(),
                    'reused_existing' => false,
                ], 201);
        };
        // Legacy callers retain their contact-based contract. The revised UI always
        // supplies an intent; never manufacture retry identity from a person's name.
        for ($attempt = 0; $attempt < 2; $attempt++) {
            try {
                if (!empty($data['client_save_intent'])) {
                    return SellerClientSaveIntent::execute(
                        (int) $actor->id, $shopId, $data['client_save_intent'],
                        ['name' => $data['name'], 'phone' => $phone, 'email' => $email, 'shop_location_id' => $locationId],
                        $create,
                        fn ($intent) => $this->resolveIntent($intent, $scope)
                    );
                }
                return DB::transaction($create, 3);
            } catch (QueryException $error) {
                if (!SellerClientSaveIntent::isUniqueViolation($error)) {
                    throw $error;
                }
                if ($attempt === 1) {
                    throw ValidationException::withMessages([
                        'phone' => ['A client with this contact information was added concurrently. Search existing clients and select the authorized match.'],
                    ]);
                }
                // A concurrent contact insert rolled back both our row and receipt.
                // Retry in a fresh transaction so native authorized contact reuse wins.
            }
        }
    }

    public function saveIntent(Request $request, string $intentKey): JsonResponse
    {
        validator(['intent' => $intentKey], ['intent' => ['required', 'uuid']])->validate();
        $actor = auth('sanctum')->user();
        $scope = $actor->bookingBranchScope((int) $this->shop->id);
        $intent = DB::table(SellerClientSaveIntent::TABLE)
            ->where(SellerClientSaveIntent::authority((int) $actor->id, (int) $this->shop->id, $intentKey))
            ->first();
        abort_if($intent === null, 404, 'Client-save reference not found. An in-flight save may still be unconfirmed.');
        return $this->resolveIntent($intent, $scope, true);
    }

    private function resolveIntent(object $intent, array $scope, bool $statusLookup = false): JsonResponse
    {
        $locationId = $intent->shop_location_id === null ? null : (int) $intent->shop_location_id;
        if ($locationId !== null) {
            $this->authorizedLocation($locationId, $scope);
        } else {
            abort_unless($scope['unrestricted'], 404);
        }
        abort_unless($intent->result_id !== null && in_array($intent->result_kind, ['local', 'registered'], true), 409,
            'Client-save outcome is unresolved. Retry the same save reference.');
        $query = $intent->result_kind === 'local'
            ? $this->localClients($scope, $locationId)
            : $this->registeredClients($scope, $locationId);
        $client = $query->find($intent->result_id);
        abort_if($client === null, 409, 'The saved client is no longer available. This reference will not create another client.');
        return response()->json([
            'data' => SellerBookingClientResource::make($client)->resolve(),
            'reused_existing' => (bool) $intent->reused_existing,
            'client_save_intent' => $intent->intent_key,
            'save_status' => 'resolved',
        ], $statusLookup ? 200 : (int) $intent->response_status);
    }

    /**
     * A registered account is discoverable only if it has previously booked
     * at this shop as a customer and has no internal shop/system role.
     */
    private function registeredClients(array $scope, ?int $locationId = null)
    {
        $shopId = (int) $this->shop->id;

        return User::query()
            ->whereHas('roles', fn ($query) => $query->where('name', 'user'))
            ->whereDoesntHave('roles', fn ($query) => $query->where('name', '!=', 'user'))
            ->whereDoesntHave('shop')
            ->whereDoesntHave('serviceMasters', fn ($query) => $query->where('shop_id', $shopId))
            ->whereDoesntHave('invitations', fn ($query) => $query
                ->where('shop_id', $shopId)
                ->where('status', Invitation::ACCEPTED))
            ->whereHas('bookings', function ($query) use ($shopId, $scope) {
                $query->where('shop_id', $shopId)
                    ->whereNotNull('user_id')
                    ->when(!$scope['unrestricted'], function ($query) use ($scope) {
                        if ($scope['location_ids'] === []) {
                            $query->whereRaw('1 = 0');
                        } else {
                            $query->whereIn('shop_location_id', $scope['location_ids']);
                        }
                    });
            })
            ->when($locationId !== null, fn ($query) => $query->whereHas('bookings', fn ($bookings) => $bookings
                ->where('shop_id', $shopId)->where('shop_location_id', $locationId)));
    }

    private function localClients(array $scope, ?int $locationId = null)
    {
        return SellerBookingClient::query()
            ->where('shop_id', (int) $this->shop->id)
            ->when($locationId !== null, fn ($query) => $query->where(function ($query) use ($locationId, $scope) {
                $query->where('shop_location_id', $locationId);
                if ($scope['unrestricted']) {
                    $query->orWhereNull('shop_location_id');
                }
            }))
            ->when(!$scope['unrestricted'], function ($query) use ($scope) {
                if ($scope['location_ids'] === []) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->whereIn('shop_location_id', $scope['location_ids']);
                }
            });
    }

    private function authorizedLocation(int $locationId, array $scope): int
    {
        $valid = ShopLocation::query()
            ->where('id', $locationId)
            ->where('shop_id', (int) $this->shop->id)
            ->where('type', ShopLocation::SERVICE)
            ->exists();
        if (!$valid || (!$scope['unrestricted'] && !in_array($locationId, $scope['location_ids'], true))) {
            throw ValidationException::withMessages([
                'shop_location_id' => ['Select a service branch you are authorized to manage.'],
            ]);
        }

        return $locationId;
    }

    /**
     * Return only clients visible in the selected branch. If phone and email
     * match separate identities, the caller must resolve that conflict rather
     * than selecting an arbitrary record.
     */
    private function findDuplicates(array $scope, ?string $email, ?string $phone, ?int $locationId): \Illuminate\Support\Collection
    {
        $matches = collect();
        if ($email === null && $phone === null) {
            return $matches;
        }

        $registered = $this->registeredClients($scope, $locationId)
            ->where(function ($query) use ($email, $phone) {
                if ($email !== null) {
                    $query->whereRaw('LOWER(TRIM(COALESCE(users.email, \'\'))) = ?', [$email]);
                }
                if ($phone !== null) {
                    $method = $email !== null ? 'orWhere' : 'where';
                    $query->{$method}('users.phone', 'like', '%' . substr($phone, -7) . '%');
                }
            })
            ->get(['users.id', 'users.firstname', 'users.lastname', 'users.phone', 'users.email'])
            ->filter(fn (User $user) =>
                ($email !== null && SellerBookingClient::normalizeEmail($user->email) === $email)
                || ($phone !== null && SellerBookingClient::normalizePhone($user->phone) === $phone)
            );

        foreach ($registered as $user) {
            $matches->put('registered:' . $user->id, [
                'resource' => SellerBookingClientResource::make($user)->resolve(),
            ]);
        }

        $local = $this->localClients($scope, $locationId)
            ->where(function ($query) use ($email, $phone) {
                if ($email !== null) {
                    $query->where('normalized_email', $email);
                }
                if ($phone !== null) {
                    $method = $email !== null ? 'orWhere' : 'where';
                    $query->{$method}('normalized_phone', $phone);
                }
            })
            ->get();

        foreach ($local as $client) {
            if (($email !== null && $client->normalized_email === $email)
                || ($phone !== null && $client->normalized_phone === $phone)) {
                $matches->put('local:' . $client->id, [
                    'resource' => SellerBookingClientResource::make($client)->resolve(),
                ]);
            }
        }

        return $matches->values();
    }
}