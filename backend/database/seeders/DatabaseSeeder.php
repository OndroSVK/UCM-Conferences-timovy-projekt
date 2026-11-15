<?php

namespace Database\Seeders;

use App\Modules\Auth\Models\User;
use App\Modules\Auth\Models\UserRole;
use App\Modules\Conferences\Models\Conference;
use App\Modules\Conferences\Models\ConferenceAssignment;
use App\Modules\Conferences\Models\FormField;
use App\Modules\Conferences\Models\ParticipationCategory;
use App\Modules\Payments\Actions\RecordPayment;
use App\Modules\Registration\Actions\RegisterExistingUserForConference;
use App\Modules\Registration\Actions\RegisterNewParticipant;
use App\Modules\Registration\Models\Registration;
use App\Modules\Submissions\Actions\SubmitSubmission;
use App\Shared\Auth\Role;
use App\Shared\Conference\ConferenceContext;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;

/**
 * Prototype data (backend.md 11): two conferences running at the same time, a staff
 * account for every role and fictional participants. No real people (4.7).
 * Passwords of all accounts: config('conferences.seed_password') — local use only.
 * Sample fees 180 / 120 / 90 EUR are test values (O38).
 */
class DatabaseSeeder extends Seeder
{
    public function run(ConferenceContext $context): void
    {
        $password = (string) config('conferences.seed_password');

        $ans27 = $this->conference($context, 2027, now()->addMonths(8));
        $ans28 = $this->conference($context, 2028, now()->addMonths(20));

        $superadmin = $this->user('superadmin@example.test', 'Super Admin', $password);
        UserRole::query()->create(['user_id' => $superadmin->id, 'role' => Role::Superadmin->value]);

        // Organizer of ANS 2027 delegated for payments ("poverený organizátor"); in ANS 2028 without delegation.
        $organizer = $this->user('organizer@example.test', 'Olga Organizer', $password);
        $this->assign($context, $ans27, $organizer, Role::Organizer, ['payments.record', 'payments.waive', 'invoices.retry']);
        $this->assign($context, $ans28, $organizer, Role::Organizer);

        $committee = $this->user('committee@example.test', 'Carl Committee', $password);
        $this->assign($context, $ans27, $committee, Role::ProgrammeCommittee);

        // Participant registered for both conferences (US-08).
        ['user' => $participant, 'registration' => $speaker] = app(RegisterNewParticipant::class)->handle($ans27, $this->input([
            'email' => 'participant@example.test', 'first_name' => 'Petra', 'last_name' => 'Participant', 'category' => 'speaker',
        ], $password));
        app(RegisterExistingUserForConference::class)->handle($participant, $ans28, $this->input(['category' => 'passive_participant'], $password));

        // A poster author with a partial payment and a submitted poster.
        ['user' => $poster, 'registration' => $posterRegistration] = app(RegisterNewParticipant::class)->handle($ans27, $this->input([
            'email' => 'poster@example.test', 'first_name' => 'Pavol', 'last_name' => 'Poster', 'category' => 'poster_author',
        ], $password));

        // A user with both roles chooses after login (R17).
        app(RegisterNewParticipant::class)->handle($ans27, $this->input([
            'email' => 'both@example.test', 'first_name' => 'Bea', 'last_name' => 'Both', 'category' => 'passive_participant',
        ], $password));
        $this->assign($context, $ans27, User::query()->where('email', 'both@example.test')->firstOrFail(), Role::ProgrammeCommittee);

        $context->runForConference($ans27->id, function () use ($organizer, $poster, $posterRegistration) {
            app(RecordPayment::class)->handle($organizer, $posterRegistration, ['amount' => '50', 'paid_on' => now()->toDateString(), 'note' => 'Seed: partial payment']);
            app(SubmitSubmission::class)->handle($poster, Registration::query()->findOrFail($posterRegistration->id)->submissions()->firstOrFail(), [
                'title' => 'Seed poster: soil microbiome',
                'file' => UploadedFile::fake()->createWithContent('poster.pdf', "%PDF-1.4\n% seed file\n%%EOF\n"),
            ]);
        });

        $this->command?->info("Seeded. Log in with *@example.test and the password from SEED_USER_PASSWORD (default: {$password}).");
        $this->command?->info('Conferences: http://ans2027.localhost:8000 and http://ans2028.localhost:8000');
    }

