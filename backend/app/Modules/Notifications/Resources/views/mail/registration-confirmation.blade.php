@extends('notifications::mail.layout')

@section('body')
    <p>{{ __('mail.registration_confirmation.intro', ['conference' => $conference->name]) }}</p>
    <p>{{ __('registration.category') }}: <strong>{{ __('categories.'.$registration->category_code->value) }}</strong></p>

    @if ($instructions)
        <p>{{ __('mail.registration_confirmation.pay') }}</p>
        @include('notifications::mail.payment-details', ['instructions' => $instructions])
    @else
        <p>{{ __('mail.registration_confirmation.no_payment') }}</p>
    @endif

    <p>{{ __('mail.registration_confirmation.user_page') }}</p>
@endsection
