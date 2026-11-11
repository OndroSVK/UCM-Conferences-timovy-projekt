<?php

namespace App\Http\Controllers;

use App\Modules\Auth\Actions\ResolvePostLoginDestination;
use App\Modules\Auth\Enums\LoginDestination;
use App\Modules\Conferences\Actions\GetRegistrationFormOptions;
use App\Modules\Conferences\Actions\ResolveConferenceFromHost;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class HomeController extends Controller
{
    /**
     * On a conference subdomain: short conference page with the registration entry.
     * Otherwise: the workspace of a logged-in user, or the login.
     * The main page of each conference stays a static site by IT (R22, R23).
     */
    public function __invoke(Request $request, ResolveConferenceFromHost $resolve, GetRegistrationFormOptions $options)
    {
        try {
            $conference = $resolve->handle($request->getHost());

            return view('public.conference', $options->handle($conference));
        } catch (NotFoundHttpException) {
            // not a conference host
        }

        if ($request->user() === null) {
            return redirect()->route('login');
        }

        return redirect()->route(self::routeFor(app(ResolvePostLoginDestination::class)->handle($request->user())));
    }

    public static function routeFor(LoginDestination $destination): string
    {
        return match ($destination) {
            LoginDestination::ParticipantPortal => 'account.index',
            LoginDestination::Admin => 'admin.registrations.index',
            LoginDestination::Choose => 'choose',
        };
    }
}
