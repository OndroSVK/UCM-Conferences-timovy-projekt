@extends('layouts.workspace')

@section('sidebar')
    <nav aria-label="Account">
        <div class="nav-label">{{ __('ui.account.nav') }}</div>
        <a href="{{ route('account.index') }}" @class(['active' => request()->routeIs('account.index', 'account.registrations.*')])>{{ __('ui.account.conferences') }}</a>
        <a href="{{ route('account.submissions.index') }}" @class(['active' => request()->routeIs('account.submissions.*')])>{{ __('ui.account.my_submissions') }}</a>
        <a href="{{ route('account.profile') }}" @class(['active' => request()->routeIs('account.profile')])>{{ __('ui.account.profile') }}</a>
    </nav>
@endsection

@section('header')
    <span>{{ __('ui.account.nav') }}</span>
@endsection
