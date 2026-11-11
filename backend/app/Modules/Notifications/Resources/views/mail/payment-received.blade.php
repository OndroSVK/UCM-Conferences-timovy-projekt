@extends('notifications::mail.layout')

@section('body')
    <p>{{ __('mail.payment_received.intro', ['amount' => $balance->paid->format(), 'conference' => $conference->name]) }}</p>
    @if ($balance->overpaid->isPositive())
        <p>{{ __('mail.payment_received.overpaid', ['amount' => $balance->overpaid->format()]) }}</p>
    @endif
    <p>{{ $hasInvoice ? __('mail.payment_received.invoice_attached') : __('mail.payment_received.invoice_later') }}</p>
@endsection
