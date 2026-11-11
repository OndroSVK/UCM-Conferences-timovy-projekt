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
        <form method="post" action="{{ $user ? route('register.existing') : route('register.store') }}" class="stack" novalidate>
            @csrf

            @if ($user)
                <div class="notice lime"><p>{{ __('ui.registration.existing_intro', ['email' => $user->email]) }}</p></div>
            @else
                <section class="card">
                    <h2>{{ __('ui.registration.account') }}</h2>
                    <div class="form-grid" style="margin-top: 14px">
                        <label class="field full">
                            {{ __('ui.registration.email') }}
                            <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                            @error('email')
                                <span class="field-error">{{ $message }}
                                    <a class="link" href="{{ route('login') }}">{{ __('ui.registration.login_instead') }}</a></span>
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

            <section class="card">
                <h2>{{ __('ui.registration.participation') }}</h2>
                <fieldset style="border: 0; padding: 0; margin: 14px 0 0">
                    <legend class="sr-only">{{ __('ui.registration.category') }}</legend>
                    @foreach ($categories as $category)
                        <label class="choice">
                            <input type="radio" name="category" value="{{ $category['code'] }}" @checked(old('category', 'speaker') === $category['code']) required>
                            <span>{{ __('categories.'.$category['code']) }}</span>
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

            <label class="checkbox">
                <input type="checkbox" name="data_processing_consent" value="1" @checked(old('data_processing_consent')) required>
                <span>{{ __('ui.registration.consent') }}</span>
            </label>

            <div class="form-actions">
                <button type="submit" class="btn primary">{{ __('ui.registration.submit') }}</button>
            </div>
        </form>
    @endif
@endsection
