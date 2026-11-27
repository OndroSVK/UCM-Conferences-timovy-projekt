<?php

use App\Http\Middleware\ResolveConferenceFromRequestHost;
use App\Http\Middleware\UseAdminConference;
use App\Http\Middleware\UseOwnerContext;
use App\Shared\Actions\DomainException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'conference.host' => ResolveConferenceFromRequestHost::class,
            'conference.owner' => UseOwnerContext::class,
            'conference.admin' => UseAdminConference::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('home'));
    })
    ->withCommands([
        __DIR__.'/../app/Console/Commands',
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // A refused business rule is shown to the user next to the form, not as an error page.
        $exceptions->render(function (DomainException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->withInput($request->except(['password', 'password_confirmation', 'file']))
                ->withErrors(['domain' => $e->getMessage()]);
        });
    })->create();
