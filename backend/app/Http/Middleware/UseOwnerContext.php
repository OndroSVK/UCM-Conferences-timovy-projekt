<?php

namespace App\Http\Middleware;

use App\Shared\Conference\ConferenceContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * User page: own registrations across all conferences, regardless of the subdomain (BO7).
 */
final class UseOwnerContext
{
    public function __construct(private readonly ConferenceContext $context)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $this->context->setOwner((int) $request->user()->getAuthIdentifier());

        return $next($request);
    }
}
