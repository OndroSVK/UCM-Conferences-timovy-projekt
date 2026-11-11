@extends('layouts.account', ['title' => __('ui.account.profile')])

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">{{ __('ui.account.nav') }}</div>
            <h1>{{ __('ui.account.profile_title') }}</h1>
            <p>{{ __('ui.account.profile_intro') }}</p>
        </div>
    </div>

    <form method="post" action="{{ route('account.profile.update') }}" class="card">
        @csrf
        <div class="form-grid">
            @foreach (['title_before' => false, 'first_name' => true, 'last_name' => true, 'title_after' => false] as $name => $required)
                <label class="field">
                    {{ __('ui.registration.'.$name) }}
                    <input name="{{ $name }}" value="{{ old($name, $profile?->{$name}) }}" @required($required)>
                </label>
            @endforeach
            <label class="field full">
                {{ __('ui.registration.organization') }}
                <input name="organization" value="{{ old('organization', $profile?->organization) }}" required>
            </label>
            <label class="field full">
                {{ __('ui.registration.email') }}
                <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" autocomplete="email" required>
                <small class="muted">{{ __('ui.account.email_hint') }}</small>
            </label>
        </div>
        <div class="form-actions">
            <button class="btn primary">{{ __('ui.save') }}</button>
        </div>
    </form>
@endsection
