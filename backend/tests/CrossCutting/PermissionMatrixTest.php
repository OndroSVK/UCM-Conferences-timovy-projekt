<?php

use App\Shared\Auth\AccessControl;
use App\Shared\Auth\Permission;
use App\Shared\Auth\Role;

// backend.md 9 — allowed AND forbidden combinations. Participants act through ownership checks
// (tested per feature), so they have no permissions here.

dataset('matrix', [
    //                                  participant organizer delegated committee superadmin
    'registrations.view' => [Permission::RegistrationsView, false, true, true, true, true],
    'registrations.view_finance' => [Permission::RegistrationsViewFinance, false, true, true, false, true],
    'registrations.change_status' => [Permission::RegistrationsChangeStatus, false, true, true, false, true],
    'payments.record' => [Permission::PaymentsRecord, false, false, true, false, true],
    'payments.waive' => [Permission::PaymentsWaive, false, false, true, false, true],
    'invoices.view' => [Permission::InvoicesView, false, true, true, false, true],
    'invoices.retry' => [Permission::InvoicesRetry, false, false, true, false, true],
    'submissions.download' => [Permission::SubmissionsDownload, false, true, true, true, true],
    'submissions.decide' => [Permission::SubmissionsDecide, false, false, false, true, true],
    'exports.registrations' => [Permission::ExportsRegistrations, false, true, true, true, true],
    'conferences.assign_admins' => [Permission::ConferencesAssignAdmins, false, false, false, false, true],
]);

it('matches the permission matrix in the assigned conference', function (
    Permission $permission,
    bool $participant,
    bool $organizer,
    bool $delegated,
    bool $committee,
    bool $superadmin,
) {
    $conference = makeConference(2027);
    ['user' => $participantUser] = registerParticipant($conference);
    $access = app(AccessControl::class);

    $actors = [
        'participant' => [$participantUser, $participant],
        'organizer' => [staff($conference, Role::Organizer), $organizer],
        'delegated organizer' => [paymentsOrganizer($conference), $delegated],
        'committee' => [staff($conference, Role::ProgrammeCommittee), $committee],
        'superadmin' => [superadmin(), $superadmin],
    ];

    foreach ($actors as $name => [$user, $expected]) {
        expect($access->can($user, $permission, $conference->id))
            ->toBe($expected, "{$name} / {$permission->value}");
    }
})->with('matrix');

it('gives staff no permission in a conference they are not assigned to', function (Permission $permission) {
    $assigned = makeConference(2027);
    $other = makeConference(2028);
    $access = app(AccessControl::class);

    foreach ([paymentsOrganizer($assigned), staff($assigned, Role::ProgrammeCommittee)] as $user) {
        expect($access->can($user, $permission, $other->id))->toBeFalse();
    }
})->with(Permission::cases());
