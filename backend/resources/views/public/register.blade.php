@extends('layouts.public', ['title' => __('ui.registration.title').' · '.$conference->name])

@section('content')
    <section class="page-heading">
        <div>
            <div class="eyebrow">{{ $conference->name }}</div>
            <h1>{{ __('ui.registration.title') }}</h1>
        </div>
    </section>

    @if ($alreadyRegistered)
        <div class="card">
            <p>{{ __('ui.registration.already') }}</p>
            <p style="margin-top: 12px"><a class="btn primary" href="{{ route('account.index') }}">{{ __('ui.registration.open_registration') }}</a></p>
        </div>
    @elseif (! $conference->isRegistrationOpen())
        <div class="card"><p>{{ __('ui.conference.registration_closed') }}</p></div>
    @else
        <form method="post" action="{{ $user ? route('register.existing') : route('register.store') }}" class="stack registration-form" data-step-form data-errors="{{ json_encode($errors->keys()) }}">
            @csrf
            <ol class="registration-progress" data-progress hidden aria-label="{{ __('ui.flow.progress') }}">
                @foreach (['identity', 'details', 'summary'] as $step => $label)
                    <li data-step-label="{{ $step }}"><span>{{ $step + 1 }}</span>{{ __('ui.flow.'.$label) }}</li>
                @endforeach
            </ol>
            <div data-step="0" class="stack">

            @if ($user)
                <div class="notice lime"><p>{{ __('ui.registration.existing_intro', ['email' => $user->email]) }}</p></div>
            @else
                <section class="card">
                    <h2>{{ __('ui.registration.account') }}</h2>
                    <div class="form-grid" style="margin-top: 14px">
                        <label class="field full">
                            {{ __('ui.registration.email') }}
                            <input type="email" name="email" id="register-email" value="{{ old('email') }}" autocomplete="email" required
                                   data-check-url="{{ route('register.check-email') }}">
                            {{-- US-04: shown by frontend.js when the e-mail already has an account --}}
                            <span class="field-hint field-error" id="email-exists" hidden>
                                {{ __('ui.registration.email_exists') }}
                                <a class="link" href="{{ route('login', ['continue' => 'registration']) }}">{{ __('ui.registration.login_instead') }}</a>
                            </span>
                            @error('email')
                                <span class="field-error">{{ $message }}
                                    <a class="link" href="{{ route('login', ['continue' => 'registration']) }}">{{ __('ui.registration.login_instead') }}</a></span>
                            @enderror
                        </label>
                        <label class="field">
                            {{ __('ui.registration.password') }}
                            <input type="password" name="password" minlength="8" autocomplete="new-password" required>
                        </label>
                        <label class="field">
                            {{ __('ui.registration.password_confirmation') }}
                            <input type="password" name="password_confirmation" minlength="8" autocomplete="new-password" required>
                        </label>
                    </div>
                </section>
            @endif

            @if (! $user || ! $hasProfile)
                <section class="card">
                    <h2>{{ __('ui.registration.personal') }}</h2>
                    <div class="form-grid" style="margin-top: 14px">
                        @foreach (['title_before' => false, 'first_name' => true, 'last_name' => true, 'title_after' => false] as $name => $required)
                            <label class="field">
                                {{ __('ui.registration.'.$name) }}
                                <input name="{{ $name }}" value="{{ old($name) }}" @required($required)>
                            </label>
                        @endforeach
                        <label class="field full">
                            {{ __('ui.registration.organization') }}
                            <input name="organization" value="{{ old('organization') }}" required>
                        </label>
                    </div>
                </section>
            @endif

            </div>
            <div data-step="1" class="stack">
            <section class="card">
                <h2>{{ __('ui.registration.participation') }}</h2>
                <fieldset style="border: 0; padding: 0; margin: 14px 0 0">
                    <legend class="sr-only">{{ __('ui.registration.category') }}</legend>
                    @foreach ($categories as $category)
                        <label class="choice">
                            <input type="radio" name="category" value="{{ $category['code'] }}" data-price="{{ $category['price']->format() }}" data-label="{{ $category['name'] }}" @checked(old('category', 'speaker') === $category['code']) required>
                            <span>{{ $category['name'] }}</span>
                            <b>{{ $category['price']->format() }}</b>
                        </label>
                    @endforeach
                </fieldset>
                @if (! empty($fields['industry']))
                    <label class="field" style="margin-top: 14px">
                        {{ __('ui.registration.industry') }}
                        <select name="industry" required>
                            <option value=""></option>
                            @foreach ($fields['industry'] as $option)
                                <option value="{{ $option['value'] }}" @selected(old('industry') === $option['value'])>{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </label>
                @endif
            </section>

            <section class="card">
                <h2>{{ __('ui.registration.billing') }}</h2>
                <div class="form-grid" style="margin-top: 14px">
                    <label class="field full">
                        {{ __('ui.registration.billing_type') }}
                        <select name="billing_type" required>
                            @foreach (__('ui.registration.billing_types') as $value => $label)
                                <option value="{{ $value }}" @selected(old('billing_type') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    @foreach (['billing_name' => true, 'billing_street' => true, 'billing_city' => true, 'billing_postal_code' => true, 'billing_country' => true, 'billing_company_id' => false, 'billing_tax_id' => false, 'billing_vat_id' => false] as $name => $required)
                        <label class="field">
                            {{ __('ui.registration.'.$name) }}
                            <input name="{{ $name }}" value="{{ old($name) }}" @required($required)>
                        </label>
                    @endforeach
                </div>
            </section>

            </div>
            <div data-step="2" class="stack">
                <section class="card" data-registration-summary hidden>
                    <h2>{{ __('ui.flow.summary') }}</h2>
                    <p class="muted">{{ __('ui.flow.review') }}</p>
                    <dl class="kv summary-list">
                        @foreach (['email', 'title_before', 'first_name', 'last_name', 'title_after', 'organization', 'category', 'industry', 'billing_type', 'billing_name', 'billing_street', 'billing_city', 'billing_postal_code', 'billing_country', 'billing_company_id', 'billing_tax_id', 'billing_vat_id'] as $key)
                            <dt>{{ __('ui.registration.'.$key) }}</dt><dd data-summary="{{ $key }}" data-fallback="{{ $key === 'email' ? $user?->email : ($profile?->{$key} ?? '') }}"></dd>
                        @endforeach
                        <dt>{{ __('ui.account.price') }}</dt><dd><strong data-summary-price></strong></dd>
                    </dl>
                </section>
            <label class="checkbox">
                <input type="checkbox" name="data_processing_consent" value="1" @checked(old('data_processing_consent')) required>
                <span>{{ __('ui.registration.consent') }}
                    @if ($conference->privacy_url)
                        <a class="link" href="{{ $conference->privacy_url }}" target="_blank" rel="noopener noreferrer">{{ __('ui.registration.privacy_link') }} ↗</a>
                    @endif
                </span>
            </label>

            </div>
            <div class="form-actions step-actions">
                <button type="button" class="btn flat" data-step-back hidden>{{ __('ui.back') }}</button>
                <button type="button" class="btn primary" data-step-next hidden>{{ __('ui.flow.next') }}</button>
                <button type="submit" data-step-submit class="btn primary">{{ __('ui.registration.submit') }}</button>
            </div>
        </form>
    @endif
@endsection
