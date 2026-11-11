<?php

namespace App\Modules\Payments\Actions;

use App\Modules\Registration\Models\Registration;
use App\Shared\Actions\Action;
use App\Shared\Auth\Permission;
use App\Shared\Conference\ConferenceContext;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Finds a registration by VS within the ACTIVE conference only (a VS of another
 * conference is not found even though VS are unique globally).
 */
final class FindRegistrationByVariableSymbol extends Action
{
    public function __construct(private readonly ConferenceContext $context)
    {
    }

    public function handle(Authenticatable $actor, string $variableSymbol): ?Registration
    {
        $conferenceId = $this->context->requireConferenceId();
        $this->authorize($actor, Permission::RegistrationsViewFinance, $conferenceId);

        $vs = preg_replace('/\D/', '', $variableSymbol) ?? '';
        if ($vs === '') {
            return null;
        }

        return Registration::query()->where('variable_symbol', $vs)->first();
    }
}
