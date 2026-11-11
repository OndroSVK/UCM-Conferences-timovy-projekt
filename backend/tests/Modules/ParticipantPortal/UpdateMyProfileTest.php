<?php

use App\Modules\ParticipantPortal\Actions\UpdateMyProfile;
use App\Modules\Registration\Models\Registration;
use Illuminate\Support\Facades\Notification;

it('updates the profile and name; a new e-mail must be verified again; billing data stay', function () {
    Notification::fake();
    $conference = makeConference(2027);
    ['user' => $user, 'registration' => $registration] = registerParticipant($conference, 'speaker', ['billing_name' => 'Old Billing']);

    $profile = app(UpdateMyProfile::class)->handle($user, [
        'first_name' => 'Jana',
        'last_name' => 'Nová',
        'organization' => 'Other University',
        'email' => 'jana.nova@example.test',
    ]);

    inConference($conference);
    expect($profile->organization)->toBe('Other University')
        ->and($user->fresh()->name)->toBe('Jana Nová')
        ->and($user->fresh()->email)->toBe('jana.nova@example.test')
        ->and($user->fresh()->hasVerifiedEmail())->toBeFalse()
        ->and(Registration::query()->find($registration->id)->billing_name)->toBe('Old Billing');
});
