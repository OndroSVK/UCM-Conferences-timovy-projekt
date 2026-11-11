<?php

use App\Modules\Invoicing\Actions\DownloadInvoice;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Payments\Actions\RecordPayment;
use App\Shared\Auth\Role;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->conference = makeConference(2027);
    ['user' => $this->owner, 'registration' => $registration] = registerParticipant($this->conference, 'speaker');
    inConference($this->conference);
    app(RecordPayment::class)->handle(paymentsOrganizer($this->conference), $registration, ['amount' => '180', 'paid_on' => today()->toDateString()]);
    $this->invoice = Invoice::query()->sole();
});

it('allows the owner, the organizer and the superadmin', function () {
    foreach ([$this->owner, staff($this->conference, Role::Organizer), superadmin()] as $actor) {
        expect(app(DownloadInvoice::class)->handle($actor, $this->invoice)->filename)->toBe('TEST-ANS2027-0001.pdf');
    }
});

it('refuses the programme committee (no financial data)', function () {
    app(DownloadInvoice::class)->handle(staff($this->conference, Role::ProgrammeCommittee), $this->invoice);
})->throws(AuthorizationException::class);

it('refuses another participant', function () {
    ['user' => $other] = registerParticipant($this->conference);

    app(DownloadInvoice::class)->handle($other, $this->invoice);
})->throws(AuthorizationException::class);
