<?php

use App\Modules\Registration\Models\Registration;
use App\Modules\Submissions\Models\Submission;
use App\Shared\Auth\Role;

it('renders server categories and fees in the step form with a fixed login return', function () {
    $conference = makeConference();
    inConference($conference);
    $conference->participationCategories()->where('code', 'speaker')->update(['price_cents' => 23700, 'name' => 'Invited presentation']);

    $this->get('http://ans2027.localhost/register')->assertOk()
        ->assertSee('data-step-form', false)->assertSee('237.00 EUR')
        ->assertSee('Invited presentation')->assertSee('continue=registration', false)
        ->assertSee('name="billing_street"', false)->assertSee('name="password_confirmation"', false);
});

it('returns an existing account to conference registration after login', function () {
    $conference = makeConference();
    $other = makeConference(2028);
    ['user' => $owner] = registerParticipant($other, 'speaker', ['email' => 'return@example.test']);

    $this->get('http://ans2027.localhost/login?continue=registration')->assertOk();
    $this->post('http://ans2027.localhost/login', ['email' => $owner->email, 'password' => 'secret-password'])
        ->assertRedirect('http://ans2027.localhost/register');
    $this->get('http://ans2027.localhost/register')->assertOk()->assertSee($owner->email);
    $this->post('http://ans2027.localhost/register/existing', registrationInput())->assertSessionHasNoErrors()->assertRedirect();
    inConference($conference);
    expect(Registration::query()->where('user_id', $owner->id)->count())->toBe(1);
});

it('does not accept an arbitrary post-login return address', function () {
    makeConference();
    $this->get('http://ans2027.localhost/login?continue=https://evil.example.test')
        ->assertOk()->assertSessionMissing('url.intended');
});

it('shows all three real statuses in the participant dashboard', function () {
    $conference = makeConference();
    ['user' => $owner] = registerParticipant($conference, 'passive_participant');
    $this->actingAs($owner)->get(route('account.index'))->assertOk()
        ->assertSee(__('statuses.submission.not_required'))
        ->assertSee(__('statuses.registration.received'))
        ->assertSee(__('statuses.payment.pending'));
});

it('hides submission upload after the deadline without hiding previous versions', function () {
    $conference = makeConference();
    ['user' => $owner, 'registration' => $registration] = registerParticipant($conference);
    inConference($conference);
    $submission = Submission::query()->where('registration_id', $registration->id)->firstOrFail();
    app(\App\Modules\Submissions\Actions\SubmitSubmission::class)->handle($owner, $submission, ['title' => 'Existing PDF', 'file' => fakePdf()]);
    $conference->update(['submission_deadline' => today()->subDay()]);

    $this->actingAs($owner)->get(route('account.registrations.show', $registration->id))->assertOk()
        ->assertSee(__('ui.flow.upload_closed'))->assertSee(__('ui.account.version', ['number' => 1]))
        ->assertDontSee('type="file"', false);
});

it('keeps financial data and unsupported tools out of the committee workspace', function () {
    $conference = makeConference();
    ['registration' => $registration] = registerParticipant($conference);
    $committee = staff($conference, Role::ProgrammeCommittee);

    $this->actingAs($committee)->get(route('admin.registrations.show', $registration->id))->assertOk()
        ->assertDontSee($registration->variable_symbol)
        ->assertDontSee('role-switch')->assertDontSee('website-editor.js');
});
