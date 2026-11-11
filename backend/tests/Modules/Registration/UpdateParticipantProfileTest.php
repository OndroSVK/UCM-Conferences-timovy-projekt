<?php

use App\Modules\Registration\Actions\UpdateParticipantProfile;
use Illuminate\Validation\ValidationException;

it('creates and updates the profile of an account', function () {
    $user = makeUser();
    $update = app(UpdateParticipantProfile::class);

    $update->handle($user, ['first_name' => 'Jana', 'last_name' => 'Testová', 'organization' => 'Example University']);
    $profile = $update->handle($user, ['first_name' => 'Jana', 'last_name' => 'Nová', 'title_before' => 'doc.', 'organization' => 'Example University']);

    expect($profile->fullName())->toBe('doc. Jana Nová')
        ->and($profile->user_id)->toBe($user->id);
});

it('requires names and organization', function () {
    app(UpdateParticipantProfile::class)->handle(makeUser(), ['first_name' => 'Jana']);
})->throws(ValidationException::class);
