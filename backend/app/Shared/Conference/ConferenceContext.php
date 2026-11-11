<?php

namespace App\Shared\Conference;

use Closure;

/**
 * Active conference for the current request or job (backend.md 4.1).
 *
 * Modes:
 *  - none:       every query on a conference-bound model fails (MissingConferenceContext)
 *  - conference: queries are limited to one conference
 *  - owner:      participant portal; limited to conferences where the user is registered (BO7)
 *  - all:        superadmin only, explicitly requested
 */
final class ConferenceContext
{
    public const MODE_NONE = 'none';

    public const MODE_CONFERENCE = 'conference';

    public const MODE_OWNER = 'owner';

    public const MODE_ALL = 'all';

    private string $mode = self::MODE_NONE;

    private ?int $conferenceId = null;

    private ?int $ownerUserId = null;

    public function setConference(int $conferenceId): void
    {
        $this->mode = self::MODE_CONFERENCE;
        $this->conferenceId = $conferenceId;
        $this->ownerUserId = null;
    }

    // PROPOSAL: BO7 — participant portal reads own registrations across conferences.
    public function setOwner(int $userId): void
    {
        $this->mode = self::MODE_OWNER;
        $this->conferenceId = null;
        $this->ownerUserId = $userId;
    }

    /**
     * Only for superadmin; callers must verify the role before calling (see SuperadminScope action helpers).
     */
    public function setAll(): void
    {
        $this->mode = self::MODE_ALL;
        $this->conferenceId = null;
        $this->ownerUserId = null;
    }

    public function clear(): void
    {
        $this->mode = self::MODE_NONE;
        $this->conferenceId = null;
        $this->ownerUserId = null;
    }

    public function mode(): string
    {
        return $this->mode;
    }

    public function isSet(): bool
    {
        return $this->mode !== self::MODE_NONE;
    }

    public function conferenceId(): ?int
    {
        return $this->conferenceId;
    }

    public function requireConferenceId(): int
    {
        if ($this->mode !== self::MODE_CONFERENCE || $this->conferenceId === null) {
            throw new MissingConferenceContext('An active conference is required for this operation.');
        }

        return $this->conferenceId;
    }

    public function ownerUserId(): ?int
    {
        return $this->ownerUserId;
    }

    /**
     * Runs the callback inside a single conference and restores the previous context afterwards.
     * Used by queued listeners, which receive the conference id in their event.
     *
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    public function runForConference(int $conferenceId, Closure $callback): mixed
    {
        return $this->runWith(fn () => $this->setConference($conferenceId), $callback);
    }

    /**
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    public function runAsOwner(int $userId, Closure $callback): mixed
    {
        return $this->runWith(fn () => $this->setOwner($userId), $callback);
    }

    private function runWith(Closure $apply, Closure $callback): mixed
    {
        $previous = [$this->mode, $this->conferenceId, $this->ownerUserId];
        $apply();

        try {
            return $callback();
        } finally {
            [$this->mode, $this->conferenceId, $this->ownerUserId] = $previous;
        }
    }
}
