<?php

namespace App\Modules\Reporting\Actions;

use App\Modules\Payments\Actions\GetPaymentBalance;
use App\Modules\Registration\Models\Registration;
use App\Modules\Reporting\Exports\RegistrationsExport;
use App\Modules\Submissions\Models\Submission;
use App\Modules\Workflow\Enums\RegistrationStatus;
use App\Modules\Workflow\Enums\SubmissionStatus;
use App\Shared\Actions\Action;
use App\Shared\Auth\AccessControl;
use App\Shared\Auth\Permission;
use App\Shared\Conference\ConferenceContext;
use App\Shared\Files\FileContent;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;

/**
 * XLSX / CSV of the ACTIVE conference, filtered by statuses. Columns by role (US-15):
 * without registrations.view_finance (programme committee) only basic data and the
 * submission — no prices, payments, billing data or VS.
 */
final class ExportRegistrations extends Action
{
    public const FORMAT_XLSX = 'xlsx';

    public const FORMAT_CSV = 'csv';

    public function __construct(
        private readonly ConferenceContext $context,
        private readonly AccessControl $access,
        private readonly GetPaymentBalance $balance,
    ) {
    }

    public static function rules(): array
    {
        return [
            'format' => ['required', Rule::in([self::FORMAT_XLSX, self::FORMAT_CSV])],
            'registration_statuses' => ['array'],
            'registration_statuses.*' => [Rule::enum(RegistrationStatus::class)],
            'submission_statuses' => ['array'],
            'submission_statuses.*' => [Rule::enum(SubmissionStatus::class)],
        ];
    }

    /**
     * @param  array{format:string, registration_statuses?:list<string>, submission_statuses?:list<string>}  $input
     */
    public function handle(Authenticatable $actor, array $input): FileContent
    {
        $conferenceId = $this->context->requireConferenceId();
        $this->authorize($actor, Permission::ExportsRegistrations, $conferenceId);
        $data = $this->validate($input);

        $finance = $this->access->can($actor, Permission::RegistrationsViewFinance, $conferenceId);

        $query = Registration::query()
            ->with(['profile', 'user', 'conference'])
            ->orderBy('id');

        // Filtering by registration status is part of the organizer view only.
        if ($finance && ! empty($data['registration_statuses'])) {
            $query->whereIn('status', $data['registration_statuses']);
        }

        if (! empty($data['submission_statuses'])) {
            $query->whereIn('id', Submission::query()
                ->whereIn('status', $data['submission_statuses'])
                ->select('registration_id'));
        }

        $registrations = $query->get();
        $submissions = Submission::query()
            ->whereIn('registration_id', $registrations->pluck('id'))
            ->get()
            ->keyBy('registration_id');

        $rows = $registrations->map(function (Registration $r) use ($finance, $submissions) {
            $submission = $submissions->get($r->id);
            $row = [
                $r->profile?->last_name,
                $r->profile?->first_name,
                $r->profile?->title_before,
                $r->profile?->title_after,
                $r->profile?->organization,
                $r->user->email,
                __('categories.'.$r->category_code->value),
                $submission?->type?->value,
                $submission?->title,
                $submission?->status?->value,
            ];

            if ($finance) {
                $balance = $this->balance->handle($r);
                array_push(
                    $row,
                    $r->status?->value,
                    $r->industry,
                    $r->variable_symbol,
                    $balance->price->decimal(),
                    $r->currency,
                    $r->fee_waived ? 'yes' : 'no',
                    $r->payment_status?->value,
                    $balance->paid->decimal(),
                    $balance->remaining->decimal(),
                    $r->billing_type->value,
                    $r->billing_name,
                    $r->billing_street,
                    $r->billing_city,
                    $r->billing_postal_code,
                    $r->billing_country,
                    $r->billing_company_id,
                    $r->billing_tax_id,
                    $r->billing_vat_id,
                );
            }

            return $row;
        })->all();

        $headings = array_map(fn (string $key) => __('exports.columns.'.$key), $this->columns($finance));
        $format = $data['format'];
        $subdomain = $registrations->first()?->conference?->subdomain ?? 'conference';

        return new FileContent(
            Excel::raw(new RegistrationsExport($headings, $rows), $format === self::FORMAT_CSV ? ExcelWriter::CSV : ExcelWriter::XLSX),
            sprintf('registrations-%s-%s.%s', $subdomain, now()->format('Ymd-His'), $format),
            $format === self::FORMAT_CSV
                ? 'text/csv'
                : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        );
    }

    /**
     * @return list<string>
     */
    public function columns(bool $finance): array
    {
        $basic = [
            'last_name', 'first_name', 'title_before', 'title_after', 'organization', 'email',
            'category', 'submission_type', 'submission_title', 'submission_status',
        ];

        if (! $finance) {
            return $basic;
        }

        return array_merge($basic, [
            'registration_status', 'industry', 'variable_symbol', 'price', 'currency', 'fee_waived',
            'payment_status', 'paid', 'remaining', 'billing_type', 'billing_name', 'billing_street',
            'billing_city', 'billing_postal_code', 'billing_country', 'billing_company_id',
            'billing_tax_id', 'billing_vat_id',
        ]);
    }
}
