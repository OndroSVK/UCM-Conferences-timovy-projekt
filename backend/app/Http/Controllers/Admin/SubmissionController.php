<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Reporting\Actions\ListConferenceSubmissions;
use App\Modules\Submissions\Actions\DownloadSubmissionFile;
use App\Modules\Submissions\Actions\RecordCommitteeDecision;
use App\Modules\Submissions\Models\Submission;
use App\Modules\Submissions\Models\SubmissionVersion;
use Illuminate\Http\Request;

/** Admin / committee: submissions, downloads, single-round decision (O37). */
final class SubmissionController extends Controller
{
    public function index(Request $request, ListConferenceSubmissions $list)
    {
        return view('admin.submissions.index', [
            'submissions' => $list->handle($request->user(), $request->only('status')),
            'filters' => $request->only('status'),
        ]);
    }

    public function show(Request $request, ListConferenceSubmissions $list, int $submission)
    {
        return view('admin.submissions.show', ['submission' => $list->find($request->user(), $submission)]);
    }

    public function decide(Request $request, RecordCommitteeDecision $decide, int $submission)
    {
        $decide->handle($request->user(), Submission::query()->findOrFail($submission), $request->only(['decision', 'justification']));

        return redirect()->route('admin.submissions.show', $submission)->with('status', __('ui.admin.decision_recorded'));
    }

    public function download(Request $request, DownloadSubmissionFile $download, int $version)
    {
        return $this->sendFile($download->handle($request->user(), SubmissionVersion::query()->findOrFail($version)));
    }
}
