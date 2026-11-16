@extends('layouts.account', ['title' => __('ui.account.conferences')])

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">{{ __('ui.account.nav') }}</div>
            <h1>{{ __('ui.dashboard.welcome', ['name' => auth()->user()->name]) }}</h1>
            <p>{{ __('ui.dashboard.account_intro') }}</p>
        </div>
        <a class="btn flat" href="{{ route('account.profile') }}">{{ __('ui.account.profile') }} →</a>
    </div>
    <div class="conference-grid">
        @forelse ($registrations as $registration)
            <article class="card conference-card">
                <div class="eyebrow">{{ $registration->conference->starts_on->format('j. n. Y') }} · {{ $registration->conference->venue }}</div>
                <h2>{{ $registration->conference->name }}</h2>
                <p>{{ __('categories.'.$registration->category_code->value) }}</p>
                <dl class="status-list">
                    <div><dt>{{ __('ui.account.registration') }}</dt><dd>@include('partials.status', ['category' => 'registration', 'value' => $registration->status])</dd></div>
                    <div><dt>{{ __('ui.account.payment') }}</dt><dd>@include('partials.status', ['category' => 'payment', 'value' => $registration->payment_status])</dd></div>
                    <div><dt>{{ __('ui.account.submission') }}</dt><dd>@include('partials.status', ['category' => 'submission', 'value' => $registration->submissions->first()?->status])</dd></div>
                </dl>
                <p class="muted">{{ __('ui.account.deadline', ['date' => $registration->conference->submission_deadline->format('j. n. Y')]) }}</p>
                <div class="actions">
                    <a class="btn primary" href="{{ route('account.registrations.show', $registration->id) }}">{{ __('ui.registration.open_registration') }} →</a>
                    <a class="btn flat" href="{{ route('account.registrations.show', $registration->id) }}#payment">{{ __('ui.account.payment') }}</a>
                </div>
            </article>
        @empty
            <div class="card empty"><h2>{{ __('ui.account.empty') }}</h2><p>{{ __('ui.flow.register_on_conference') }}</p></div>
        @endforelse
    </div>
@endsection
