@extends('layouts.public', ['title' => __('ui.home.title')])

@section('content')
    <section class="hero" aria-labelledby="home-title">
        <div class="hero-copy">
            <p class="eyebrow">{{ __('ui.home.university') }}</p>
            <h1 id="home-title">{{ __('ui.home.heading') }} <em>{{ __('ui.home.heading_highlight') }}</em></h1>
            <p>{{ __('ui.home.intro') }}</p>
            <div class="actions">
                <a class="btn primary" href="#participation">{{ __('ui.home.participation') }}</a>
            </div>
        </div>
        <div class="hero-art" aria-hidden="true">
            <svg viewBox="0 0 400 400" fill="none">
                <g transform="translate(200 180)">
                    <ellipse rx="154" ry="61" transform="rotate(-35)" />
                    <ellipse rx="154" ry="61" transform="rotate(35)" />
                    <ellipse rx="154" ry="61" transform="rotate(90)" />
                    <circle r="26" fill="#dbf587" stroke="#19211f" />
                    <circle cx="126" cy="-82" r="15" fill="#ffb8a0" stroke="#19211f" />
                    <circle cx="-126" cy="-82" r="12" fill="#dbf587" stroke="#19211f" />
                    <circle cy="154" r="12" fill="#fffef9" stroke="#19211f" />
                </g>
            </svg>
        </div>
    </section>

    <section class="card stack" id="participation" aria-labelledby="participation-title">
        <h2 id="participation-title">{{ __('ui.home.participation') }}</h2>
        <p>{{ __('ui.home.registration') }}</p>
        <p>{{ __('ui.home.account') }}</p>
        <div class="actions">
            <a class="btn lime" href="{{ route('login') }}">{{ __('ui.login') }}</a>
        </div>
    </section>
@endsection
