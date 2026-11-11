<?php

namespace App\Modules\Workflow\Exceptions;

use App\Shared\Actions\DomainException;

final class InvalidStatusTransition extends DomainException
{
}
