<?php

use App\Http\Middleware\UseAdminConference;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Payments\Actions\RecordPayment;
use App\Modules\Registration\Models\Registration;
use App\Modules\Reporting\Actions\GetRegistrationForStaff;
use App\Modules\Reporting\Actions\ListConferenceRegistrations;
use App\Modules\Reporting\Actions\ListConferenceSubmissions;
use App\Modules\Submissions\Actions\SubmitSubmission;
use App\Shared\Auth\Role;
use Illuminate\Support\Facades\Storage;

// Admin screens over HTTP + the read Actions added for the admin
// (ListConferenceRegistrations, GetRegistrationForStaff, ListConferenceSubmissions).

beforeEach(function () {
    Storage::fake('local');
    $this->ans27 = makeConference(2027);
    $this->ans28 = makeConference(2028);
    ['user' => $this->author, 'registration' => $this->registration] = registerParticipant($this->ans27, 'speaker', [
        'last_name' => 'Searchable', 'billing_name' => 'Billing Secret s.r.o.',
    ]);
    registerParticipant($this->ans28, 'speaker', ['last_name' => 'OtherConf']);
    $this->cashier = paymentsOrganizer($this->ans27);
    conferenceContext()->clear();
});

it('lists registrations of the active conference only', function () {
    $this->actingAs($this->cashier)->get(route('admin.registrations.index'))
        ->assertOk()
        ->assertSee('Searchable')
        ->assertDontSee('OtherConf');
});

it('searches by VS for the organizer', function () {
    $this->actingAs($this->cashier)
        ->get(route('admin.registrations.find', ['vs' => $this->registration->variable_symbol]))
        ->assertRedirect(route('admin.registrations.show', $this->registration->id));
});

// US-12 over HTTP
it('records a partial and a full payment; the invoice appears once', function () {
    $url = route('admin.registrations.payments', $this->registration->id);

    $this->actingAs($this->cashier)->post($url, ['amount' => '80', 'paid_on' => today()->toDateString()])->assertSessionHasNoErrors();
    $this->actingAs($this->cashier)->get(route('admin.registrations.show', $this->registration->id))
        ->assertSee(__('statuses.payment.partially_paid'))->assertSee('100.00 EUR');

    $this->actingAs($this->cashier)->post($url, ['amount' => '100', 'paid_on' => today()->toDateString()]);
    $this->actingAs($this->cashier)->post($url, ['amount' => '5', 'paid_on' => today()->toDateString()])
        ->assertSessionHas('warning'); // overpayment BO6

    conferenceContext()->setConference($this->ans27->id);
    $invoice = Invoice::query()->sole();
    $this->actingAs($this->cashier)->get(route('admin.invoices.download', $invoice->id))->assertOk()->assertHeader('content-type', 'application/pdf');

    // the participant sees and downloads the same invoice
    $this->actingAs($this->author)->get(route('account.registrations.show', $this->registration->id))->assertSee($invoice->number);
    $this->actingAs($this->author)->get(route('account.invoices.download', $invoice->id))->assertOk();
});

it('shows a refused business rule next to the form', function () {
    $this->actingAs(staff($this->ans27, Role::Organizer))
        ->post(route('admin.registrations.payments', $this->registration->id), ['amount' => '10', 'paid_on' => today()->toDateString()])
        ->assertForbidden();

    $this->actingAs($this->cashier)->post(route('admin.registrations.waiver', $this->registration->id));
    $this->actingAs($this->cashier)
        ->from(route('admin.registrations.show', $this->registration->id))
        ->post(route('admin.registrations.waiver', $this->registration->id))
        ->assertSessionHasErrors('domain');
});

it('hides financial data from the programme committee', function () {
    $committee = staff($this->ans27, Role::ProgrammeCommittee);

    $this->actingAs($committee)->get(route('admin.registrations.show', $this->registration->id))
        ->assertOk()
        ->assertSee('Searchable')
        ->assertDontSee('Billing Secret')
        ->assertDontSee($this->registration->variable_symbol);

    expect(app(GetRegistrationForStaff::class))->toBeInstanceOf(GetRegistrationForStaff::class);
});

