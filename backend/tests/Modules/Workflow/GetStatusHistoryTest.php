<?php

use App\Modules\Workflow\Actions\GetStatusHistory;
use App\Modules\Workflow\Enums\StatusCategory;
use App\Shared\Auth\Role;
use Illuminate\Auth\Access\AuthorizationException;

beforeEach(function () {
    $this->conference = makeConference(2027);
    ['user' => $this->owner, 'registration' => $this->registration] = registerParticipant($this->conference);
    inConference($this->conference);
});

$categories = fn ($history) => $history->pluck('category')->unique()->map(fn (StatusCategory $c) => $c->value)->sort()->values()->all();

it('shows the owner all own categories', function () use ($categories) {
    expect($categories(app(GetStatusHistory::class)->handle($this->owner, $this->registration)))
        ->toBe(['payment', 'registration', 'submission']);
});

it('hides payment history from the programme committee', function () use ($categories) {
    $committee = staff($this->conference, Role::ProgrammeCommittee);

    expect($categories(app(GetStatusHistory::class)->handle($committee, $this->registration)))
        ->toBe(['registration', 'submission']);
});

it('shows payment history to the organizer', function () use ($categories) {
    $organizer = staff($this->conference, Role::Organizer);

    expect($categories(app(GetStatusHistory::class)->handle($organizer, $this->registration)))
        ->toContain('payment');
});

it('refuses another participant', function () {
    ['user' => $other] = registerParticipant($this->conference);

    app(GetStatusHistory::class)->handle($other, $this->registration);
})->throws(AuthorizationException::class);

it('refuses staff of another conference', function () {
    $other = staff(makeConference(2028), Role::Organizer);

    app(GetStatusHistory::class)->handle($other, $this->registration);
})->throws(AuthorizationException::class);
