@extends('layouts.account', ['title' => __('ui.account.my_submissions')])

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">{{ __('ui.account.nav') }}</div>
            <h1>{{ __('ui.account.my_submissions') }}</h1>
            <p>{{ __('ui.flow.submissions_intro') }}</p>
        </div>
    </div>
    <div class="conference-grid">
        @forelse ($submissions as $submission)
            <article class="card conference-card">
                <div class="eyebrow">{{ $submission->conference->name }}</div>
                <h2>{{ $submission->title ?? __('ui.flow.untitled') }}</h2>
                @include('partials.status', ['category' => 'submission', 'value' => $submission->status])
                <p>{{ __('ui.account.deadline', ['date' => $submission->conference->submission_deadline->format('j. n. Y')]) }}</p>
                <div class="actions">
                    <a class="btn primary" href="{{ route('account.registrations.show', $submission->registration_id) }}#submission">{{ __('ui.open') }}</a>
                    @if ($submission->latestVersion)
                        <a class="btn flat" href="{{ route('account.submissions.download', $submission->latestVersion->id) }}">{{ __('ui.download') }} PDF</a>
                    @endif
                </div>
            </article>
        @empty
            <div class="card empty"><h2>{{ __('ui.flow.no_submissions') }}</h2><p>{{ __('ui.flow.no_submissions_hint') }}</p></div>
        @endforelse
    </div>
@endsection
