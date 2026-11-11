<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Models\User;
use App\Modules\Conferences\Actions\AssignAdminToConference;
use App\Shared\Auth\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Superadmin assigns staff to the active conference (BR4, BR8). */
final class TeamController extends Controller
{
    public function index(Request $request, AssignAdminToConference $assign)
    {
        return view('admin.team', [
            'assignments' => $assign->assignments($request->user(), $request->attributes->get('conference')),
        ]);
    }

    public function store(Request $request, AssignAdminToConference $assign)
    {
        $data = $request->validate(['email' => ['required', 'email', Rule::exists('users', 'email')]]);
        $user = User::query()->where('email', strtolower($data['email']))->firstOrFail();

        $assign->handle($request->user(), $request->attributes->get('conference'), [
            'user_id' => $user->id,
            'role' => (string) $request->input('role'),
            'extra_permissions' => array_values((array) $request->input('extra_permissions', [])),
        ]);

        return redirect()->route('admin.team.index')->with('status', __('ui.admin.team_saved'));
    }

    public function destroy(Request $request, AssignAdminToConference $assign)
    {
        $assign->revoke(
            $request->user(),
            $request->attributes->get('conference'),
            (int) $request->input('user_id'),
            Role::from((string) $request->input('role')),
        );

        return redirect()->route('admin.team.index')->with('status', __('ui.admin.team_saved'));
    }
}
