@extends('layouts.public', ['title' => __('ui.password.reset_title')])

@section('content')
    <section class="card" style="max-width: 560px">
        <h1>{{ __('ui.password.reset_title') }}</h1>
        <form method="post" action="{{ route('password.update') }}" style="margin-top: 16px">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <label class="field">
                {{ __('ui.login_page.email') }}
                <input type="email" name="email" value="{{ old('email', $email) }}" autocomplete="email" required>
            </label>
            <label class="field">
                {{ __('ui.password.new') }}
                <input type="password" name="password" minlength="8" autocomplete="new-password" required>
            </label>
            <label class="field">
                {{ __('ui.password.confirm') }}
                <input type="password" name="password_confirmation" minlength="8" autocomplete="new-password" required>
            </label>
            <div class="form-actions">
                <button class="btn primary">{{ __('ui.password.reset') }}</button>
            </div>
        </form>
    </section>
@endsection
