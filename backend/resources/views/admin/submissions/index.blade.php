@extends('layouts.admin', ['title' => __('ui.admin.submissions')])

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">{{ $activeConference->name }}</div>
            <h1>{{ __('ui.admin.submissions') }}</h1>
        </div>
    </div>

    <section class="panel">
        <form method="get" class="toolbar">
            <div class="filters">
                <select name="status" aria-label="{{ __('ui.admin.all_statuses') }}">
                    <option value="">{{ __('ui.admin.all_statuses') }}</option>
                    @foreach (['draft', 'submitted', 'accepted', 'conditionally_accepted', 'rejected'] as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ __('statuses.submission.'.$status) }}</option>
                    @endforeach
                </select>
                <button class="btn flat">{{ __('ui.admin.filter') }}</button>
            </div>
            <small class="muted">{{ $submissions->total() }}</small>
        </form>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th scope="col">{{ __('ui.account.submission_title') }}</th>
                    <th scope="col">{{ __('ui.admin.participant') }}</th>
                    <th scope="col">{{ __('ui.account.submission') }}</th>
                    <th scope="col"></th>
                </tr>
                </thead>
                <tbody>
                @forelse ($submissions as $submission)
                    <tr>
                        <td><strong>{{ $submission->title ?? __('ui.none') }}</strong>
                            <span class="secondary">{{ $submission->type?->value }}</span></td>
                        <td>{{ $submission->registration->profile?->fullName() }}</td>
                        <td>@include('partials.status', ['category' => 'submission', 'value' => $submission->status])</td>
                        <td><a class="link" href="{{ route('admin.submissions.show', $submission->id) }}">{{ __('ui.open') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4">{{ __('ui.admin.empty') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="table-footer">{{ $submissions->links('partials.pagination') }}</div>
    </section>
@endsection