it('the committee decides a submission; the organizer does not see the form', function () {
    conferenceContext()->setConference($this->ans27->id);
    $submission = $this->registration->submissions()->first();
    app(SubmitSubmission::class)->handle($this->author, $submission, ['title' => 'Decide me', 'file' => fakePdf()]);
    conferenceContext()->clear();
    $committee = staff($this->ans27, Role::ProgrammeCommittee);

    $this->actingAs($committee)->get(route('admin.submissions.index'))->assertOk()->assertSee('Decide me');
    $this->actingAs($this->cashier)->get(route('admin.submissions.show', $submission->id))
        ->assertOk()->assertDontSee(__('ui.admin.record_decision'));

    $this->actingAs($committee)->post(route('admin.submissions.decide', $submission->id), [
        'decision' => 'accepted', 'justification' => 'Great.',
    ])->assertRedirect(route('admin.submissions.show', $submission->id));

    $this->actingAs($this->cashier)
        ->post(route('admin.submissions.decide', $submission->id), ['decision' => 'rejected', 'justification' => 'x'])
        ->assertForbidden();

    expect(class_exists(ListConferenceSubmissions::class) && class_exists(ListConferenceRegistrations::class))->toBeTrue();
});

// BR4
it('an organizer cannot switch to a conference that is not assigned', function () {
    $this->actingAs($this->cashier)
        ->post(route('admin.conference.switch'), ['conference_id' => $this->ans28->id])
        ->assertForbidden();

    $this->actingAs($this->cashier)
        ->withSession([UseAdminConference::SESSION_KEY => $this->ans28->id])
        ->get(route('admin.registrations.index'))
        ->assertOk()
        ->assertDontSee('OtherConf'); // falls back to an assigned conference
});

it('the superadmin switches conferences and manages the team', function () {
    $admin = superadmin();
    $newcomer = makeUser(['email' => 'newcomer@example.test']);

    $this->actingAs($admin)->post(route('admin.conference.switch'), ['conference_id' => $this->ans28->id]);
    $this->actingAs($admin)->get(route('admin.registrations.index'))->assertSee('OtherConf');

    $this->actingAs($admin)->post(route('admin.team.store'), [
        'email' => 'newcomer@example.test', 'role' => 'organizer', 'extra_permissions' => ['payments.record'],
    ])->assertRedirect(route('admin.team.index'));

    $this->actingAs($admin)->get(route('admin.team.index'))->assertSee('newcomer@example.test');
    $this->actingAs($newcomer)->get(route('admin.registrations.index'))->assertOk()->assertSee('OtherConf');
});

it('exports XLSX and CSV by role', function () {
    $this->actingAs($this->cashier)->post(route('admin.exports.download'), ['format' => 'xlsx'])
        ->assertOk()->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $csv = $this->actingAs(staff($this->ans27, Role::ProgrammeCommittee))
        ->post(route('admin.exports.download'), ['format' => 'csv'])
        ->assertOk()
        ->getContent();

    expect($csv)->toContain('Searchable')->not->toContain('Billing Secret');
});

it('a participant registered on both conferences sees only own data in the portal', function () {
    conferenceContext()->setConference($this->ans27->id);
    app(RecordPayment::class)->handle($this->cashier, $this->registration, ['amount' => '180', 'paid_on' => today()->toDateString()]);
    conferenceContext()->clear();

    ['user' => $other, 'registration' => $otherRegistration] = registerParticipant($this->ans27);
    conferenceContext()->setConference($this->ans27->id);
    $invoice = Invoice::query()->sole();
    conferenceContext()->clear();

    $this->actingAs($other)->get(route('account.invoices.download', $invoice->id))->assertForbidden();
    expect(Registration::class)->toBeString();
});

it('paginates long lists with its own pagination view', function () {
    foreach (range(1, 27) as $i) {
        registerParticipant($this->ans27, 'passive_participant', ['last_name' => "Bulk{$i}"]);
    }
    conferenceContext()->clear();

    $this->actingAs($this->cashier)->get(route('admin.registrations.index'))
        ->assertOk()
        ->assertSee('Page 1 of 2')
        ->assertSee('page=2')
        ->assertDontSee('<svg class="w-5 h-5"', false); // not the Tailwind default view

    $this->actingAs($this->cashier)->get(route('admin.registrations.index', ['page' => 2]))
        ->assertOk()
        ->assertSee('Page 2 of 2')
        ->assertSee('Searchable'); // the oldest registration is on the last page
});

it('the superadmin creates a new staff account on the team screen', function () {
    $admin = superadmin();

    $this->actingAs($admin)->from(route('admin.team.index'))
        ->post(route('admin.team.store'), ['email' => 'fresh@example.test', 'role' => 'programme_committee'])
        ->assertSessionHasErrors('email'); // unknown e-mail without name and password

    $this->actingAs($admin)->post(route('admin.team.store'), [
        'email' => 'fresh@example.test', 'name' => 'Fresh Member', 'password' => 'initial-pass-1', 'role' => 'programme_committee',
    ])->assertRedirect(route('admin.team.index'));

    $this->post(route('logout'));
    $this->post('/login', ['email' => 'fresh@example.test', 'password' => 'initial-pass-1'])
        ->assertRedirect(route('admin.registrations.index'));
});
