@extends('notifications::mail.layout')

@section('body')
    @if ($version > 1)
        <p>{{ __('mail.submission_received.new_version', ['title' => $submission->title, 'version' => $version]) }}</p>
    @else
        <p>{{ __('mail.submission_received.intro', ['title' => $submission->title]) }}</p>
    @endif
    <p>{{ __('mail.submission_received.next') }}</p>
@endsection
