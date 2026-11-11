<?php

namespace App\Modules\Reporting\Actions;

use App\Modules\Registration\Models\Registration;
use App\Modules\Workflow\Enums\RegistrationStatus;
use App\Shared\Actions\Action;
use App\Shared\Auth\AccessControl;
use App\Shared\Auth\Permission;
use App\Shared\Conference\ConferenceContext;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;

/**
 * Admin list of registrations of the ACTIVE conference (read-only, added for the admin UI).
 * Search by name, organization, e-mail or VS; VS search and payment columns only with
 * the finance permission.
 */
final class ListConferenceRegistrations extends Action
{
    public function __construct(
        private readonly ConferenceContext $context,
        private readonly AccessControl $access,
    ) {
    }

    public static function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(RegistrationStatus::class)],
            'category' => ['nullable', 'string', 'max:40'],
        ];
    }

    /**
     * @param  array{q?:string|null, status?:string|null, category?:string|null}  $filters
     */
    public function handle(Authenticatable $actor, array $filters = []): LengthAwarePaginator
    {
        $conferenceId = $this->context->requireConferenceId();
        $this->authorize($actor, Permission::RegistrationsView, $conferenceId);
        $data = $this->validate($filters);
        $finance = $this->access->can($actor, Permission::RegistrationsViewFinance, $conferenceId);

        $query = Registration::query()
            ->with(['profile', 'user'])
            ->orderByDesc('id');

        if (! empty($data['q'])) {
            $term = '%'.str_replace(['%', '_'], ['\%', '\_'], $data['q']).'%';
            $query->where(function ($q) use ($term, $finance, $data) {
                $q->whereHas('profile', fn ($p) => $p->where('last_name', 'like', $term)
                    ->orWhere('first_name', 'like', $term)
                    ->orWhere('organization', 'like', $term))
                    ->orWhereHas('user', fn ($u) => $u->where('email', 'like', $term));

                if ($finance) {
                    $q->orWhere('variable_symbol', preg_replace('/\D/', '', $data['q']) ?: '-');
                }
            });
        }

        if (! empty($data['status'])) {
            $query->where('status', $data['status']);
        }

        if (! empty($data['category'])) {
            $query->where('category_code', $data['category']);
        }

        return $query->paginate(25)->withQueryString();
    }
}
