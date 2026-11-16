@extends('layouts.account', ['title' => $conference->name])

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow"><a class="link" href="{{ route('account.index') }}">{{ __('ui.account.conferences') }}</a></div>
            <h1>{{ $conference->name }}</h1>
            <p>{{ $conference->starts_on->format('j. n. Y') }} – {{ $conference->ends_on->format('j. n. Y') }} · {{ $conference->venue }}</p>
        </div>
    </div>

    {{-- Three independent statuses (R5) --}}
    <section class="three-grid">
        <article class="card stat">
            <div class="stat-label">{{ __('ui.account.registration') }}</div>
            <div style="margin-top: 8px">@include('partials.status', ['category' => 'registration', 'value' => $statuses['registration']])</div>
            <div class="stat-foot">{{ __('categories.'.$registration->category_code->value) }}</div>
        </article>
        <article class="card stat">
            <div class="stat-label">{{ __('ui.account.payment') }}</div>
            <div style="margin-top: 8px">@include('partials.status', ['category' => 'payment', 'value' => $statuses['payment']])</div>
            <div class="stat-foot">{{ __('ui.account.paid') }}: {{ $balance->paid->format() }} / {{ $balance->due->format() }}</div>
        </article>
        <article class="card stat">
            <div class="stat-label">{{ __('ui.account.submission') }}</div>
            <div style="margin-top: 8px">@include('partials.status', ['category' => 'submission', 'value' => $statuses['submission']])</div>
            <div class="stat-foot">{{ $submission?->title ?? __('ui.none') }}</div>
        </article>
    </section>

    {{-- Payment (R1): personalised instructions or no payment request --}}
    <section class="panel" id="payment">
        <div class="panel-head"><h2>{{ __('ui.account.payment') }}</h2></div>
        <div class="panel-body">
            @if ($balance->feeWaived)
                <p>{{ __('ui.account.fee_waived') }}</p>
            @elseif ($payment_instructions)
                <h3>{{ __('ui.account.instructions') }}</h3>
                <p>{{ __('ui.account.instructions_intro') }}</p>
                <dl class="kv" style="margin-top: 12px">
                    <dt>{{ __('ui.account.amount') }}</dt><dd><strong>{{ $payment_instructions['amount']->format() }}</strong></dd>
                    <dt>{{ __('ui.account.iban') }}</dt><dd>{{ $payment_instructions['iban'] }}</dd>
                    @if ($payment_instructions['bic'])<dt>{{ __('ui.account.bic') }}</dt><dd>{{ $payment_instructions['bic'] }}</dd>@endif
                    <dt>{{ __('ui.account.payee') }}</dt><dd>{{ $payment_instructions['payee'] }}</dd>
                    <dt>{{ __('ui.account.variable_symbol') }}</dt><dd><strong>{{ $payment_instructions['variable_symbol'] }}</strong>
                        <button type="button" class="btn flat small" data-copy-value="{{ $payment_instructions['variable_symbol'] }}" data-success="{{ __('ui.flow.copied') }}" data-failure="{{ __('ui.flow.copy_failed') }}" hidden>{{ __('ui.flow.copy') }}</button>
                        <span data-copy-status role="status" class="field-hint"></span></dd>
                    <dt>{{ __('ui.account.due_date') }}</dt><dd>{{ $payment_instructions['due_date']->format('j. n. Y') }}</dd>
                    <dt>{{ __('ui.account.message') }}</dt><dd>{{ $payment_instructions['message'] }}</dd>
                </dl>
            @elseif ($statuses['payment'] === null)
                <p>{{ __('ui.account.no_payment') }}</p>
            @else
                <dl class="kv">
                    <dt>{{ __('ui.account.price') }}</dt><dd>{{ $balance->due->format() }}</dd>
                    <dt>{{ __('ui.account.paid') }}</dt><dd>{{ $balance->paid->format() }}</dd>
                </dl>
            @endif

            @if (\App\Shared\Modules\Modules::enabled('Invoicing'))
            <h3 style="margin-top: 18px">{{ __('ui.account.invoices') }}</h3>
            @forelse ($invoices as $invoice)
                <p><a class="link" href="{{ route('account.invoices.download', $invoice->id) }}">{{ $invoice->number }} · {{ $invoice->amount()->format() }}</a></p>
            @empty
                <p class="muted">{{ __('ui.account.no_invoices') }}</p>
            @endforelse
            @endif
        </div>
    </section>

    {{-- Submission (R14): PDF only, new versions until the deadline (O31) --}}
    @if ($submission && $submission->type)
        <section class="panel" id="submission">
            <div class="panel-head">
                <h2>{{ __('ui.account.your_submission') }}</h2>
                <small class="muted">{{ __('ui.account.deadline', ['date' => $conference->submission_deadline->format('j. n. Y')]) }}</small>
            </div>
            <div class="panel-body stack">
                @php($maxMb = (int) floor(config('conferences.submissions.max_kilobytes') / 1024))

                @if (! $conference->isSubmissionOpen())
                    <div class="notice"><p>{{ __('ui.flow.upload_closed') }}</p></div>
                @elseif ($submission->status === \App\Modules\Workflow\Enums\SubmissionStatus::Draft)
                    <form method="post" enctype="multipart/form-data" action="{{ route('account.submissions.submit', $submission->id) }}">
                        @csrf
                        <p>{{ __('ui.account.submission_kind.'.$submission->type->value) }}</p>
                        <label class="field">
                            {{ __('ui.account.submission_title') }}
                            <input name="title" value="{{ old('title') }}" maxlength="300" required>
                        </label>
                        <label class="field">
                            {{ __('ui.account.file', ['mb' => $maxMb]) }}
                            <input type="file" name="file" accept="application/pdf,.pdf" required>
                        </label>
                        <button class="btn primary">{{ __('ui.account.submit') }}</button>
                    </form>
                @elseif ($submission->status === \App\Modules\Workflow\Enums\SubmissionStatus::Submitted)
                    <form method="post" enctype="multipart/form-data" action="{{ route('account.submissions.replace', $submission->id) }}">
                        @csrf
                        <label class="field">
                            {{ __('ui.account.replace') }} — {{ __('ui.account.file', ['mb' => $maxMb]) }}
                            <input type="file" name="file" accept="application/pdf,.pdf" required>
                        </label>
                        <small class="muted">{{ __('ui.account.replace_hint') }}</small>
                        <div><button class="btn flat">{{ __('ui.account.replace') }}</button></div>
                    </form>
                @endif

                @if ($submission->versions->isNotEmpty())
                    <div>
                        <h3>{{ __('ui.account.versions') }}</h3>
                        @foreach ($submission->versions as $version)
                            <p><a class="link" href="{{ route('account.submissions.download', $version->id) }}">{{ __('ui.account.version', ['number' => $version->version]) }}</a>
                                <span class="muted">· {{ $version->submitted_at->format('j. n. Y H:i') }}</span></p>
                        @endforeach
                    </div>
                @endif

                @foreach ($submission->decisions as $decision)
                    <div class="notice">
                        <p><strong>{{ __('ui.account.decision') }}: {{ __('decisions.'.$decision->decision->value) }}</strong></p>
                        <p style="white-space: pre-line">{{ $decision->justification }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <section class="panel">
        <div class="panel-head"><h2>{{ __('ui.account.history') }}</h2></div>
        <div class="panel-body">
            <ul class="history">
                @foreach ($history as $entry)
                    <li>{{ $entry->created_at->format('j. n. Y H:i') }} ·
                        {{ __('ui.account.'.$entry->category->value) }}:
                        {{ $entry->from_status ? __("statuses.{$entry->category->value}.{$entry->from_status}") : '—' }} →
                        {{ $entry->to_status ? __("statuses.{$entry->category->value}.{$entry->to_status}") : __('statuses.payment.none') }}
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endsection
