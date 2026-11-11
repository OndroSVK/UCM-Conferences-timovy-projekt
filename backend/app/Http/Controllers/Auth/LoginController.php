<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\HomeController;
use App\Modules\Auth\Actions\AuthenticateUser;
use App\Modules\Auth\Actions\ResolvePostLoginDestination;
use App\Modules\Auth\Enums\LoginDestination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** One login for participants and admins; redirect by role (R11, R17, US-02). */
final class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $request, AuthenticateUser $authenticate)
    {
        ['user' => $user, 'destination' => $destination] = $authenticate->handle(
            $request->only(['email', 'password']),
            (string) $request->ip(),
        );

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route(HomeController::routeFor($destination)));
    }

    public function choose(Request $request, ResolvePostLoginDestination $resolve)
    {
        if ($resolve->handle($request->user()) !== LoginDestination::Choose) {
            return redirect()->route('home');
        }

        return view('auth.choose');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
