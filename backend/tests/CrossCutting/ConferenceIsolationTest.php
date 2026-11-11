<?php

use App\Modules\Conferences\Actions\SwitchActiveConference;
use App\Modules\Conferences\Models\ParticipationCategory;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Notifications\Models\EmailLog;
use App\Modules\Payments\Actions\RecordPayment;
use App\Modules\Payments\Models\Payment;
use App\Modules\Registration\Models\Registration;
use App\Modules\Submissions\Models\Submission;
use App\Modules\Workflow\Models\StatusHistory;
use App\Shared\Auth\Role;
use App\Shared\Conference\MissingConferenceContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Storage;

// backend.md 11.1 — data of conference B are neither visible nor changeable in the context of A.

beforeEach(function () {
    Storage::fake('local');
    $this->a = makeConference(2027);
    $this->b = makeConference(2028);

    ['registration' => $this->regA] = registerParticipant($this->a);
    ['registration' => $this->regB] = registerParticipant($this->b);

    foreach ([[$this->a, $this->regA], [$this->b, $this->regB]] as [$conference, $registration]) {
        inConference($conference);
        app(RecordPayment::class)->handle(paymentsOrganizer($conference), $registration, ['amount' => '180', 'paid_on' => today()->toDateString()]);
    }

    conferenceContext()->clear();
});

$models = [
    'registrations' => Registration::class,
    'submissions' => Submission::class,
    'payments' => Payment::class,
    'invoices' => Invoice::class,
    'status history' => StatusHistory::class,
    'e-mail log' => EmailLog::class,
    'categories' => ParticipationCategory::class,
];

it('shows only data of the active conference', function (string $model) {
    inConference($this->a);
    $ids = $model::query()->pluck('conference_id')->unique()->values()->all();

    expect($ids)->toBe([$this->a->id]);
})->with($models);

it('fails without an active conference instead of returning all data', function (string $model) {
    $model::query()->get();
})->with($models)->throws(MissingConferenceContext::class);

it('finds no record of the other conference by id', function () {
    inConference($this->a);

    expect(Registration::query()->find($this->regB->id))->toBeNull();
});

it('refuses to write a record of the other conference', function () {
    inConference($this->a);

    $this->regB->forceFill(['billing_city' => 'Hacked'])->save();
})->throws(MissingConferenceContext::class);

it('refuses to create a record for the other conference', function () {
    inConference($this->a);

    Payment::query()->create([
        'conference_id' => $this->b->id,
        'registration_id' => $this->regB->id,
        'amount_cents' => 1,
        'currency' => 'EUR',
        'paid_on' => today(),
        'variable_symbol' => $this->regB->variable_symbol,
        'recorded_by' => superadmin()->id,
    ]);
})->throws(MissingConferenceContext::class);

it('an organizer of A cannot act on a registration of B even when the context is B', function () {
    $cashierA = paymentsOrganizer($this->a);
    inConference($this->b);

    app(RecordPayment::class)->handle($cashierA, $this->regB, ['amount' => '1', 'paid_on' => today()->toDateString()]);
})->throws(AuthorizationException::class);

// BR4
it('an admin cannot activate a conference that is not assigned', function () {
    app(SwitchActiveConference::class)->handle(staff($this->a, Role::Organizer), $this->b->id);
})->throws(AuthorizationException::class);
