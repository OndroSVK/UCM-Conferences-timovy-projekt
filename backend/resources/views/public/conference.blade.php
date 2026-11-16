@extends('layouts.public', ['title' => $conference->name])

@section('content')
    <section class="page-heading conference-hero">
        <div>
            <div class="eyebrow">{{ strtoupper($conference->subdomain) }}</div>
            <h1>{{ $conference->name }}</h1>
            <p>{{ __('ui.conference.dates', ['start' => $conference->starts_on->format('j. n. Y'), 'end' => $conference->ends_on->format('j. n. Y')]) }} · {{ $conference->venue }}</p>
        </div>
        <div class="actions">
            @if ($conference->isRegistrationOpen())
                <a class="btn lime" href="{{ route('register') }}">{{ __('ui.conference.register') }}</a>
            @else
                <span class="pill gray">{{ __('ui.conference.registration_closed') }}</span>
            @endif
        </div>
    </section>

    <div class="two-grid">
        <section class="card">
            <h2>{{ __('ui.conference.categories') }}</h2>
            <dl class="kv" style="margin-top: 12px">
                @foreach ($categories as $category)
                    <dt>{{ $category['name'] }}</dt>
                    <dd><strong>{{ $category['price']->format() }}</strong></dd>
                @endforeach
            </dl>
        </section>
        <section class="card">
            <h2>{{ __('ui.conference.deadlines') }}</h2>
            <dl class="kv" style="margin-top: 12px">
                <dt>{{ __('ui.conference.registration_deadline') }}</dt><dd>{{ $conference->registration_deadline->format('j. n. Y') }}</dd>
                <dt>{{ __('ui.conference.submission_deadline') }}</dt><dd>{{ $conference->submission_deadline->format('j. n. Y') }}</dd>
                <dt>{{ __('ui.conference.payment_deadline') }}</dt><dd>{{ $conference->payment_deadline->format('j. n. Y') }}</dd>
            </dl>
            @if ($conference->accommodation_url)
                <p style="margin-top: 12px"><a class="link" href="{{ $conference->accommodation_url }}" rel="noopener noreferrer" target="_blank">{{ __('ui.conference.accommodation') }} ↗</a></p>
            @endif
        </section>
    </div>
@endsection
