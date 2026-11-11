<?php

use App\Modules\Reporting\Actions\ExportRegistrations;
use App\Modules\Workflow\Actions\ChangeStatus;
use App\Modules\Workflow\Enums\RegistrationStatus;
use App\Shared\Auth\Role;
use App\Shared\Conference\MissingConferenceContext;
use Illuminate\Auth\Access\AuthorizationException;

beforeEach(function () {
    $this->ans27 = makeConference(2027);
    $this->ans28 = makeConference(2028);
    registerParticipant($this->ans27, 'speaker', ['last_name' => 'Alfa', 'billing_name' => 'Secret Billing Ltd']);
    registerParticipant($this->ans27, 'passive_participant', ['last_name' => 'Beta']);
    registerParticipant($this->ans28, 'speaker', ['last_name' => 'Other-Conference']);
    inConference($this->ans27);
});

function csvRows(string $csv): array
{
    return array_map('str_getcsv', array_values(array_filter(explode("\n", trim($csv)))));
}

// US-15
it('committee export contains only basic data and the submission status of the current conference', function () {
    $file = app(ExportRegistrations::class)->handle(staff($this->ans27, Role::ProgrammeCommittee), ['format' => 'csv']);
    $rows = csvRows($file->content);

    expect($rows[0])->toBe(array_map(fn ($c) => __('exports.columns.'.$c), app(ExportRegistrations::class)->columns(false)))
        ->and(count($rows))->toBe(3)
        ->and($file->content)->not->toContain('Secret Billing')
        ->and($file->content)->not->toContain('Other-Conference')
        ->and($file->content)->not->toContain('27000001')
        ->and($file->content)->not->toContain('"180"');
});

it('organizer export contains all columns including billing and payments', function () {
    $file = app(ExportRegistrations::class)->handle(staff($this->ans27, Role::Organizer), ['format' => 'csv']);

    expect($file->content)->toContain('Secret Billing Ltd')
        ->and($file->content)->toContain('"180"') // amounts are numeric cells
        ->and($file->content)->toContain('27000001')
        ->and(csvRows($file->content)[0])->toHaveCount(28);
});

it('filters by registration status', function () {
    $organizer = staff($this->ans27, Role::Organizer);
    $beta = \App\Modules\Registration\Models\Registration::query()->orderByDesc('id')->first();
    app(ChangeStatus::class)->byStaff($organizer, $beta, RegistrationStatus::Cancelled);

    $file = app(ExportRegistrations::class)->handle($organizer, ['format' => 'csv', 'registration_statuses' => ['received']]);

    expect($file->content)->toContain('Alfa')->and($file->content)->not->toContain('Beta');
});

it('produces an XLSX file', function () {
    $file = app(ExportRegistrations::class)->handle(staff($this->ans27, Role::Organizer), ['format' => 'xlsx']);

    expect(substr($file->content, 0, 2))->toBe('PK') // zip container
        ->and($file->filename)->toEndWith('.xlsx');
});

it('refuses a participant', function () {
    ['user' => $participant] = registerParticipant($this->ans27);
    inConference($this->ans27);

    app(ExportRegistrations::class)->handle($participant, ['format' => 'csv']);
})->throws(AuthorizationException::class);

it('requires an active conference', function () {
    conferenceContext()->clear();

    app(ExportRegistrations::class)->handle(superadmin(), ['format' => 'csv']);
})->throws(MissingConferenceContext::class);
