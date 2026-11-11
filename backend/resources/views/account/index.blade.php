@extends('layouts.account', ['title' => __('ui.account.conferences')])

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">{{ __('ui.account.nav') }}</div>
            <h1>{{ __('ui.account.title') }}</h1>
            <p>{{ __('ui.account.intro') }}</p>
        </div>
    </div>

    @forelse ($registrations as $registration)
        <a class="card" href="{{ route('account.registrations.show', $registration->id) }}" style="display: block">
            <div class="eyebrow">{{ $registration->conference->starts_on->format('j. n. Y') }} · {{ $registration->conference->venue }}</div>
            <h2 style="margin: 6px 0 10px">{{ $registration->conference->name }}</h2>
            <p>{{ __('categories.'.$registration->category_code->value) }} · VS {{ $registration->variable_symbol }}</p>
            <p style="margin-top: 8px">
                @include('partials.status', ['category' => 'registration', 'value' => $registration->status])
                @include('partials.status', ['category' => 'payment', 'value' => $registration->payment_status])
            </p>
        </a>
    @empty
        <div class="card empty"><p>{{ __('ui.account.empty') }}</p></div>
    @endforelse
@endsection
