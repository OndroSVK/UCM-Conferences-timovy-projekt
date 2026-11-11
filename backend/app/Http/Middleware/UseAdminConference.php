<?php

namespace App\Http\Middleware;

use App\Modules\Conferences\Actions\ListAccessibleConferences;
use App\Modules\Conferences\Actions\SwitchActiveConference;
use App\Shared\Auth\AccessControl;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin: only staff; the active conference comes from the session switcher and is
 * re-checked on every request (BR4). Without a stored choice the newest accessible
 * conference is used.
 */
final class UseAdminConference
{
    public const SESSION_KEY = 'admin_conference_id';

    public function __construct(
        private readonly AccessControl $access,
        private readonly ListAccessibleConferences $list,
        private readonly SwitchActiveConference $switch,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $this->access->isStaff($user)) {
            throw new AuthorizationException(__('errors.forbidden'));
        }

        $conferences = $this->list->handle($user);
        $conferenceId = (int) $request->session()->get(self::SESSION_KEY);

        if (! $conferences->contains('id', $conferenceId)) {
            $conferenceId = (int) $conferences->first()?->id;
        }

        $active = $conferenceId ? $this->switch->handle($user, $conferenceId) : null;
        $request->session()->put(self::SESSION_KEY, $active?->id);
        $request->attributes->set('conference', $active);

        View::share('adminConferences', $conferences);
        View::share('activeConference', $active);
        View::share('isSuperadmin', $this->access->isSuperadmin($user));
        View::share('permissions', $active ? array_map(fn ($p) => $p->value, $this->access->permissions($user, $active->id)) : []);

        if ($active === null) {
            return response()->view('admin.no-conference', status: 409);
        }

        return $next($request);
    }
}
