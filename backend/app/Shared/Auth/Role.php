<?php

namespace App\Shared\Auth;

/**
 * Roles (backend.md 4.2). Participant and superadmin are global; staff roles are
 * assigned per conference in conference_user (BR4, BR8).
 */
enum Role: string
{
    case Participant = 'participant';
    case Organizer = 'organizer';
    case ProgrammeCommittee = 'programme_committee';
    case Superadmin = 'superadmin';
    /** Prepared for the full version, not used in the prototype. */
    case Economist = 'economist';
    /** Prepared for the full version, not used in the prototype. */
    case Reviewer = 'reviewer';

    public function isConferenceScoped(): bool
    {
        return in_array($this, [self::Organizer, self::ProgrammeCommittee, self::Economist, self::Reviewer], true);
    }

    /**
     * Roles a superadmin may assign to a conference in the prototype.
     *
     * @return list<self>
     */
    public static function assignableInPrototype(): array
    {
        return [self::Organizer, self::ProgrammeCommittee];
    }

    /**
     * @return list<Permission>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::Superadmin => Permission::cases(),
            self::Organizer => [
                Permission::RegistrationsView,
                Permission::RegistrationsViewFinance,
                Permission::RegistrationsChangeStatus,
                Permission::InvoicesView,
                Permission::SubmissionsView,
                Permission::SubmissionsDownload,
                Permission::ExportsRegistrations,
            ],
            self::ProgrammeCommittee => [
                Permission::RegistrationsView,
                Permission::SubmissionsView,
                Permission::SubmissionsDownload,
                Permission::SubmissionsDecide,
                Permission::ExportsRegistrations,
            ],
            // Participants act only on their own data (ownership checks), economist/reviewer: full version.
            self::Participant, self::Economist, self::Reviewer => [],
        };
    }
}
