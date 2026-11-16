<?php

namespace App\Http\Controllers;

use App\Modules\Invoicing\Actions\DownloadInvoice;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\ParticipantPortal\Actions\GetMyRegistrationDetail;
use App\Modules\ParticipantPortal\Actions\ListMyConferences;
use App\Modules\ParticipantPortal\Actions\ListMySubmissions;
use App\Modules\ParticipantPortal\Actions\UpdateMyProfile;
use App\Modules\Registration\Models\ParticipantProfile;
use App\Modules\Submissions\Actions\DownloadSubmissionFile;
use App\Modules\Submissions\Actions\ReplaceSubmissionFile;
use App\Modules\Submissions\Actions\SubmitSubmission;
use App\Modules\Submissions\Models\Submission;
use App\Modules\Submissions\Models\SubmissionVersion;
use App\Modules\Workflow\Actions\GetStatusHistory;
use Illuminate\Http\Request;

/** User page (M4). The owner context limits every query to own conferences (BO7). */
final class AccountController extends Controller
{
    public function index(Request $request, ListMyConferences $list)
    {
        return view('account.index', ['registrations' => $list->handle($request->user())]);
    }

    public function show(Request $request, GetMyRegistrationDetail $detail, GetStatusHistory $history, int $registration)
    {
        $data = $detail->handle($request->user(), $registration);

        return view('account.registration', $data + [
            'history' => $history->handle($request->user(), $data['registration']),
        ]);
    }

    public function profile(Request $request)
    {
        return view('account.profile', [
            'profile' => ParticipantProfile::query()->where('user_id', $request->user()->id)->first(),
        ]);
    }

    public function submissions(Request $request, ListMySubmissions $list)
    {
        return view('account.submissions', ['submissions' => $list->handle($request->user())]);
    }

    public function updateProfile(Request $request, UpdateMyProfile $update)
    {
        $update->handle($request->user(), $request->only([
            'first_name', 'last_name', 'title_before', 'title_after', 'organization', 'email',
        ]));

        return redirect()->route('account.profile')->with('status', __('ui.profile.saved'));
    }

    public function submit(Request $request, SubmitSubmission $submit, int $submission)
    {
        $model = Submission::query()->findOrFail($submission);
        $submit->handle($request->user(), $model, ['title' => $request->input('title'), 'file' => $request->file('file')]);

        return redirect()->route('account.registrations.show', $model->registration_id)->with('status', __('ui.submission.submitted'));
    }

    public function replace(Request $request, ReplaceSubmissionFile $replace, int $submission)
    {
        $model = Submission::query()->findOrFail($submission);
        $replace->handle($request->user(), $model, ['file' => $request->file('file')]);

        return redirect()->route('account.registrations.show', $model->registration_id)->with('status', __('ui.submission.replaced'));
    }

    public function downloadSubmission(Request $request, DownloadSubmissionFile $download, int $version)
    {
        return $this->sendFile($download->handle($request->user(), SubmissionVersion::query()->findOrFail($version)));
    }

    public function downloadInvoice(Request $request, DownloadInvoice $download, int $invoice)
    {
        return $this->sendFile($download->handle($request->user(), Invoice::query()->findOrFail($invoice)));
    }
}
