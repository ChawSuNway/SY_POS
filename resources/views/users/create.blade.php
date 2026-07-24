@extends('layouts.app')
@section('title', __('app.users') . ' — ' . __('app.create'))

@section('content')
    <div class="card" style="max-width:640px">
        <div class="card-head">
            <h3>👥 {{ __('app.users') }} — {{ __('app.create') }}</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                <div class="form-grid">
                    <div class="field">
                        <label>{{ __('app.name') }} *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required maxlength="100"
                               class="@error('name') is-invalid @enderror">
                        <x-ferr name="name"/>
                    </div>
                    <div class="field">
                        <label>{{ __('app.email') }} *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="@error('email') is-invalid @enderror">
                        <x-ferr name="email"/>
                    </div>
                    <div class="field">
                        <label>{{ __('app.role') }} *</label>
                        <select name="role" class="@error('role') is-invalid @enderror">
                            <option value="cashier" {{ old('role')=='cashier' ? 'selected' : '' }}>Cashier / ကက်ရှီယာ</option>
                            <option value="manager" {{ old('role')=='manager' ? 'selected' : '' }}>Manager / မန်နေဂျာ</option>
                            <option value="admin" {{ old('role')=='admin' ? 'selected' : '' }}>Admin / အက်ဒမင်</option>
                        </select>
                        <x-ferr name="role"/>
                    </div>
                    <div class="field">
                        <label>{{ __('app.password') }} *</label>
                        <input type="password" name="password" required minlength="6"
                               class="@error('password') is-invalid @enderror">
                        <x-ferr name="password"/>
                    </div>
                    <div class="field full">
                        <label class="check">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            {{ __('app.active') }}
                        </label>
                    </div>
                </div>
                <div class="btn-row" style="margin-top:8px">
                    <button type="submit" class="btn primary">{{ __('app.save') }}</button>
                    <a class="btn ghost" href="{{ route('users.index') }}">{{ __('app.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection
