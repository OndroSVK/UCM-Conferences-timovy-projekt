<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('partials.head')
</head>
<body>
<a class="skip" href="#main">{{ __('ui.skip') }}</a>
<div id="app">
    <header class="public-header">
        <a class="brand" href="{{ route('home') }}" aria-label="{{ __('ui.brand') }}">
            <span class="brand-mark">u</span>
            <span>UCM <small>CONFERENCES</small></span>
        </a>
        <nav class="public-nav" aria-label="Main">
            @auth
                <a href="{{ route('home') }}">{{ auth()->user()->name }}</a>
                <form class="inline-form" method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn flat">{{ __('ui.logout') }}</button>
                </form>
            @else
                <a href="{{ route('login') }}">{{ __('ui.login') }}</a>
            @endauth
        </nav>
    </header>
    <main id="main" tabindex="-1" class="public-main">
        <div class="public-content stack">
            @include('partials.flash')
            @yield('content')
        </div>
    </main>
    <footer class="public-footer">
        <span>{{ __('ui.footer') }}</span>
        <span>{{ __('ui.prototype_notice') }}</span>
    </footer>
</div>
</body>
</html>
