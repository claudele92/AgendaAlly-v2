<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Exceptions\Handler;
use App\Traits\Loggable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class HandlerSecurityTest extends IsolatedTestCase
{
    private object $logger;
    private Handler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance('translator', new Translator(new ArrayLoader(), 'en'));
        $this->logger = new class {
            public array $entries = [];

            public function info(...$arguments): void
            {
                $this->entries[] = ['info', $arguments];
            }

            public function error(...$arguments): void
            {
                $this->entries[] = ['error', $arguments];
            }
        };
        Log::swap($this->logger);
        $this->handler = new Handler($this->app);
    }

    public function test_authentication_failure_never_logs_the_bearer_token(): void
    {
        $this->logger->entries = [];
        $token = 'synthetic-bearer-token-not-for-logs';
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $response = $this->handler->handleException($request, new AuthenticationException());

        self::assertSame(401, $response->getStatusCode());
        self::assertSame([], $this->logger->entries);
        self::assertStringNotContainsString($token, $response->getContent());
    }

    public function test_unexpected_and_server_errors_do_not_expose_exception_details(): void
    {
        $request = Request::create('/');
        $sensitiveDetails = 'secret card data at /srv/private/app/Payment.php';

        $unexpected = $this->handler->handleException($request, new \RuntimeException($sensitiveDetails, 73));
        $serverError = $this->handler->handleException($request, new HttpException(503, $sensitiveDetails));

        foreach ([$unexpected, $serverError] as $response) {
            self::assertSame('Internal server error', $response->getData(true)['message']);
            self::assertStringNotContainsString($sensitiveDetails, $response->getContent());
        }

        self::assertSame(500, $unexpected->getStatusCode());
        self::assertSame(503, $serverError->getStatusCode());
        self::assertSame('500', $unexpected->getData(true)['statusCode']);
        self::assertSame('503', $serverError->getData(true)['statusCode']);
    }

    public function test_client_http_error_keeps_existing_response_contract(): void
    {
        $response = $this->handler->handleException(
            Request::create('/'),
            new HttpException(403, 'Forbidden')
        );

        self::assertSame(403, $response->getStatusCode());
        self::assertSame([
            'status' => false,
            'statusCode' => '403',
            'message' => 'Forbidden',
        ], array_intersect_key(
            $response->getData(true),
            array_flip(['status', 'statusCode', 'message'])
        ));
    }

    public function test_exception_logging_omits_message_code_and_source_path(): void
    {
        $secret = 'synthetic secret payment token in exception';
        $exception = new \RuntimeException($secret, 9876);
        $logger = new class {
            use Loggable;
        };

        $this->logger->entries = [];
        $logger->error($exception);

        self::assertCount(1, $this->logger->entries);
        self::assertSame('error', $this->logger->entries[0][0]);
        self::assertSame('An application exception was reported.', $this->logger->entries[0][1][0]);
        self::assertSame(['exception_class' => \RuntimeException::class], $this->logger->entries[0][1][1]);
        self::assertStringNotContainsString($secret, json_encode($this->logger->entries));
        self::assertStringNotContainsString($exception->getFile(), json_encode($this->logger->entries));
        self::assertStringNotContainsString('9876', json_encode($this->logger->entries));
    }
}