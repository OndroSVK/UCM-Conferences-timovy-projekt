<?php

namespace App\Modules\Workflow\Actions;

use App\Modules\Workflow\Contracts\HasWorkflowStatus;
use App\Modules\Workflow\Enums\RegistrationStatus;
use App\Modules\Workflow\Enums\StatusCategory;
use App\Modules\Workflow\Events\StatusChanged;
use App\Modules\Workflow\Exceptions\InvalidStatusTransition;
use App\Modules\Workflow\Models\StatusHistory;
use App\Shared\Actions\Action;
use App\Shared\Auth\Permission;
use BackedEnum;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * The only way to change a status (backend.md 4.3). Checks the allowed transition
 * (map in the enum), locks the row, writes status_history and raises StatusChanged
 * after commit. The calling feature checks its own permission; byStaff() is the
 * admin entry point for registration status.
 */
final class ChangeStatus extends Action
{
    /**
     * Normal transition. $to = null means "no status" (only pending → null for a fee waiver).
     */
    public function handle(
        Model&HasWorkflowStatus $subject,
        StatusCategory $category,
        ?BackedEnum $to,
        ?Authenticatable $actor = null,
        ?string $note = null,
    ): void {
        $this->apply($subject, $category, $to, $actor, $note, initial: false);
    }

    /**
     * First status of a new record (— → received / pending / draft / not_required).
     * Allowed only once per record and category.
     */
    public function initialize(
        Model&HasWorkflowStatus $subject,
        StatusCategory $category,
        BackedEnum $to,
        ?Authenticatable $actor = null,
        ?string $note = null,
    ): void {
        $this->apply($subject, $category, $to, $actor, $note, initial: true);
    }

    /**
     * Organizer / superadmin changes the registration status (confirm, cancel).
     */
    public function byStaff(
        Authenticatable $actor,
        Model&HasWorkflowStatus $registration,
        RegistrationStatus $to,
        ?string $note = null,
    ): void {
        $this->authorize($actor, Permission::RegistrationsChangeStatus, $registration->workflowConferenceId());

        $this->apply($registration, StatusCategory::Registration, $to, $actor, $note, initial: false);
    }

    private function apply(
        Model&HasWorkflowStatus $subject,
        StatusCategory $category,
        ?BackedEnum $to,
        ?Authenticatable $actor,
        ?string $note,
        bool $initial,
    ): void {
        $enum = $category->enum();

        if ($to !== null && ! $to instanceof $enum) {
            throw new InvalidArgumentException(sprintf('Status of category [%s] must be %s.', $category->value, $enum));
        }

        DB::transaction(function () use ($subject, $category, $enum, $to, $actor, $note, $initial): void {
            $column = $category->column();

            /** @var Model&HasWorkflowStatus $locked */
            $locked = $subject->newQuery()->whereKey($subject->getKey())->lockForUpdate()->firstOrFail();
            $from = $locked->getRawOriginal($column);
            $toValue = $to?->value;

            if ($initial) {
                $alreadyHadStatus = $from !== null || StatusHistory::query()
                    ->where('subject_type', $subject->getMorphClass())
                    ->where('subject_id', $subject->getKey())
                    ->where('category', $category->value)
                    ->exists();

                if ($alreadyHadStatus || ! in_array($to, $enum::initialStates(), true)) {
                    throw $this->invalid($from, $toValue);
                }
            } elseif (! in_array($toValue ?? '', $enum::transitions()[$from ?? ''] ?? [], true)) {
                throw $this->invalid($from, $toValue);
            }

            $locked->forceFill([$column => $to])->save();
            $subject->forceFill([$column => $to])->syncOriginalAttribute($column);

            StatusHistory::query()->create([
                'conference_id' => $subject->workflowConferenceId(),
                'registration_id' => $subject->workflowRegistrationId(),
                'category' => $category->value,
                'subject_type' => $subject->getMorphClass(),
                'subject_id' => $subject->getKey(),
                'from_status' => $from,
                'to_status' => $toValue,
                'actor_id' => $actor?->getAuthIdentifier(),
                'note' => $note,
            ]);

            StatusChanged::dispatch(
                $category,
                $subject->getMorphClass(),
                (int) $subject->getKey(),
                $subject->workflowRegistrationId(),
                $subject->workflowConferenceId(),
                $from,
                $toValue,
                $actor?->getAuthIdentifier(),
            );
        });
    }

    private function invalid(?string $from, ?string $to): InvalidStatusTransition
    {
        return new InvalidStatusTransition(__('errors.invalid_transition', [
            'from' => $from ?? '—',
            'to' => $to ?? '—',
        ]));
    }
}
