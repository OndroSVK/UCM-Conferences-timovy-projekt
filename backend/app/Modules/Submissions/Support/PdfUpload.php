<?php

namespace App\Modules\Submissions\Support;

use App\Modules\Submissions\Models\Submission;
use App\Modules\Submissions\Models\SubmissionVersion;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * PDF only (R14): MIME type detected from the content AND the "%PDF-" header,
 * size limit BO5. Stores the file on the private disk as a new version.
 * Shared by SubmitSubmission and ReplaceSubmissionFile.
 */
final class PdfUpload
{
    public function validate(mixed $file): UploadedFile
    {
        Validator::make(['file' => $file], [
            'file' => [
                'required',
                'file',
                'mimetypes:application/pdf',
                'max:'.(int) config('conferences.submissions.max_kilobytes'),
            ],
        ])->validate();

        /** @var UploadedFile $file */
        $handle = fopen($file->getRealPath(), 'rb');
        $header = $handle === false ? '' : (string) fread($handle, 5);
        if ($handle !== false) {
            fclose($handle);
        }

        if ($header !== '%PDF-') {
            throw ValidationException::withMessages(['file' => __('submissions.not_a_pdf')]);
        }

        return $file;
    }

    public function storeVersion(Submission $submission, UploadedFile $file, int $version, Authenticatable $actor): SubmissionVersion
    {
        $disk = (string) config('conferences.submissions.disk');
        $directory = "submissions/{$submission->conference_id}/{$submission->id}";
        $name = sprintf('v%d-%s.pdf', $version, Str::random(16));

        $path = Storage::disk($disk)->putFileAs($directory, $file, $name);

        return SubmissionVersion::query()->create([
            'conference_id' => $submission->conference_id,
            'submission_id' => $submission->id,
            'version' => $version,
            'path' => $path,
            'original_name' => Str::limit($file->getClientOriginalName(), 250, ''),
            'size_bytes' => (int) $file->getSize(),
            'sha256' => hash_file('sha256', $file->getRealPath()),
            'uploaded_by' => $actor->getAuthIdentifier(),
            'submitted_at' => now(),
        ]);
    }
}
