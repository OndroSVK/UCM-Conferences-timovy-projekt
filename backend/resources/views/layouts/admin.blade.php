@extends('layouts.workspace')

@section('sidebar')
    @isset($activeConference)
        <div class="edition-card">
            <div class="eyebrow">{{ __('ui.admin.active_conference') }}</div>
            <div class="edition-title">{{ $activeConference->name }}</div>
            @if ($adminConferences->count() > 1)
                <form method="post" action="{{ route('admin.conference.switch') }}" style="margin-top: 8px">
                    @csrf
                    <label class="sr-only" for="conference_id">{{ __('ui.admin.switch') }}</label>
                    <select id="conference_id" name="conference_id" onchange="this.form.submit()">
                        @foreach ($adminConferences as $c)
                            <option value="{{ $c->id }}" @selected($c->id === $activeConference->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    <noscript><button class="btn small">{{ __('ui.admin.switch') }}</button></noscript>
                </form>
            @endif
        </div>
        <nav aria-label="Administration">
            <div class="nav-label">{{ __('ui.admin.nav') }}</div>
            <a href="{{ route('admin.registrations.index') }}" @class(['active' => request()->routeIs('admin.registrations.*')])>{{ __('ui.admin.registrations') }}</a>
            @if (in_array('submissions.view', $permissions, true))
                <a href="{{ route('admin.submissions.index') }}" @class(['active' => request()->routeIs('admin.submissions.*')])>{{ __('ui.admin.submissions') }}</a>
            @endif
            <a href="{{ route('admin.exports.show') }}" @class(['active' => request()->routeIs('admin.exports.*')])>{{ __('ui.admin.exports') }}</a>
            @if ($isSuperadmin)
                <a href="{{ route('admin.team.index') }}" @class(['active' => request()->routeIs('admin.team.*')])>{{ __('ui.admin.team') }}</a>
            @endif
        </nav>
    @endisset
@endsection

@section('header')
    <span>{{ __('ui.admin.workspace') }}</span>
    @isset($activeConference)<span>{{ $activeConference->name }}</span>@endisset
@endsection
