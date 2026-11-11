<?php

use App\Modules\Registration\Actions\CalculateRegistrationPrice;
use App\Shared\Actions\DomainException;

it('takes the price from the category of the given conference', function () {
    $ans27 = makeConference(2027);
    $ans28 = makeConference(2028);
    inConference($ans28);
    \App\Modules\Conferences\Models\ParticipationCategory::query()->where('code', 'speaker')->update(['price_cents' => 20000]);

    $price = app(CalculateRegistrationPrice::class);

    expect($price->handle($ans27, 'speaker')->cents)->toBe(18000)
        ->and($price->handle($ans28, 'speaker')->cents)->toBe(20000)
        ->and($price->handle($ans27, 'speaker', feeWaived: true)->cents)->toBe(0);
});

it('refuses an unknown category', function () {
    app(CalculateRegistrationPrice::class)->handle(makeConference(2027), 'vip');
})->throws(DomainException::class);
