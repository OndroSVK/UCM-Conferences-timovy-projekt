<?php

namespace App\Modules\Registration\Actions;

use App\Modules\Conferences\Models\Conference;
use App\Modules\Conferences\Models\ParticipationCategory;
use App\Shared\Actions\Action;
use App\Shared\Actions\DomainException;
use App\Shared\Conference\ConferenceContext;
use App\Shared\Money\Money;

/**
 * Price from the category of the given conference; fee waiver → 0.
 * Amounts come only from the database (R32).
 */
final class CalculateRegistrationPrice extends Action
{
    public function __construct(private readonly ConferenceContext $context)
    {
    }

    public function handle(Conference $conference, string $categoryCode, bool $feeWaived = false): Money
    {
        $category = $this->context->runForConference(
            $conference->id,
            fn () => ParticipationCategory::query()->where('code', $categoryCode)->first(),
        );

        if ($category === null) {
            throw new DomainException(__('errors.unknown_category'));
        }

        return $feeWaived ? Money::zero($conference->currency) : $category->price($conference->currency);
    }
}
