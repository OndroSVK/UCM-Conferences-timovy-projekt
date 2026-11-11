<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head><meta charset="utf-8"><title>{{ $conference->name }}</title></head>
<body style="font-family: Arial, sans-serif; font-size: 15px; color: #111; line-height: 1.5; max-width: 640px; margin: 0 auto; padding: 24px;">
    <p style="font-size: 12px; letter-spacing: .08em; text-transform: uppercase; color: #555;">{{ $conference->name }}</p>
    <p>{{ __('mail.greeting', ['name' => $registration->profile?->fullName() ?? $registration->user->name]) }}</p>
    @yield('body')
    <p style="margin-top: 32px; color: #555; font-size: 13px;">{{ __('mail.footer') }}</p>
</body>
</html>
