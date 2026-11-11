<?php

namespace App\Modules\Conferences\Actions;

use App\Modules\Conferences\Models\Conference;
use App\Modules\Conferences\Models\FormField;
use App\Modules\Conferences\Models\ParticipationCategory;
use App\Shared\Actions\Action;
use App\Shared\Conference\ConferenceContext;

/**
 * Read-only options of the registration form of one conference: categories with
 * prices and option lists (e.g. industries).
 */
final class GetRegistrationFormOptions extends Action
{
    public function __construct(private readonly ConferenceContext $context)
    {
    }

    /**
     * @return array{
     *     conference: Conference,
     *     categories: list<array{code:string, name:string, price:\App\Shared\Money\Money, submission_requirement:string}>,
     *     fields: array<string, list<array{value:string, label:string}>>
     * }
     */
    public function handle(Conference $conference): array
    {
        return $this->context->runForConference($conference->id, function () use ($conference) {
            $categories = ParticipationCategory::query()->orderBy('sort')->get()
                ->map(fn (ParticipationCategory $c) => [
                    'code' => $c->code->value,
                    'name' => $c->name,
                    'price' => $c->price($conference->currency),
                    'submission_requirement' => $c->submission_requirement->value,
                ])->all();

            $fields = FormField::query()->orderBy('sort')->get()
                ->groupBy('field')
                ->map(fn ($options) => $options->map(fn (FormField $o) => [
                    'value' => $o->value,
                    'label' => $o->label,
                ])->values()->all())
                ->all();

            return [
                'conference' => $conference,
                'categories' => $categories,
                'fields' => $fields,
            ];
        });
    }
}