    private function conference(ConferenceContext $context, int $year, \Illuminate\Support\Carbon $start): Conference
    {
        $conference = Conference::query()->create([
            'name' => "Applied Natural Sciences {$year}",
            'subdomain' => "ans{$year}",
            'year' => $year,
            'venue' => 'Example Venue, Trnava',
            'starts_on' => $start->toDateString(),
            'ends_on' => $start->copy()->addDays(2)->toDateString(),
            'registration_deadline' => $start->copy()->subMonths(2)->toDateString(),
            'payment_deadline' => $start->copy()->subMonth()->toDateString(),
            'submission_deadline' => $start->copy()->subMonths(3)->toDateString(),
            'currency' => 'EUR',
            'iban' => 'SK3112000000198742637541', // test IBAN, not a real account
            'bic' => 'TESTSKBX',
            'payee_name' => 'Example Faculty (test)',
            'issuer_name' => 'Example Faculty (test)',
            'issuer_address' => 'Example Street 1, 917 01 Trnava, Slovakia',
            'issuer_company_id' => '00000000',
            'issuer_tax_id' => '0000000000',
            'accommodation_url' => 'https://example.com/accommodation',
            'privacy_url' => 'https://example.com/privacy',
        ]);

        $context->runForConference($conference->id, function () use ($conference) {
            foreach ([
                ['speaker', 'Speaker', 18000, 'abstract'],
                ['poster_author', 'Poster author', 12000, 'poster'],
                ['passive_participant', 'Passive participant', 9000, 'none'],
            ] as $i => [$code, $name, $price, $requirement]) {
                ParticipationCategory::query()->create([
                    'conference_id' => $conference->id, 'code' => $code, 'name' => $name,
                    'price_cents' => $price, 'submission_requirement' => $requirement, 'sort' => $i,
                ]);
            }

            foreach (['physics' => 'Physics', 'chemistry' => 'Chemistry', 'biology' => 'Biology', 'ecology' => 'Ecology'] as $value => $label) {
                FormField::query()->create([
                    'conference_id' => $conference->id, 'field' => FormField::INDUSTRY,
                    'value' => $value, 'label' => $label,
                ]);
            }
        });

        return $conference;
    }

    private function user(string $email, string $name, string $password): User
    {
        return User::query()->forceCreate([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'email_verified_at' => now(),
        ]);
    }

    /** @param list<string> $extra */
    private function assign(ConferenceContext $context, Conference $conference, User $user, Role $role, array $extra = []): void
    {
        $context->runForConference($conference->id, fn () => ConferenceAssignment::query()->create([
            'conference_id' => $conference->id,
            'user_id' => $user->id,
            'role' => $role->value,
            'extra_permissions' => $extra ?: null,
        ]));
    }

    /**
     * @param  array<string, string>  $override
     * @return array<string, string>
     */
    private function input(array $override, string $password): array
    {
        return array_merge([
            'password' => $password,
            'password_confirmation' => $password,
            'first_name' => 'Test',
            'last_name' => 'Participant',
            'organization' => 'Example University',
            'industry' => 'biology',
            'billing_type' => 'participant',
            'billing_name' => 'Test Participant',
            'billing_street' => 'Example Street 2',
            'billing_city' => 'Trnava',
            'billing_postal_code' => '917 01',
            'billing_country' => 'Slovakia',
            'data_processing_consent' => '1',
        ], $override);
    }
}
