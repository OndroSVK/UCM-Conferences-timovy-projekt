<?php

use App\Modules\Conferences\Actions\CreateConference;
use App\Modules\Conferences\Actions\GetRegistrationFormOptions;
use App\Modules\Conferences\Models\Conference;
use Illuminate\Validation\ValidationException;

function conferenceDefinition(array $override = []): array
{
    return array_replace(json_decode(file_get_contents(database_path('conference.example.json')), true), $override);
}

it('creates a conference with categories, prices in cents and option lists from the example file', function () {
    $conference = app(CreateConference::class)->handle(conferenceDefinition());

    $options = app(GetRegistrationFormOptions::class)->handle($conference);

    expect($conference->subdomain)->toBe('ans2027')
        ->and($conference->privacy_url)->toBe('https://example.com/privacy')
        ->and(collect($options['categories'])->pluck('price')->map->cents->all())->toBe([18000, 12000, 9000])
        ->and(array_column($options['fields']['industry'], 'value'))->toBe(['physics', 'chemistry', 'biology']);
});

it('a participant can register for the created conference', function () {
    $conference = app(CreateConference::class)->handle(conferenceDefinition([
        'starts_on' => now()->addYear()->toDateString(),
        'ends_on' => now()->addYear()->addDays(2)->toDateString(),
        'registration_deadline' => now()->addMonths(6)->toDateString(),
        'payment_deadline' => now()->addMonths(7)->toDateString(),
        'submission_deadline' => now()->addMonths(5)->toDateString(),
    ]));

    ['registration' => $registration] = registerParticipant($conference, 'speaker');

    expect($registration->price_cents)->toBe(18000);
});

it('refuses an invalid definition and creates nothing', function (array $override) {
    try {
        app(CreateConference::class)->handle(conferenceDefinition($override));
        $this->fail('Expected a validation error.');
    } catch (ValidationException) {
        expect(Conference::query()->count())->toBe(0);
    }
})->with([
    'no categories' => [['categories' => []]],
    'unknown category' => [['categories' => [['code' => 'vip', 'name' => 'VIP', 'price' => '1', 'submission_requirement' => 'none']]]],
    'duplicate category' => [['categories' => [
        ['code' => 'speaker', 'name' => 'A', 'price' => '1', 'submission_requirement' => 'abstract'],
        ['code' => 'speaker', 'name' => 'B', 'price' => '2', 'submission_requirement' => 'abstract'],
    ]]],
    'negative price' => [['categories' => [['code' => 'speaker', 'name' => 'A', 'price' => '-5', 'submission_requirement' => 'abstract']]]],
    'bad subdomain' => [['subdomain' => 'ANS 2027']],
    'bad iban' => [['iban' => 'not-an-iban']],
    'deadline after start' => [['registration_deadline' => '2028-01-01']],
    'end before start' => [['ends_on' => '2027-09-01']],
]);

it('refuses a second conference on the same subdomain', function () {
    app(CreateConference::class)->handle(conferenceDefinition());

    app(CreateConference::class)->handle(conferenceDefinition());
})->throws(ValidationException::class);
