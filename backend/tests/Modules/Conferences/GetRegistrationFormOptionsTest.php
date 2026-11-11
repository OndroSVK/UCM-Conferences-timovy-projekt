<?php

use App\Modules\Conferences\Actions\GetRegistrationFormOptions;

it('returns categories with prices and option lists of the conference only', function () {
    $ans27 = makeConference(2027);
    makeConference(2028);

    $options = app(GetRegistrationFormOptions::class)->handle($ans27);

    expect($options['categories'])->toHaveCount(3)
        ->and($options['categories'][0]['code'])->toBe('speaker')
        ->and($options['categories'][0]['price']->format())->toBe('180.00 EUR')
        ->and(array_column($options['fields']['industry'], 'value'))->toBe(['physics', 'chemistry', 'biology']);
});
