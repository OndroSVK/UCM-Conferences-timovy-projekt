@extends('layouts.public', ['title' => __('ui.password.forgot_title')])

@section('content')
    <section class="card" style="max-width: 560px">
        <h1>{{ __('ui.password.forgot_title') }}</h1>
        <p>{{ __('ui.password.forgot_intro') }}</p>
        <form method="post" action="{{ route('password.email') }}" style="margin-top: 16px">
            @csrf
            <label class="field">
                {{ __('ui.login_page.email') }}
                <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
            </label>
            <div class="form-actions">
                <a class="btn flat" href="{{ route('login') }}">{{ __('ui.back') }}</a>
                <button class="btn primary">{{ __('ui.password.send') }}</button>
            </div>
        </form>
    </section>
@endsection
