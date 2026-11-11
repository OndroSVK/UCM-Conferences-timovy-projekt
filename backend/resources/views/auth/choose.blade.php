@extends('layouts.public', ['title' => __('ui.choose.title')])

@section('content')
    <h1>{{ __('ui.choose.title') }}</h1>
    <div class="two-grid">
        <a class="card lime" href="{{ route('account.index') }}">
            <h2>{{ __('ui.choose.participant') }}</h2>
            <p>{{ __('ui.choose.participant_hint') }}</p>
        </a>
        <a class="card lavender" href="{{ route('admin.registrations.index') }}">
            <h2>{{ __('ui.choose.admin') }}</h2>
            <p>{{ __('ui.choose.admin_hint') }}</p>
        </a>
    </div>
@endsection
