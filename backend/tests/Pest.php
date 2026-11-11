<?php

use App\Modules\Auth\Models\User;
use App\Modules\Auth\Models\UserRole;
use App\Modules\Conferences\Models\Conference;
use App\Modules\Conferences\Models\FormField;
use App\Modules\Conferences\Models\ParticipationCategory;
use App\Modules\Registration\Actions\RegisterNewParticipant;
use App\Modules\Registration\Models\Registration;
use App\Shared\Auth\Role;
use App\Shared\Conference\ConferenceContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/*
 * Test data are fictional only (backend.md 4.7): example.test addresses, test IBAN.
 * Sample prices 180 / 120 / 90 EUR (O38) are test values, not real fees.
 */

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Modules', 'CrossCutting', 'Http');

function conferenceContext(): ConferenceContext
{
    return app(ConferenceContext::class);
}

/** Sets the active conference for the rest of the test. */
function inConference(Conference $conference): Conference
{
    conferenceContext()->setConference($conference->id);

    return $conference;
}

/**
 * Conference with the three categories and industry options.
 *
 * @param  array<string, mixed>  $attributes
 */
function makeConference(int $year = 2027, array $attributes = []): Conference
{
    $conference = Conference::factory()->edition($year)->create($attributes);

    conferenceContext()->runForConference($conference->id, function () use ($conference) {
        foreach ([
            ['speaker', 'Speaker', 18000, 'abstract', 1],
            ['poster_author', 'Poster author', 12000, 'poster', 2],
            ['passive_participant', 'Passive participant', 9000, 'none', 3],
        ] as [$code, $name, $price, $requirement, $sort]) {
            ParticipationCategory::query()->create([
                'conference_id' => $conference->id,
                'code' => $code,
                'name' => $name,
                'price_cents' => $price,
                'submission_requirement' => $requirement,
                'sort' => $sort,
            ]);
        }

        foreach ([['physics', 'Physics'], ['chemistry', 'Chemistry'], ['biology', 'Biology']] as $i => [$value, $label]) {
            FormField::query()->create([
                'conference_id' => $conference->id,
                'field' => FormField::INDUSTRY,
                'value' => $value,
                'label' => $label,
                'sort' => $i,
            ]);
        }
    });

    return $conference;
}

/** @param array<string, mixed> $attributes */
function makeUser(array $attributes = []): User
{
    return User::factory()->create($attributes);
}

function superadmin(): User
{
    $user = makeUser();
    UserRole::query()->create(['user_id' => $user->id, 'role' => Role::Superadmin->value]);

    return $user;
}

/**
 * Staff member with a role in one conference (BR4, BR8).
 *
 * @param  list<string>  $extraPermissions  e.g. ['payments.record']
 */
function staff(Conference $conference, Role $role, array $extraPermissions = [], ?User $user = null): User
{
    $user ??= makeUser();

    DB::table('conference_user')->insert([
        'conference_id' => $conference->id,
        'user_id' => $user->id,
        'role' => $role->value,
        'extra_permissions' => $extraPermissions === [] ? null : json_encode($extraPermissions),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return $user;
}

/** Organizer delegated to record payments, waive fees and retry invoices in this conference. */
function paymentsOrganizer(Conference $conference): User
{
    return staff($conference, Role::Organizer, ['payments.record', 'payments.waive', 'invoices.retry']);
}

/**
 * Valid input of the registration form.
 *
 * @param  array<string, mixed>  $override
 * @return array<string, mixed>
 */
function registrationInput(array $override = []): array
{
    static $counter = 0;
    $counter++;

    return array_merge([
        'email' => "participant{$counter}@example.test",
        'password' => 'secret-password',
        'password_confirmation' => 'secret-password',
        'first_name' => 'Jana',
        'last_name' => "Testová{$counter}",
        'title_before' => 'Ing.',
        'title_after' => 'PhD.',
        'organization' => 'Example University',
        'category' => 'speaker',
        'industry' => 'physics',
        'billing_type' => 'participant',
        'billing_name' => 'Jana Testová',
        'billing_street' => 'Example Street 1',
        'billing_city' => 'Trnava',
        'billing_postal_code' => '917 01',
        'billing_country' => 'Slovakia',
        'data_processing_consent' => '1',
    ], $override);
}

/**
 * Registers a new participant through the real feature.
 *
 * @param  array<string, mixed>  $override
 * @return array{user: User, registration: Registration}
 */
function registerParticipant(Conference $conference, string $category = 'speaker', array $override = []): array
{
    return app(RegisterNewParticipant::class)->handle(
        $conference,
        registrationInput(['category' => $category] + $override),
    );
}

/** A fresh copy of a registration read inside its conference. */
function freshRegistration(Registration $registration): Registration
{
    return conferenceContext()->runForConference($registration->conference_id, fn () => Registration::query()->findOrFail($registration->id));
}

function fakePdf(string $name = 'paper.pdf'): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, "%PDF-1.4\n1 0 obj << /Type /Catalog >> endobj\ntrailer << >>\n%%EOF\n");
}
