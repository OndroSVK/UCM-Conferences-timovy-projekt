@extends('notifications::mail.layout')

@section('body')
    <p>{{ __('mail.decision.intro', ['title' => $decision->submission->title]) }}</p>
    <p><strong>{{ __('decisions.'.$decision->decision->value) }}</strong></p>
    <p>{{ __('mail.decision.justification') }}</p>
    <blockquote style="border-left: 3px solid #111; margin: 0; padding: 4px 12px; white-space: pre-line;">{{ $decision->justification }}</blockquote>
@endsection
