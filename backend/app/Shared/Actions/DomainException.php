<?php

namespace App\Shared\Actions;

use RuntimeException;

/**
 * A business rule refused the operation. The message is a translated, user-safe text.
 */
class DomainException extends RuntimeException
{
}
