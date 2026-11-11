<?php

use App\Modules\Auth\Models\User;
use App\Modules\Registration\Actions\RegisterExistingUserForConference;
use App\Modules\Registration\Models\Registration;
use App\Shared\Actions\DomainException;
use App\Shared\Auth\AccessControl;
use App\Shared\Auth\Role;
use Illuminate\Validation\ValidationException;

$registrationOnly = fn (array $override = []) => array_intersect_key(registrationInput($override), array_flip([
    'category', 'industry', 'billing_type', 'billing_name', 'billing_street', 'billing_city',
    'billing_postal_code', 'billing_country', 'data_processing_consent',
]));

// US-04
it('registers a logged-in participant for another conference without a new account', function () use ($registrationOnly) {
    $ans27 = makeConference(2027);
    $ans28 = makeConference(2028);
    ['user' => $user] = registerParticipant($ans27);

    $registration = app(RegisterExistingUserForConference::class)->handle($user, $ans28, $registrationOnly(['category' => 'passive_participant']));

    expect(User::query()->count())->toBe(1)
        ->and($registration->conference_id)->toBe($ans28->id)
        ->and($registration->variable_symbol)->toBe('28000001');
});

it('refuses a second registration for the same conference', function () use ($registrationOnly) {
    $ans27 = makeConference(2027);
    ['user' => $user] = registerParticipant($ans27);

    app(RegisterExistingUserForConference::class)->handle($user, $ans27, $registrationOnly());
})->throws(DomainException::class);

it('asks a staff member without profile for the profile and adds the participant role', function () use ($registrationOnly) {
    $ans27 = makeConference(2027);
    $organizer = staff($ans27, Role::Organizer);

    expect(fn () => app(RegisterExistingUserForConference::class)->handle($organizer, $ans27, $registrationOnly()))
        ->toThrow(ValidationException::class);

    app(RegisterExistingUserForConference::class)->handle($organizer, $ans27, $registrationOnly() + [
        'first_name' => 'Org', 'last_name' => 'Anizer', 'organization' => 'Example University',
    ]);

    expect(app(AccessControl::class)->isParticipant($organizer))->toBeTrue();
    inConference($ans27);
    expect(Registration::query()->where('user_id', $organizer->id)->exists())->toBeTrue();
});
