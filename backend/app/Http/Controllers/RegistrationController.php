<?php

namespace App\Http\Controllers;

use App\Modules\Auth\Actions\CheckEmailRegistered;
use App\Modules\Conferences\Actions\GetRegistrationFormOptions;
use App\Modules\Registration\Actions\RegisterExistingUserForConference;
use App\Modules\Registration\Actions\RegisterNewParticipant;
use App\Modules\Registration\Models\ParticipantProfile;
use App\Modules\Registration\Models\Registration;
use App\Shared\Conference\ConferenceContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Registration on the conference subdomain (US-03, US-04). */
final class RegistrationController extends Controller
{
    public function show(Request $request, GetRegistrationFormOptions $options, ConferenceContext $context)
    {
        $conference = $request->attributes->get('conference');
        $user = $request->user();
        $profile = $user === null ? null : $context->runAsOwner($user->id,
            fn () => ParticipantProfile::query()->where('user_id', $user->id)->first());

        $alreadyRegistered = $user !== null
            && Registration::query()->where('user_id', $user->id)->exists(); // scoped to this conference

        return view('public.register', $options->handle($conference) + [
            'user' => $user,
            'hasProfile' => $profile !== null,
            'profile' => $profile,
            'alreadyRegistered' => $alreadyRegistered,
        ]);
    }

    public function store(Request $request, RegisterNewParticipant $register)
    {
        ['user' => $user, 'registration' => $registration] = $register->handle(
            $request->attributes->get('conference'),
            $request->all(),
        );

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('account.registrations.show', $registration->id)
            ->with('status', __('ui.registration.done'));
    }

    public function storeExisting(Request $request, RegisterExistingUserForConference $register)
    {
        $registration = $register->handle($request->user(), $request->attributes->get('conference'), $request->all());

        return redirect()->route('account.registrations.show', $registration->id)
            ->with('status', __('ui.registration.done'));
    }

    /** US-04: yes/no only, rate limited. */
    public function checkEmail(Request $request, CheckEmailRegistered $check)
    {
        return response()->json(['registered' => $check->handle($request->only('email'), (string) $request->ip())]);
    }
}
