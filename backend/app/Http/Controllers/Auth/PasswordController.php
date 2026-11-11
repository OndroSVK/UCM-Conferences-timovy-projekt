<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Actions\ResetPassword;
use App\Modules\Auth\Actions\SendPasswordResetLink;
use Illuminate\Http\Request;

final class PasswordController extends Controller
{
    public function forgot()
    {
        return view('auth.forgot-password');
    }

    public function sendLink(Request $request, SendPasswordResetLink $send)
    {
        $send->handle($request->only('email'));

        // Same answer whether the account exists or not.
        return back()->with('status', __('ui.password.link_sent'));
    }

    public function showReset(Request $request, string $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => (string) $request->query('email')]);
    }

    public function reset(Request $request, ResetPassword $reset)
    {
        $reset->handle($request->only(['token', 'email', 'password', 'password_confirmation']));

        return redirect()->route('login')->with('status', __('ui.password.reset_done'));
    }
}
