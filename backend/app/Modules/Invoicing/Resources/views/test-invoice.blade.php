<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $watermark }} {{ $invoice->number }}</title>
    <style>
        @page { margin: 28mm 20mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        .muted { color: #555; }
        .watermark {
            position: fixed; top: 40%; left: -5%; width: 110%;
            text-align: center; font-size: 64px; font-weight: bold;
            color: rgba(220, 0, 0, 0.35); transform: rotate(-35deg);
        }
        .banner { border: 2px solid #d00; color: #d00; padding: 6px 10px; font-weight: bold; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 6px 4px; vertical-align: top; }
        .parties td { width: 50%; }
        .items th { text-align: left; border-bottom: 1px solid #111; }
        .items td { border-bottom: 1px solid #ccc; }
        .right { text-align: right; }
        .total td { font-weight: bold; border-top: 2px solid #111; }
    </style>
</head>
<body>
    <div class="watermark">{{ $watermark }}</div>

    <div class="banner">{{ $watermark }} — {{ __('invoices.not_valid') }}</div>

    <h1>{{ $watermark }} {{ $invoice->number }}</h1>
    <p class="muted">{{ __('invoices.issued_on') }}: {{ ($invoice->issued_at ?? now())->format('Y-m-d') }}
        · {{ __('invoices.variable_symbol') }}: {{ $registration->variable_symbol }}</p>

    <table class="parties">
        <tr>
            <td>
                <strong>{{ __('invoices.issuer') }}</strong><br>
                {{ $conference->issuer_name }}<br>
                {{ $conference->issuer_address }}<br>
                @if ($conference->issuer_company_id) {{ __('invoices.company_id') }}: {{ $conference->issuer_company_id }}<br> @endif
                @if ($conference->issuer_tax_id) {{ __('invoices.tax_id') }}: {{ $conference->issuer_tax_id }}<br> @endif
                @if ($conference->issuer_vat_id) {{ __('invoices.vat_id') }}: {{ $conference->issuer_vat_id }}<br> @endif
                IBAN: {{ $conference->iban }}
            </td>
            <td>
                <strong>{{ __('invoices.customer') }}</strong><br>
                {{ $registration->billing_name }}<br>
                {{ $registration->billing_street }}<br>
                {{ $registration->billing_postal_code }} {{ $registration->billing_city }}<br>
                {{ $registration->billing_country }}<br>
                @if ($registration->billing_company_id) {{ __('invoices.company_id') }}: {{ $registration->billing_company_id }}<br> @endif
                @if ($registration->billing_tax_id) {{ __('invoices.tax_id') }}: {{ $registration->billing_tax_id }}<br> @endif
                @if ($registration->billing_vat_id) {{ __('invoices.vat_id') }}: {{ $registration->billing_vat_id }}<br> @endif
            </td>
        </tr>
    </table>

    <table class="items" style="margin-top: 18px">
        <tr>
            <th>{{ __('invoices.item') }}</th>
            <th class="right">{{ __('invoices.amount') }}</th>
        </tr>
        <tr>
            <td>
                {{ __('invoices.fee_for', ['conference' => $conference->name]) }}<br>
                <span class="muted">{{ $registration->profile?->fullName() }} · {{ __('categories.'.$registration->category_code->value) }}</span>
            </td>
            <td class="right">{{ $invoice->amount()->format() }}</td>
        </tr>
        <tr class="total">
            <td>{{ __('invoices.total') }}</td>
            <td class="right">{{ $invoice->amount()->format() }}</td>
        </tr>
    </table>

    <p class="muted" style="margin-top: 24px">{{ __('invoices.disclaimer') }}</p>
</body>
</html>
