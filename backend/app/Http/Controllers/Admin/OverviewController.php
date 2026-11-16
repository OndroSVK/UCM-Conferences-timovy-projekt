<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Reporting\Actions\GetConferenceOverview;
use Illuminate\Http\Request;

final class OverviewController extends Controller
{
    public function __invoke(Request $request, GetConferenceOverview $overview)
    {
        return view('admin.overview', ['counts' => $overview->handle($request->user())]);
    }
}
