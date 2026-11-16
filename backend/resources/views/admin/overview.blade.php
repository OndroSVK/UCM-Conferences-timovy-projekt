@extends('layouts.admin', ['title' => __('ui.dashboard.overview')])

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">{{ $activeConference->name }}</div>
            <h1>{{ __('ui.dashboard.overview') }}</h1>
            <p>{{ $activeConference->starts_on->format('j. n. Y') }} – {{ $activeConference->ends_on->format('j. n. Y') }} · {{ $activeConference->venue }}</p>
        </div>
        <a class="btn primary" href="{{ route('admin.registrations.index') }}">{{ __('ui.admin.registrations') }} →</a>
    </div>
    <section class="stats-grid" aria-label="{{ __('ui.dashboard.overview') }}">
        @foreach ($counts as $key => $count)
            <article class="card stat">
                <div class="stat-label">{{ __('ui.dashboard.'.$key) }}</div>
                <div class="stat-value">{{ $count }}</div>
            </article>
        @endforeach
    </section>
    <div class="two-grid">
        @include('partials.deadlines', ['conference' => $activeConference])
        <section class="card stack">
            <h2>{{ __('ui.dashboard.next') }}</h2>
            <a class="btn flat" href="{{ route('admin.submissions.index', ['status' => 'submitted']) }}">{{ __('ui.dashboard.awaiting_decision') }} →</a>
            <a class="btn flat" href="{{ route('admin.exports.show') }}">{{ __('ui.admin.exports') }} →</a>
            @if ($isSuperadmin)<a class="btn flat" href="{{ route('admin.team.index') }}">{{ __('ui.admin.team') }} →</a>@endif
        </section>
    </div>
@endsection
