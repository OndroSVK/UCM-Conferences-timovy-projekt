@extends('layouts.admin', ['title' => $registration->profile?->fullName()])

@use('App\Modules\Invoicing\Enums\InvoiceStatus')
@use('App\Modules\Workflow\Enums\PaymentStatus')
@use('App\Modules\Workflow\Enums\RegistrationStatus')
@php($can = fn (string $permission) => in_array($permission, $permissions, true))

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow"><a class="link" href="{{ route('admin.registrations.index') }}">{{ __('ui.admin.registrations') }}</a></div>
            <h1>{{ $registration->profile?->fullName() }}</h1>
            <p>{{ $registration->profile?->organization }} · {{ __('categories.'.$registration->category_code->value) }}</p>
        </div>
    </div>

    <section class="three-grid">
        <article class="card stat">
            <div class="stat-label">{{ __('ui.account.registration') }}</div>
            <div>@include('partials.status', ['category' => 'registration', 'value' => $registration->status])</div>
        </article>
        @if ($finance)
            <article class="card stat">
                <div class="stat-label">{{ __('ui.account.payment') }}</div>
                <div>@include('partials.status', ['category' => 'payment', 'value' => $registration->payment_status])</div>
                <div class="stat-foot">{{ $balance->paid->format() }} / {{ $balance->due->format() }}</div>
            </article>
        @endif
        <article class="card stat">
            <div class="stat-label">{{ __('ui.account.submission') }}</div>
            <div>@include('partials.status', ['category' => 'submission', 'value' => $submission?->status])</div>
            @if ($submission && $submission->type)
                <div class="stat-foot"><a class="link" href="{{ route('admin.submissions.show', $submission->id) }}">{{ $submission->title ?? __('ui.open') }}</a></div>
            @endif
        </article>
    </section>

    <div class="two-grid">
        <section class="card">
            <h2>{{ __('ui.admin.contact') }}</h2>
            <dl class="kv" style="margin-top: 12px">
                <dt>{{ __('ui.registration.email') }}</dt><dd>{{ $registration->user->email }}</dd>
                <dt>{{ __('ui.registration.industry') }}</dt><dd>{{ $registration->industry ?? __('ui.none') }}</dd>
            </dl>
        </section>

        @if ($finance)
            <section class="card">
                <h2>{{ __('ui.admin.billing') }}</h2>
                <dl class="kv" style="margin-top: 12px">
                    <dt>{{ __('ui.registration.billing_type') }}</dt><dd>{{ __('ui.registration.billing_types.'.$registration->billing_type->value) }}</dd>
                    <dt>{{ __('ui.registration.billing_name') }}</dt><dd>{{ $registration->billing_name }}</dd>
                    <dt>{{ __('ui.registration.billing_street') }}</dt><dd>{{ $registration->billing_street }}, {{ $registration->billing_postal_code }} {{ $registration->billing_city }}, {{ $registration->billing_country }}</dd>
                    @foreach (['billing_company_id', 'billing_tax_id', 'billing_vat_id'] as $key)
                        @if ($registration->{$key})<dt>{{ __('ui.registration.'.$key) }}</dt><dd>{{ $registration->{$key} }}</dd>@endif
                    @endforeach
                    <dt>{{ __('ui.account.variable_symbol') }}</dt><dd><strong>{{ $registration->variable_symbol }}</strong></dd>
                </dl>
            </section>
        @endif
    </div>

    @if ($finance)
        <section class="panel">
            <div class="panel-head"><h2>{{ __('ui.admin.payments') }}</h2></div>
            <div class="panel-body stack">
                <dl class="kv">
                    <dt>{{ __('ui.account.price') }}</dt><dd>{{ $balance->price->format() }}@if ($balance->feeWaived) — {{ __('ui.account.fee_waived') }}@endif</dd>
                    <dt>{{ __('ui.account.paid') }}</dt><dd>{{ $balance->paid->format() }}</dd>
                    <dt>{{ __('ui.account.remaining') }}</dt><dd><strong>{{ $balance->remaining->format() }}</strong></dd>
                    @if ($balance->overpaid->isPositive())
                        <dt class="danger">!</dt><dd class="danger">{{ __('payments.overpaid', ['amount' => $balance->overpaid->format()]) }}</dd>
                    @endif
                </dl>

                <div class="table-wrap">
                    <table>
                        <thead><tr><th>{{ __('ui.admin.paid_on') }}</th><th>{{ __('ui.account.amount') }}</th><th>{{ __('ui.admin.recorded_by') }}</th><th>{{ __('ui.admin.note') }}</th></tr></thead>
                        <tbody>
                        @forelse ($payments as $payment)
                            <tr><td>{{ $payment->paid_on->format('j. n. Y') }}</td><td>{{ $payment->amount()->format() }}</td><td>{{ $payment->recorder?->name }}</td><td>{{ $payment->note }}</td></tr>
                        @empty
                            <tr><td colspan="4">{{ __('ui.admin.no_payments') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($can('payments.record') && $registration->payment_status !== null && $registration->status !== RegistrationStatus::Cancelled)
                    <form method="post" action="{{ route('admin.registrations.payments', $registration->id) }}" class="form-grid">
                        @csrf
                        <label class="field">
                            {{ __('ui.admin.amount', ['currency' => $registration->currency]) }}
                            <input name="amount" inputmode="decimal" value="{{ old('amount', $balance->remaining->isPositive() ? $balance->remaining->decimal() : '') }}" required>
                        </label>
                        <label class="field">
                            {{ __('ui.admin.paid_on') }}
                            <input type="date" name="paid_on" value="{{ old('paid_on', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required>
                        </label>
                        <label class="field full">
                            {{ __('ui.admin.note') }}
                            <input name="note" value="{{ old('note') }}" maxlength="500">
                        </label>
                        <div class="form-actions full"><button class="btn primary">{{ __('ui.admin.record_payment') }}</button></div>
                    </form>
                @endif

                @if ($can('payments.waive') && ! $registration->fee_waived && $registration->payment_status === PaymentStatus::Pending && $payments->isEmpty())
                    <form method="post" action="{{ route('admin.registrations.waiver', $registration->id) }}" class="form-grid">
                        @csrf
                        <label class="field full">
                            {{ __('ui.admin.fee_waiver') }} — {{ __('ui.admin.note') }}
                            <input name="note" maxlength="500">
                        </label>
                        <div class="form-actions full"><button class="btn flat">{{ __('ui.admin.grant_waiver') }}</button></div>
                    </form>
                @endif

                <div>
                    <h3>{{ __('ui.account.invoices') }}</h3>
                    @forelse ($invoices as $invoice)
                        @if ($invoice->status === InvoiceStatus::Issued)
                            <p><a class="link" href="{{ route('admin.invoices.download', $invoice->id) }}">{{ $invoice->number }} · {{ $invoice->amount()->format() }}</a></p>
                        @else
                            <p class="danger">{{ __('ui.admin.invoice_failed', ['reason' => $invoice->failure_reason]) }}</p>
                            @if ($can('invoices.retry'))
                                <form method="post" action="{{ route('admin.invoices.retry', $invoice->id) }}">
                                    @csrf
                                    <button class="btn small">{{ __('ui.admin.retry') }}</button>
                                </form>
                            @endif
                        @endif
                    @empty
                        <p class="muted">{{ __('ui.account.no_invoices') }}</p>
                    @endforelse
                </div>
            </div>
        </section>
    @endif

    @if ($can('registrations.change_status') && $registration->status !== RegistrationStatus::Cancelled)
        <section class="panel">
            <div class="panel-head"><h2>{{ __('ui.admin.registration_status') }}</h2></div>
            <form method="post" action="{{ route('admin.registrations.status', $registration->id) }}" class="panel-body form-grid">
                @csrf
                <label class="field full">
                    {{ __('ui.admin.note') }}
                    <input name="note" maxlength="1000">
                </label>
                <div class="form-actions full">
                    @if ($registration->status === RegistrationStatus::Received)
                        <button class="btn primary" name="status" value="confirmed">{{ __('ui.admin.confirm') }}</button>
                    @endif
                    <button class="btn flat danger" name="status" value="cancelled">{{ __('ui.admin.cancel') }}</button>
                </div>
            </form>
        </section>
    @endif

    <section class="panel">
        <div class="panel-head"><h2>{{ __('ui.account.history') }}</h2></div>
        <div class="panel-body">
            <ul class="history">
                @foreach ($history as $entry)
                    <li>{{ $entry->created_at->format('j. n. Y H:i') }} · {{ __('ui.account.'.$entry->category->value) }}:
                        {{ $entry->from_status ? __("statuses.{$entry->category->value}.{$entry->from_status}") : '—' }} →
                        {{ $entry->to_status ? __("statuses.{$entry->category->value}.{$entry->to_status}") : __('statuses.payment.none') }}
                        · {{ $entry->actor?->name ?? 'system' }}@if ($entry->note) · {{ $entry->note }}@endif
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endsection
