<?php

namespace App\Modules\Notifications\Models;

use App\Modules\Notifications\Enums\EmailType;
use App\Shared\Conference\BelongsToConference;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int|null $registration_id
 * @property string $recipient
 * @property EmailType $type
 * @property string $result
 * @property string|null $error
 */
class EmailLog extends Model
{
    use BelongsToConference;

    public const UPDATED_AT = null;

    public const SENT = 'sent';

    public const FAILED = 'failed';

    protected $table = 'email_log';

    protected $fillable = ['conference_id', 'registration_id', 'recipient', 'type', 'result', 'error'];

    protected function casts(): array
    {
        return ['type' => EmailType::class, 'created_at' => 'datetime'];
    }
}
