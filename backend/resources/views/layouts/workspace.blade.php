<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('partials.head')
</head>
<body>
<a class="skip" href="#main">{{ __('ui.skip') }}</a>
<div id="app">
    <div class="workspace" id="workspace">
        <aside class="sidebar">
            <a class="brand" href="{{ route('home') }}" aria-label="{{ __('ui.brand') }}">
                <span class="brand-mark">u</span>
                <span>UCM <small>CONFERENCES</small></span>
            </a>
            @yield('sidebar')
            <div class="sidebar-bottom">
                <div class="user">
                    <span class="avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 2)) }}</span>
                    <div>
                        {{ auth()->user()->name }}
                        <small>{{ auth()->user()->email }}</small>
                    </div>
                </div>
                <form method="post" action="{{ route('logout') }}" style="margin-top: 12px">
                    @csrf
                    <button class="btn flat small">{{ __('ui.logout') }}</button>
                </form>
            </div>
        </aside>
        <div class="workspace-body">
            <header class="workspace-header">
                <div class="header-left">
                    <button class="icon-btn mobile-menu" type="button" aria-label="{{ __('ui.menu') }}" aria-expanded="false"
                            data-menu-toggle aria-controls="workspace">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
                    </button>
                    @yield('header')
                    @inject('access', 'App\Shared\Auth\AccessControl')
                    @if ($access->isStaff(auth()->user()) && $access->isParticipant(auth()->user()))
                        <a class="btn flat small workspace-switch" href="{{ route('choose') }}">{{ __('ui.flow.switch_workspace') }}</a>
                    @endif
                </div>
            </header>
            <main class="workspace-main" id="main" tabindex="-1">
                <div class="stack">
                    @include('partials.flash')
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
</div>
</body>
</html>
