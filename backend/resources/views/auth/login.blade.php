@extends('layouts.public', ['title' => __('ui.login')])

@section('content')
    <section class="auth">
        <div class="auth-art">
            <div>
                <div class="eyebrow">UCM CONFERENCES</div>
                <h2 style="margin-top: 30px">{{ __('ui.footer') }}</h2>
            </div>
            <span class="orbit" aria-hidden="true">✳</span>
        </div>
        <div class="auth-form">
            <h1>{{ __('ui.login_page.title') }}</h1>
            <p>{{ __('ui.login_page.intro') }}</p>
            <form method="post" action="{{ route('login.attempt') }}">
                @csrf
                <label class="field">
                    {{ __('ui.login_page.email') }}
                    <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                </label>
                <label class="field">
                    {{ __('ui.login_page.password') }}
                    <input type="password" name="password" autocomplete="current-password" required>
                </label>
                <a href="{{ route('password.request') }}" class="link">{{ __('ui.login_page.forgot') }}</a>
                <button type="submit" class="btn primary">{{ __('ui.login_page.submit') }}</button>
            </form>
            <div class="form-bottom">{{ __('ui.login_page.no_account') }}</div>
        </div>
    </section>
@endsection
