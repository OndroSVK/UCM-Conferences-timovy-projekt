<?php

namespace App\Modules\Conferences\Actions;

use App\Modules\Conferences\Enums\ParticipationCategoryCode;
use App\Modules\Conferences\Enums\SubmissionRequirement;
use App\Modules\Conferences\Models\Conference;
use App\Modules\Conferences\Models\FormField;
use App\Modules\Conferences\Models\ParticipationCategory;
use App\Shared\Actions\Action;
use App\Shared\Conference\ConferenceContext;
use App\Shared\Money\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Creates a conference with its categories, prices and option lists from one
 * definition (see database/conference.example.json). Used by the console command
 * conferences:create; the superadmin screen with form settings is full version.
 *
 * All amounts, the IBAN and the issuer come from the definition, never from code (R32).
 */
final class CreateConference extends Action
{
    public function __construct(private readonly ConferenceContext $context)
    {
    }

    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'subdomain' => ['required', 'string', 'max:63', 'regex:/^[a-z0-9]([a-z0-9-]*[a-z0-9])?$/', 'unique:conferences,subdomain'],
            'year' => ['required', 'integer', 'between:2000,2100'],
            'venue' => ['nullable', 'string', 'max:255'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
            'registration_deadline' => ['required', 'date', 'before_or_equal:starts_on'],
            'payment_deadline' => ['required', 'date', 'before_or_equal:starts_on'],
            'submission_deadline' => ['required', 'date', 'before_or_equal:starts_on'],
            'currency' => ['required', 'string', 'regex:/^[A-Z]{3}$/'],
            'iban' => ['required', 'string', 'max:34', 'regex:/^[A-Z]{2}[0-9A-Z]{13,32}$/'],
            'bic' => ['nullable', 'string', 'max:11'],
            'payee_name' => ['required', 'string', 'max:255'],
            'issuer_name' => ['required', 'string', 'max:255'],
            'issuer_address' => ['required', 'string', 'max:255'],
            'issuer_company_id' => ['nullable', 'string', 'max:20'],
            'issuer_tax_id' => ['nullable', 'string', 'max:20'],
            'issuer_vat_id' => ['nullable', 'string', 'max:20'],
            'accommodation_url' => ['nullable', 'url', 'max:255'],
            'privacy_url' => ['nullable', 'url', 'max:255'],

            'categories' => ['required', 'array', 'min:1'],
            'categories.*.code' => ['required', 'distinct', Rule::enum(ParticipationCategoryCode::class)],
            'categories.*.name' => ['required', 'string', 'max:255'],
            'categories.*.price' => ['required', 'string', 'regex:/^\d{1,7}([.,]\d{1,2})?$/'],
            'categories.*.submission_requirement' => ['required', Rule::enum(SubmissionRequirement::class)],

            'industries' => ['array'],
            'industries.*.value' => ['required', 'string', 'max:80', 'distinct'],
            'industries.*.label' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public function handle(array $input): Conference
    {
        // JSON numbers are accepted for prices, the rule works on strings.
        foreach ($input['categories'] ?? [] as $i => $category) {
            if (isset($category['price']) && is_numeric($category['price'])) {
                $input['categories'][$i]['price'] = (string) $category['price'];
            }
        }

        $data = $this->validate($input);

        return DB::transaction(function () use ($data) {
            $conference = Conference::query()->create(collect($data)->except(['categories', 'industries'])->all());

            $this->context->runForConference($conference->id, function () use ($conference, $data) {
                foreach (array_values($data['categories']) as $sort => $category) {
                    ParticipationCategory::query()->create([
                        'conference_id' => $conference->id,
                        'code' => $category['code'],
                        'name' => $category['name'],
                        'price_cents' => Money::parse($category['price'], $data['currency'])->cents,
                        'submission_requirement' => $category['submission_requirement'],
                        'sort' => $sort,
                    ]);
                }

                foreach (array_values($data['industries'] ?? []) as $sort => $industry) {
                    FormField::query()->create([
                        'conference_id' => $conference->id,
                        'field' => FormField::INDUSTRY,
                        'value' => $industry['value'],
                        'label' => $industry['label'],
                        'sort' => $sort,
                    ]);
                }
            });

            return $conference;
        });
    }
}
