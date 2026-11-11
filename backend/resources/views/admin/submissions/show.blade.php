@extends('layouts.admin', ['title' => $submission->title])

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow"><a class="link" href="{{ route('admin.submissions.index') }}">{{ __('ui.admin.submissions') }}</a></div>
            <h1>{{ $submission->title ?? __('ui.none') }}</h1>
            <p>{{ $submission->registration->profile?->fullName() }} · {{ $submission->registration->profile?->organization }}</p>
        </div>
        <div class="actions">@include('partials.status', ['category' => 'submission', 'value' => $submission->status])</div>
    </div>

    <section class="panel">
        <div class="panel-head"><h2>{{ __('ui.account.versions') }}</h2></div>
        <div class="panel-body">
            @forelse ($submission->versions as $version)
                <p><a class="link" href="{{ route('admin.submissions.download', $version->id) }}">{{ __('ui.account.version', ['number' => $version->version]) }}</a>
                    <span class="muted">· {{ $version->submitted_at->format('j. n. Y H:i') }}</span></p>
            @empty
                <p class="muted">{{ __('ui.none') }}</p>
            @endforelse
        </div>
    </section>

    @foreach ($submission->decisions as $decision)
        <div class="notice">
            <p><strong>{{ __('decisions.'.$decision->decision->value) }}</strong> · {{ __('ui.admin.decided_by', ['date' => $decision->decided_at->format('j. n. Y H:i')]) }}</p>
            <p style="white-space: pre-line">{{ $decision->justification }}</p>
        </div>
    @endforeach

    @if (in_array('submissions.decide', $permissions, true) && $submission->status === \App\Modules\Workflow\Enums\SubmissionStatus::Submitted)
        <section class="panel">
            <div class="panel-head"><h2>{{ __('ui.admin.decide') }}</h2></div>
            <form method="post" action="{{ route('admin.submissions.decide', $submission->id) }}" class="panel-body">
                @csrf
                <fieldset style="border: 0; padding: 0; margin: 0">
                    <legend>{{ __('ui.admin.decision') }}</legend>
                    <div class="radio-row" style="margin-top: 8px">
                        @foreach (\App\Modules\Submissions\Enums\DecisionType::cases() as $type)
                            <label><input type="radio" name="decision" value="{{ $type->value }}" @checked(old('decision') === $type->value) required> {{ __('decisions.'.$type->value) }}</label>
                        @endforeach
                    </div>
                </fieldset>
                <label class="field" style="margin-top: 14px">
                    {{ __('ui.admin.justification') }}
                    <textarea name="justification" rows="5" maxlength="5000" required>{{ old('justification') }}</textarea>
                </label>
                <div class="form-actions"><button class="btn primary">{{ __('ui.admin.record_decision') }}</button></div>
            </form>
        </section>
    @endif
@endsection
