<?php
declare(strict_types=1);

namespace App\Exceptions;

use App\Helpers\ResponseError;
use App\Traits\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponse;
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
        'token',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @param Throwable $e
     * @return void
     *
     *
     * @throws Throwable
     */

    public function report(Throwable $e): void
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Throwable $e
     * @return JsonResponse|Response
     *
     */
    public function render($request, Throwable $e): JsonResponse|Response
    {
        return $this->handleException($request, $e);
    }

    public function handleException($request, Throwable $exception): JsonResponse|Response
    {
        if ($exception instanceof HttpResponseException) {
            return $exception->getResponse();
        }
        if ($exception instanceof RouteNotFoundException || $exception instanceof NotFoundHttpException) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        if ($exception instanceof AuthenticationException) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_401, 'http' => Response::HTTP_UNAUTHORIZED]);
        }

        if ($exception instanceof ModelNotFoundException) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        if ($exception instanceof ValidationException) {

            $items = $exception->validator->errors()->getMessages();

            return $this->onErrorResponse(['code' => ResponseError::ERROR_400, 'data' => $items]);
        }

        if ($exception instanceof HttpExceptionInterface) {
            $status = $exception->getStatusCode();

            return $this->errorResponse(
                (string) $status,
                $status >= Response::HTTP_INTERNAL_SERVER_ERROR
                    ? 'Internal server error'
                    : $exception->getMessage(),
                $status
            )->withHeaders($exception->getHeaders());
        }

        return $this->errorResponse(
            (string) Response::HTTP_INTERNAL_SERVER_ERROR,
            'Internal server error',
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

}
