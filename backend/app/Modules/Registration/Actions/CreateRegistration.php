<?php

namespace App\Modules\Registration\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Conferences\Models\Conference;
use App\Modules\Conferences\Models\FormField;
use App\Modules\Conferences\Models\ParticipationCategory;
use App\Modules\Registration\Enums\BillingType;
use App\Modules\Registration\Events\RegistrationCreated;
use App\Modules\Registration\Models\Registration;
use App\Modules\Submissions\Actions\InitializeSubmission;
use App\Modules\Workflow\Actions\ChangeStatus;
use App\Modules\Workflow\Enums\PaymentStatus;
use App\Modules\Workflow\Enums\RegistrationStatus;
use App\Modules\Workflow\Enums\StatusCategory;
use App\Shared\Actions\Action;
use App\Shared\Actions\DomainException;
use App\Shared\Conference\ConferenceContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Shared step of RegisterNewParticipant and RegisterExistingUserForConference:
 * the registration itself with initial statuses by category (backend.md 7.4),
 * price snapshot, VS, consent and the RegistrationCreated event.
 */
final class CreateRegistration extends Action
{
    public function __construct(
        private readonly ConferenceContext $context,
        private readonly CalculateRegistrationPrice $calculatePrice,
        private readonly GenerateVariableSymbol $generateVariableSymbol,
        private readonly ChangeStatus $changeStatus,
        private readonly InitializeSubmission $initializeSubmission,
    ) {
    }

    /**
     * Rules depend on the conference: its categories and option lists.
     *
     * @return array<string, mixed>
     */
    public function rulesFor(Conference $conference): array
    {
        return $this->context->runForConference($conference->id, function () {
            $categories = ParticipationCategory::query()->get()->map(fn (ParticipationCategory $c) => $c->code->value)->all();
            $industries = FormField::query()->where('field', FormField::INDUSTRY)->pluck('value')->all();

            return [
                'category' => ['required', 'string', Rule::in($categories)],
                'industry' => $industries === []
                    ? ['nullable', 'string', 'max:80']
                    : ['required', 'string', Rule::in($industries)],
                'billing_type' => ['required', Rule::enum(BillingType::class)],
                'billing_name' => ['required', 'string', 'max:255'],
                'billing_street' => ['required', 'string', 'max:255'],
                'billing_city' => ['required', 'string', 'max:100'],
                'billing_postal_code' => ['required', 'string', 'max:20'],
                'billing_country' => ['required', 'string', 'max:100'],
                'billing_company_id' => ['nullable', 'string', 'max:20'],
                'billing_tax_id' => ['nullable', 'string', 'max:20'],
                'billing_vat_id' => ['nullable', 'string', 'max:20'],
                'data_processing_consent' => ['accepted'],
            ];
        });
    }

    /**
     * @param  array<string, mixed>  $data  validated by rulesFor()
     */
    public function handle(User $user, Conference $conference, array $data, bool $newAccount): Registration
    {
        if (! $conference->isRegistrationOpen()) {
            throw new DomainException(__('errors.registration_closed'));
        }

        return $this->context->runForConference($conference->id, fn () => DB::transaction(function () use ($user, $conference, $data, $newAccount) {
            if (Registration::query()->where('user_id', $user->id)->exists()) {
                throw new DomainException(__('errors.already_registered'));
            }

            $category = ParticipationCategory::query()->where('code', $data['category'])->firstOrFail();
            $price = $this->calculatePrice->handle($conference, $data['category']);

            $registration = Registration::query()->create([
                'conference_id' => $conference->id,
                'user_id' => $user->id,
                'participation_category_id' => $category->id,
                'category_code' => $category->code,
                'industry' => $data['industry'] ?? null,
                'billing_type' => $data['billing_type'],
                'billing_name' => $data['billing_name'],
                'billing_street' => $data['billing_street'],
                'billing_city' => $data['billing_city'],
                'billing_postal_code' => $data['billing_postal_code'],
                'billing_country' => $data['billing_country'],
                'billing_company_id' => $data['billing_company_id'] ?? null,
                'billing_tax_id' => $data['billing_tax_id'] ?? null,
                'billing_vat_id' => $data['billing_vat_id'] ?? null,
                'price_cents' => $price->cents,
                'currency' => $price->currency,
                'variable_symbol' => $this->generateVariableSymbol->handle($conference),
                'data_processing_consent_at' => now(),
            ]);

            $this->changeStatus->initialize($registration, StatusCategory::Registration, RegistrationStatus::Received, $user);

            // Zero price → no payment request (R1, BO4). PROPOSAL: O38 — passive participant may be free.
            if ($price->isPositive()) {
                $this->changeStatus->initialize($registration, StatusCategory::Payment, PaymentStatus::Pending, $user);
            }

            $this->initializeSubmission->handle($registration, $category->submission_requirement, $user);

            RegistrationCreated::dispatch($registration->id, $conference->id, $user->id, $newAccount);

            return $registration;
        }));
    }
}
