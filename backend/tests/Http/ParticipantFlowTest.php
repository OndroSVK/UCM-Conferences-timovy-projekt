<?php

use App\Modules\Auth\Models\User;
use App\Modules\Registration\Models\Registration;
use App\Modules\Submissions\Models\Submission;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;

// The HTTP layer on top of the tested Actions: routes, middleware, views.

beforeEach(function () {
    Storage::fake('local');
    $this->ans27 = makeConference(2027);
    $this->ans28 = makeConference(2028);
});

function hostUrl(string $subdomain, string $path): string
{
    return "http://{$subdomain}.localhost{$path}";
}

it('shows the conference page on its subdomain and 404 on an unknown one', function () {
    $this->get(hostUrl('ans2027', '/'))->assertOk()->assertSee($this->ans27->name)->assertSee('180.00 EUR');
    $this->get(hostUrl('ans1999', '/register'))->assertNotFound();
});

// US-03 over HTTP
it('registers a new participant and opens the user page', function () {
    $response = $this->post(hostUrl('ans2027', '/register'), registrationInput(['email' => 'web@example.test']));

    $user = User::query()->where('email', 'web@example.test')->firstOrFail();
    conferenceContext()->setOwner($user->id);
    $registration = Registration::query()->where('user_id', $user->id)->firstOrFail();

    $response->assertRedirect(route('account.registrations.show', $registration->id));
    $this->assertAuthenticatedAs($user);

    $this->get(route('account.registrations.show', $registration->id))
        ->assertOk()
        ->assertSee($registration->variable_symbol)
        ->assertSee($this->ans27->iban)
        ->assertSee('180.00 EUR');
});

it('shows a validation error with a login offer for an existing e-mail (US-04)', function () {
    registerParticipant($this->ans27, 'speaker', ['email' => 'exists@example.test']);

    $this->from(hostUrl('ans2027', '/register'))
        ->post(hostUrl('ans2027', '/register'), registrationInput(['email' => 'exists@example.test']))
        ->assertSessionHasErrors('email');
});

// US-02
it('logs in and redirects by role; a wrong password shows an error', function () {
    registerParticipant($this->ans27, 'speaker', ['email' => 'p@example.test']);
    RateLimiter::clear('login:'.sha1('p@example.test|127.0.0.1'));

    $this->post('/login', ['email' => 'p@example.test', 'password' => 'wrong'])->assertSessionHasErrors('email');
    $this->post('/login', ['email' => 'p@example.test', 'password' => 'secret-password'])->assertRedirect(route('account.index'));
});

// US-08
it('lists both conferences of a participant, newest first', function () {
    $this->ans28->update(['starts_on' => $this->ans27->starts_on->addYear()->toDateString(), 'ends_on' => $this->ans27->ends_on->addYear()->toDateString()]);
    ['user' => $user] = registerParticipant($this->ans27);
    $this->actingAs($user)->post(hostUrl('ans2028', '/register/existing'), registrationInput(['category' => 'passive_participant']))
        ->assertRedirect();

    $this->actingAs($user)->get(route('account.index'))
        ->assertOk()
        ->assertSeeInOrder([$this->ans28->name, $this->ans27->name]);
});

it('uploads a submission and downloads it; another participant gets 403', function () {
    ['user' => $author, 'registration' => $registration] = registerParticipant($this->ans27);
    conferenceContext()->setOwner($author->id);
    $submission = Submission::query()->where('registration_id', $registration->id)->firstOrFail();

    $this->actingAs($author)
        ->post(route('account.submissions.submit', $submission->id), ['title' => 'Web paper', 'file' => fakePdf()])
        ->assertRedirect(route('account.registrations.show', $registration->id));

    $version = $submission->versions()->firstOrFail();
    $this->actingAs($author)->get(route('account.submissions.download', $version->id))->assertOk();

    ['user' => $other] = registerParticipant($this->ans27);
    $this->actingAs($other)->get(route('account.submissions.download', $version->id))->assertForbidden();
});

it('a participant cannot open the registration of someone else', function () {
    ['registration' => $foreign] = registerParticipant($this->ans27);
    ['user' => $user] = registerParticipant($this->ans27);

    $this->actingAs($user)->get(route('account.registrations.show', $foreign->id))->assertNotFound();
});

it('a participant cannot enter the admin', function () {
    ['user' => $user] = registerParticipant($this->ans27);

    $this->actingAs($user)->get(route('admin.registrations.index'))->assertForbidden();
});

it('updates the profile', function () {
    ['user' => $user] = registerParticipant($this->ans27);

    $this->actingAs($user)->post(route('account.profile.update'), [
        'first_name' => 'Nina', 'last_name' => 'Nová', 'organization' => 'Example Institute', 'email' => $user->email,
    ])->assertRedirect(route('account.profile'));

    expect($user->fresh()->name)->toBe('Nina Nová');
});
