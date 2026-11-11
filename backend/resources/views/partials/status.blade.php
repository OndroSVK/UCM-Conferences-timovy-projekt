{{-- Status pill. $category: registration|payment|submission, $value: enum or null --}}
@php
    $raw = $value instanceof \BackedEnum ? $value->value : $value;
    $key = $raw ?? ($category === 'payment' ? 'none' : null);
    $color = match ($key) {
        'confirmed', 'paid', 'accepted' => 'green',
        'received', 'submitted' => 'blue',
        'pending', 'conditionally_accepted' => 'orange',
        'partially_paid' => 'yellow',
        'rejected' => 'red',
        default => 'gray',
    };
@endphp
<span class="pill {{ $color }}">{{ $key ? __("statuses.{$category}.{$key}") : __('ui.none') }}</span>
