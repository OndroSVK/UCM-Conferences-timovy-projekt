<?php

namespace App\Modules\Submissions\Actions;

use App\Modules\Submissions\Models\SubmissionVersion;
use App\Shared\Actions\Action;
use App\Shared\Auth\Permission;
use App\Shared\Files\FileDownload;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Download of a submission version: the author, or organizer / committee / superadmin
 * of that conference (submissions.download).
 */
final class DownloadSubmissionFile extends Action
{
    public function handle(Authenticatable $actor, SubmissionVersion $version): FileDownload
    {
        $submission = $version->submission()->firstOrFail();
        $registration = $submission->registration()->firstOrFail();

        $this->authorizeOwnerOr($actor, $registration->user_id, Permission::SubmissionsDownload, $submission->conference_id);

        return new FileDownload(
            (string) config('conferences.submissions.disk'),
            $version->path,
            sprintf('submission-%d-v%d.pdf', $submission->id, $version->version),
            'application/pdf',
        );
    }
}
