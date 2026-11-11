<?php

namespace App\Modules\Workflow\Contracts;

/**
 * A model whose status columns are changed only by Workflow\Actions\ChangeStatus.
 * Implemented by Registration (registration + payment status) and Submission.
 */
interface HasWorkflowStatus
{
    public function workflowRegistrationId(): int;

    public function workflowConferenceId(): int;
}
