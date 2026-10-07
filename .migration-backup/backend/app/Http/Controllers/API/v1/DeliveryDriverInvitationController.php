<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeliveryDriver\InvitationCreateRequest;
use App\Http\Requests\DeliveryDriver\InvitationRegistrationRequest;
use App\Http\Requests\DeliveryDriver\InvitationStatusRequest;
use App\Http\Requests\DeliveryDriver\InvitationTokenRequest;
use App\Models\Shop;
use App\Services\DeliveryDriver\DriverInvitationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Backend endpoints shared by native Vendor Driver management and the
 * invitation-only public onboarding page.
 */
final class DeliveryDriverInvitationController extends Controller
{
    use ApiResponse;

    private ?Shop $shop;

    public function __construct(private DriverInvitationService $service)
    {
        parent::__construct();
        $user = auth('sanctum')->user();
        $this->shop = $user?->shop ?? $user?->moderatorShop;
    }

    public function index(Request $request): JsonResponse
    {
        $page = $this->service->listForShop(
            $this->shopId(),
            max(1, min((int) $request->input('perPage', 15), 100))
        );

        return response()->json([
            'data' => $page->items(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'per_page' => $page->perPage(),
                'last_page' => $page->lastPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    public function store(InvitationCreateRequest $request): JsonResponse
    {
        $result = $this->service->create(
            $this->shopId(),
            (int) auth('sanctum')->id(),
            $request->validated()
        );

        return $this->successResponse('Delivery-driver invitation created.', [
            ...$this->service->serialize($result['invitation']),
            'invitation_link' => $this->invitationLink($result['token']),
            'notification_status' => $result['notification_status'],
        ]);
    }

    public function resend(int $id): JsonResponse
    {
        $result = $this->service->resend($this->shopId(), $id);

        return $this->successResponse('Delivery-driver invitation rotated.', [
            ...$this->service->serialize($result['invitation']),
            'invitation_link' => $this->invitationLink($result['token']),
            'notification_status' => $result['notification_status'],
        ]);
    }

    public function revoke(int $id): JsonResponse
    {
        $invitation = $this->service->revoke($this->shopId(), $id);

        return $this->successResponse('Delivery-driver invitation revoked.', [
            ...$this->service->serialize($invitation),
        ]);
    }

    public function status(int $id, InvitationStatusRequest $request): JsonResponse
    {
        $invitation = $this->service->setActive(
            $this->shopId(),
            $id,
            (bool) $request->validated('active')
        );

        return $this->successResponse('Delivery-driver relationship updated.', [
            ...$this->service->serialize($invitation),
        ]);
    }

    public function preview(InvitationTokenRequest $request): JsonResponse
    {
        return $this->successResponse(
            'Invitation preview.',
            $this->service->preview($request->validated('token'))
        );
    }

    public function register(InvitationRegistrationRequest $request): JsonResponse
    {
        $data = $this->service->register(
            $request->validated('token'),
            $request->validated()
        );

        return $this->successResponse('Verify your email before accepting this invitation.', $data);
    }

    public function accept(InvitationTokenRequest $request): JsonResponse
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            throw new AccessDeniedHttpException('Sign in to accept this invitation.');
        }

        $invitation = $this->service->accept($user, $request->validated('token'));

        return $this->successResponse('Delivery-driver invitation accepted.', [
            ...$this->service->serialize($invitation),
        ]);
    }

    public function decline(InvitationTokenRequest $request): JsonResponse
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            throw new AccessDeniedHttpException('Sign in to respond to this invitation.');
        }

        $invitation = $this->service->decline($user, $request->validated('token'));

        return $this->successResponse('Delivery-driver invitation declined.', [
            ...$this->service->serialize($invitation),
        ]);
    }

    private function invitationLink(string $token): string
    {
        // Fragment secrets are not sent in HTTP requests, referrer headers,
        // or ordinary access logs. Native frontend resolves this relative path.
        return '/delivery-driver-invitation#token=' . $token;
    }

    private function shopId(): int
    {
        if (!$this->shop) {
            throw new ForbiddenHttpException('A Vendor shop is required for Driver management.');
        }

        return (int) $this->shop->id;
    }
}