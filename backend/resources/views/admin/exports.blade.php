@extends('layouts.admin', ['title' => __('ui.admin.exports')])

@php($finance = in_array('registrations.view_finance', $permissions, true))

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">{{ $activeConference->name }}</div>
            <h1>{{ __('ui.admin.export_title') }}</h1>
            <p>{{ __('ui.admin.export_intro') }}</p>
        </div>
    </div>

    <form method="post" action="{{ route('admin.exports.download') }}" class="card stack">
        @csrf
        <fieldset style="border: 0; padding: 0; margin: 0">
            <legend>{{ __('ui.admin.format') }}</legend>
            <div class="radio-row" style="margin-top: 8px">
                <label><input type="radio" name="format" value="xlsx" checked> XLSX</label>
                <label><input type="radio" name="format" value="csv"> CSV</label>
            </div>
        </fieldset>
        @if ($finance)
            <fieldset style="border: 0; padding: 0; margin: 0">
                <legend>{{ __('ui.account.registration') }}</legend>
                <div class="radio-row" style="margin-top: 8px">
                    @foreach (\App\Modules\Workflow\Enums\RegistrationStatus::cases() as $status)
                        <label><input type="checkbox" name="registration_statuses[]" value="{{ $status->value }}"> {{ __('statuses.registration.'.$status->value) }}</label>
                    @endforeach
                </div>
            </fieldset>
        @endif
        <fieldset style="border: 0; padding: 0; margin: 0">
            <legend>{{ __('ui.account.submission') }}</legend>
            <div class="radio-row" style="margin-top: 8px">
                @foreach (['draft', 'not_required', 'submitted', 'accepted', 'conditionally_accepted', 'rejected'] as $status)
                    <label><input type="checkbox" name="submission_statuses[]" value="{{ $status }}"> {{ __('statuses.submission.'.$status) }}</label>
                @endforeach
            </div>
        </fieldset>
        <div class="form-actions"><button class="btn primary">{{ __('ui.admin.export') }}</button></div>
    </form>
@endsection
