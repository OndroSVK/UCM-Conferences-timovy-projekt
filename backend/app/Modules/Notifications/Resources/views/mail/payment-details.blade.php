<table style="border-collapse: collapse; margin: 12px 0;">
    <tr><td style="padding: 4px 16px 4px 0; color: #555;">{{ __('payments.amount') }}</td><td><strong>{{ $instructions['amount']->format() }}</strong></td></tr>
    <tr><td style="padding: 4px 16px 4px 0; color: #555;">IBAN</td><td>{{ $instructions['iban'] }}</td></tr>
    @if ($instructions['bic'])
        <tr><td style="padding: 4px 16px 4px 0; color: #555;">BIC</td><td>{{ $instructions['bic'] }}</td></tr>
    @endif
    <tr><td style="padding: 4px 16px 4px 0; color: #555;">{{ __('payments.payee') }}</td><td>{{ $instructions['payee'] }}</td></tr>
    <tr><td style="padding: 4px 16px 4px 0; color: #555;">{{ __('payments.variable_symbol') }}</td><td><strong>{{ $instructions['variable_symbol'] }}</strong></td></tr>
    <tr><td style="padding: 4px 16px 4px 0; color: #555;">{{ __('payments.due_date') }}</td><td>{{ $instructions['due_date']->format('Y-m-d') }}</td></tr>
</table>
