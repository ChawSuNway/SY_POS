@extends('layouts.app')
@section('title', __('app.users') . ' — ' . __('app.edit'))

@section('content')
    <div class="card" style="max-width:640px">
        <div class="card-head">
            <h3>👥 {{ __('app.users') }} — {{ __('app.edit') }}</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('users.update',$user) }}">
                @csrf
                @method('PUT')
                <div class="form-grid">
                    <div class="field">
                        <label>{{ __('app.name') }}</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="field">
                        <label>{{ __('app.email') }}</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                    </div>
                    <div class="field">
                        <label>{{ __('app.role') }}</label>
                        <select name="role">
                            <option value="cashier" {{ old('role', $user->role)=='cashier' ? 'selected' : '' }}>Cashier / ကက်ရှီယာ</option>
                            <option value="manager" {{ old('role', $user->role)=='manager' ? 'selected' : '' }}>Manager / မန်နေဂျာ</option>
                            <option value="admin" {{ old('role', $user->role)=='admin' ? 'selected' : '' }}>Admin / အက်ဒမင်</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>{{ __('app.password') }}</label>
                        <input type="password" name="password">
                        <div class="hint">{{ app()->getLocale()=='my' ? 'အလွတ်ထားလျှင် စကားဝှက်မပြောင်း' : 'Leave blank to keep current password' }}</div>
                    </div>
                    <div class="field full">
                        <label class="check">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
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
