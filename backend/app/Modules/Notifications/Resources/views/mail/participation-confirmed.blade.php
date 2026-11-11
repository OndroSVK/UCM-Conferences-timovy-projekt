@extends('notifications::mail.layout')

@section('body')
    <p>{{ __('mail.participation_confirmed.intro', ['conference' => $conference->name]) }}</p>
    <p>{{ __('mail.participation_confirmed.details', [
        'start' => $conference->starts_on->format('Y-m-d'),
        'end' => $conference->ends_on->format('Y-m-d'),
        'venue' => $conference->venue,
    ]) }}</p>
    @if ($conference->accommodation_url)
        <p>{{ __('mail.participation_confirmed.accommodation') }} <a href="{{ $conference->accommodation_url }}">{{ $conference->accommodation_url }}</a></p>
    @endif
@endsection
