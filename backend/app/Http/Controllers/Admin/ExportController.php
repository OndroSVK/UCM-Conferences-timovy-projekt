<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Reporting\Actions\ExportRegistrations;
use Illuminate\Http\Request;

/** XLSX / CSV export of the active conference; columns by role (US-15). */
final class ExportController extends Controller
{
    public function show()
    {
        return view('admin.exports');
    }

    public function download(Request $request, ExportRegistrations $export)
    {
        return $this->sendContent($export->handle($request->user(), [
            'format' => (string) $request->input('format', ExportRegistrations::FORMAT_XLSX),
            'registration_statuses' => array_values((array) $request->input('registration_statuses', [])),
            'submission_statuses' => array_values((array) $request->input('submission_statuses', [])),
        ]));
    }
}
