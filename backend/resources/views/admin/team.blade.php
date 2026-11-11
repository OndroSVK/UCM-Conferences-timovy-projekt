@extends('layouts.admin', ['title' => __('ui.admin.team')])

@section('content')
    <div class="page-heading">
        <div>
            <div class="eyebrow">{{ $activeConference->name }}</div>
            <h1>{{ __('ui.admin.team_title') }}</h1>
            <p>{{ __('ui.admin.team_intro') }}</p>
        </div>
    </div>

    <section class="panel">
        <div class="table-wrap">
            <table>
                <thead><tr><th>{{ __('ui.admin.participant') }}</th><th>{{ __('ui.admin.role') }}</th><th>{{ __('ui.admin.delegation') }}</th><th></th></tr></thead>
                <tbody>
                @forelse ($assignments as $assignment)
                    <tr>
                        <td><strong>{{ $assignment->user->name }}</strong><span class="secondary">{{ $assignment->user->email }}</span></td>
                        <td>{{ __('ui.admin.roles.'.$assignment->role->value) }}</td>
                        <td>{{ collect($assignment->extra_permissions ?? [])->map(fn ($p) => __('ui.admin.permissions.'.$p))->join(', ') ?: __('ui.none') }}</td>
                        <td>
                            <form method="post" action="{{ route('admin.team.destroy') }}">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $assignment->user_id }}">
                                <input type="hidden" name="role" value="{{ $assignment->role->value }}">
                                <button class="btn small flat danger">{{ __('ui.admin.remove') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4">{{ __('ui.admin.empty') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <form method="post" action="{{ route('admin.team.store') }}" class="card form-grid">
        @csrf
        <label class="field">
            {{ __('ui.admin.user_email') }}
            <input type="email" name="email" value="{{ old('email') }}" required>
        </label>
        <label class="field">
            {{ __('ui.admin.role') }}
            <select name="role">
                @foreach (__('ui.admin.roles') as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <fieldset class="full" style="border: 0; padding: 0; margin: 0">
            <legend>{{ __('ui.admin.delegation') }}</legend>
            <div class="radio-row" style="margin-top: 8px">
                @foreach (__('ui.admin.permissions') as $value => $label)
                    <label><input type="checkbox" name="extra_permissions[]" value="{{ $value }}"> {{ $label }}</label>
                @endforeach
            </div>
        </fieldset>
        <div class="form-actions full"><button class="btn primary">{{ __('ui.admin.assign') }}</button></div>
    </form>
@endsection
