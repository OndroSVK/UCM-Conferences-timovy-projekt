<?php

namespace App\Modules\Workflow\Enums;

/**
 * Status enum with a transition map. Transitions are "from value" => list of "to values";
 * the key "" means "no status" and a null target is written as "".
 * Only transitions allowed in the prototype are listed (backend.md 7).
 */
interface WorkflowStatus
{
    /** @return array<string, list<string>> */
    public static function transitions(): array;

    /** @return list<self> */
    public static function initialStates(): array;
}
