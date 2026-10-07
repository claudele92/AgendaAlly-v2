<?php
declare(strict_types=1);
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

final class ExplicitApiBearer
{
    public function handle(Request $request, Closure $next)
    {
        // An explicit API credential must not be overridden by an ambient web
        // session belonging to another role/account in the same browser.
        $original = config('sanctum.guard');
        if ($request->bearerToken()) config(['sanctum.guard' => []]);
        try { return $next($request); }
        finally { config(['sanctum.guard' => $original]); }
    }
}