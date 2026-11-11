<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Actions\VerifyEmail;
use App\Modules\Auth\Models\User;
use Illuminate\Http\Request;

final class VerificationController extends Controller
{
    /** Signed link from the e-mail; the logged-in user must be the owner. */
    public function verify(Request $request, VerifyEmail $verify, int $id, string $hash)
    {
        abort_unless((int) $request->user()->id === $id, 403);

        $verify->handle($request->user(), $hash);

        return redirect()->route('home')->with('status', __('ui.verification.done'));
    }

    public function resend(Request $request, VerifyEmail $verify)
    {
        /** @var User $user */
        $user = $request->user();
        $verify->resend($user);

        return back()->with('status', __('ui.verification.sent'));
    }
}
