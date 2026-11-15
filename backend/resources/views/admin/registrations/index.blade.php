@extends('layouts.admin', ['title' => __('ui.admin.registrations')])

@php($finance = in_array('registrations.view_finance', $permissions, true))

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">{{ $activeConference->name }}</div>
            <h1>{{ __('ui.admin.registrations') }}</h1>
        </div>
        @if ($finance)
            <form method="get" action="{{ route('admin.registrations.find') }}" class="actions">
                <label class="sr-only" for="vs">{{ __('ui.admin.find_vs') }}</label>
                <input id="vs" name="vs" inputmode="numeric" placeholder="{{ __('ui.admin.find_vs') }}" value="{{ old('vs') }}">
                <button class="btn primary">{{ __('ui.admin.find') }}</button>
            </form>
        @endif
    </div>

    <section class="panel">
        <form method="get" class="toolbar">
            <div class="filters">
                <label class="search">
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('ui.admin.search') }}" aria-label="{{ __('ui.admin.search') }}">
                </label>
                <select name="status" aria-label="{{ __('ui.admin.all_statuses') }}">
                    <option value="">{{ __('ui.admin.all_statuses') }}</option>
                    @foreach (\App\Modules\Workflow\Enums\RegistrationStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ __('statuses.registration.'.$status->value) }}</option>
                    @endforeach
                </select>
                <select name="category" aria-label="{{ __('ui.admin.all_categories') }}">
                    <option value="">{{ __('ui.admin.all_categories') }}</option>
                    @foreach (['speaker', 'poster_author', 'passive_participant'] as $code)
                        <option value="{{ $code }}" @selected(($filters['category'] ?? '') === $code)>{{ __('categories.'.$code) }}</option>
                    @endforeach
                </select>
                <button class="btn flat">{{ __('ui.admin.filter') }}</button>
            </div>
            <small class="muted">{{ $registrations->total() }}</small>
        </form>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th scope="col">{{ __('ui.admin.participant') }}</th>
                    <th scope="col">{{ __('ui.admin.organization') }}</th>
                    <th scope="col">{{ __('ui.account.category') }}</th>
                    <th scope="col">{{ __('ui.account.registration') }}</th>
                    @if ($finance)<th scope="col">{{ __('ui.account.payment') }}</th>@endif
                    <th scope="col"></th>
                </tr>
                </thead>
                <tbody>
                @forelse ($registrations as $registration)
                    <tr>
                        <td>
                            <strong>{{ $registration->profile?->fullName() }}</strong>
                            <span class="secondary">{{ $registration->user->email }}@if ($finance) · VS {{ $registration->variable_symbol }}@endif</span>
                        </td>
                        <td>{{ $registration->profile?->organization }}</td>
                        <td>{{ __('categories.'.$registration->category_code->value) }}</td>
                        <td>@include('partials.status', ['category' => 'registration', 'value' => $registration->status])</td>
                        @if ($finance)<td>@include('partials.status', ['category' => 'payment', 'value' => $registration->payment_status])</td>@endif
                        <td><a class="link" href="{{ route('admin.registrations.show', $registration->id) }}">{{ __('ui.open') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6">{{ __('ui.admin.empty') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="table-footer">{{ $registrations->links('partials.pagination') }}</div>
    </section>
@endsection
