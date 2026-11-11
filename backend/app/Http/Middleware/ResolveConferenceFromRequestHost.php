<?php

namespace App\Http\Middleware;

use App\Modules\Conferences\Actions\ResolveConferenceFromHost;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Public part: conference from the subdomain (ans2027.ucm.sk / ans2027.localhost).
 * Unknown subdomain → 404 (thrown by the Action).
 */
final class ResolveConferenceFromRequestHost
{
    public function __construct(private readonly ResolveConferenceFromHost $resolve)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $request->attributes->set('conference', $this->resolve->handle($request->getHost()));

        return $next($request);
    }
}
