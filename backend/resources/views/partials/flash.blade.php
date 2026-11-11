@if (session('status'))
    <div class="alert ok" role="status">{{ session('status') }}</div>
@endif
@if (session('warning'))
    <div class="alert warn" role="status">{{ session('warning') }}</div>
@endif
@if ($errors->any())
    <div class="alert error" role="alert">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
@auth
    @if (! auth()->user()->hasVerifiedEmail())
        <div class="alert warn">
            {{ __('ui.verification.notice') }}
            <form class="inline-form" method="post" action="{{ route('verification.send') }}">
                @csrf
                <button class="link" style="border:0;background:none;padding:0">{{ __('ui.verification.resend') }}</button>
            </form>
        </div>
    @endif
@endauth
