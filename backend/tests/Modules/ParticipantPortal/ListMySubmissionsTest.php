<?php

use App\Modules\ParticipantPortal\Actions\ListMySubmissions;

it('lists only own required submissions across conferences, including unsubmitted drafts', function () {
    $first = makeConference(2027);
    $second = makeConference(2028);
    ['user' => $owner, 'registration' => $registration] = registerParticipant($first);
    ['registration' => $foreign] = registerParticipant($first);
    app(\App\Modules\Registration\Actions\RegisterExistingUserForConference::class)
        ->handle($owner, $second, registrationInput(['category' => 'passive_participant']));

    $results = app(ListMySubmissions::class)->handle($owner);

    expect($results)->toHaveCount(1)
        ->and($results->first()->registration_id)->toBe($registration->id)
        ->and($results->first()->status->value)->toBe('draft');

    $this->actingAs($owner)->get(route('account.submissions.index'))
        ->assertOk()->assertSee(route('account.registrations.show', $registration->id), false)
        ->assertDontSee(route('account.registrations.show', $foreign->id), false);
});

it('shows an empty submissions state without sample records', function () {
    $conference = makeConference();
    ['user' => $owner] = registerParticipant($conference, 'passive_participant');

    expect(app(ListMySubmissions::class)->handle($owner))->toBeEmpty();
    $this->actingAs($owner)->get(route('account.submissions.index'))
        ->assertOk()->assertSee(__('ui.flow.no_submissions'));
});
