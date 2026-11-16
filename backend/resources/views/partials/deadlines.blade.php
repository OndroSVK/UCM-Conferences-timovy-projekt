<section class="card">
    <h2>{{ __('ui.conference.deadlines') }}</h2>
    <dl class="deadline-list">
        @foreach (['registration', 'submission', 'payment'] as $kind)
            <div><dt>{{ __('ui.conference.'.$kind.'_deadline') }}</dt><dd><time datetime="{{ $conference->{$kind.'_deadline'}->toDateString() }}">{{ $conference->{$kind.'_deadline'}->format('j. n. Y') }}</time></dd></div>
        @endforeach
    </dl>
</section>
