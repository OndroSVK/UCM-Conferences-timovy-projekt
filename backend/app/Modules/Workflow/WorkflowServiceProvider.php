<?php

namespace App\Modules\Workflow;

use App\Modules\Workflow\Events\StatusChanged;
use App\Modules\Workflow\Listeners\ConfirmRegistrationOnStatusChanged;
use App\Shared\Modules\ModuleServiceProvider;

/** M6 — status machines and status history. Owner: A. */
final class WorkflowServiceProvider extends ModuleServiceProvider
{
    protected function listeners(): array
    {
        return [
            StatusChanged::class => [ConfirmRegistrationOnStatusChanged::class],
        ];
    }
}
