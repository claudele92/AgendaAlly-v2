<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Rest;

use Illuminate\Http\JsonResponse;

class InstallController extends RestBaseController
{
    /**
     * Legacy installer operations are intentionally disabled. Installation,
     * database reconfiguration, migrations, and bootstrap-admin creation
     * must be performed out of band by an operator.
     */
    public function checkInitFile(): JsonResponse
    {
        return $this->disabled();
    }

    public function setInitFile($request): JsonResponse
    {
        return $this->disabled();
    }

    public function setDatabase($request): JsonResponse
    {
        return $this->disabled();
    }

    public function createAdmin($request): JsonResponse
    {
        return $this->disabled();
    }

    public function migrationRun(): JsonResponse
    {
        return $this->disabled();
    }

    private function disabled(): JsonResponse
    {
        return new JsonResponse(['message' => 'Installer endpoints are disabled.'], 404);
    }
}
