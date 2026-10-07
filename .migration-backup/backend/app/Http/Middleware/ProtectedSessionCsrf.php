<?php
declare(strict_types=1);
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;

final class ProtectedSessionCsrf extends \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken
{
    public function handle($request, Closure $next)
    {
        if (!$request->is('api/v1/dashboard/*') || $request->bearerToken()) {
            return $next($request);
        }
        // If a protected route supports a web-session principal, preserve that
        // transport but require its CSRF proof for protected mutations.
        // Do not bypass this check in the isolated testing HTTP runtime.
        if (!$this->isReading($request) && Auth::guard('web')->check()
            && !$this->tokensMatch($request)) {
            throw new TokenMismatchException('CSRF token mismatch.');
        }
        return $this->isReading($request)
            ? parent::handle($request, $next)
            : $next($request);
    }
}