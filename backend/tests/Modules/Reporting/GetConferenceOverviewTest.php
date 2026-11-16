<?php

use App\Modules\Reporting\Actions\GetConferenceOverview;
use App\Shared\Auth\Role;

it('counts the entire active conference and omits financial counts for the committee', function () {
    $first = makeConference(2027);
    $second = makeConference(2028);
    registerParticipant($first);
    registerParticipant($first, 'passive_participant');
    registerParticipant($second);
    $organizer = staff($first, Role::Organizer);
    $committee = staff($first, Role::ProgrammeCommittee);
    inConference($first);

    $overview = app(GetConferenceOverview::class);
    expect($overview->handle($organizer))->toBe([
        'registrations' => 2, 'confirmed' => 0, 'submissions' => 1,
        'awaiting_decision' => 0, 'awaiting_payment' => 2,
    ]);
    expect($overview->handle($committee))->not->toHaveKey('awaiting_payment');
    $this->actingAs($committee)->get(route('admin.overview'))->assertOk()
        ->assertDontSee(__('ui.dashboard.awaiting_payment'));
});

it('refuses an organizer not assigned to the active conference', function () {
    $first = makeConference(2027);
    $second = makeConference(2028);
    $organizer = staff($first, Role::Organizer);
    inConference($second);
    app(GetConferenceOverview::class)->handle($organizer);
})->throws(\Illuminate\Auth\Access\AuthorizationException::class);

it('returns zero counts for an empty conference instead of sample statistics', function () {
    $conference = makeConference();
    $organizer = staff($conference, Role::Organizer);
    inConference($conference);
    expect(array_unique(array_values(app(GetConferenceOverview::class)->handle($organizer))))->toBe([0]);
});
