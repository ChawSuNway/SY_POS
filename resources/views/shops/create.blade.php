@extends('layouts.app')
@section('title', __('app.add_shop'))

@section('content')
<form method="POST" action="{{ route('shops.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="grid cols-2" style="align-items:start">
        {{-- ဆိုင် အချက်အလက် --}}
        <div class="card">
            <div class="card-head"><h3>🏬 {{ __('app.shop_info') }}</h3></div>
            <div class="card-body">
                <div class="field">
                    <label>{{ __('app.shop_name_my') }} <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required>
                </div>
                <div class="field">
                    <label>{{ __('app.shop_name_en') }}</label>
                    <input type="text" name="name_en" value="{{ old('name_en') }}">
                </div>
                <div class="field">
                    <label>{{ __('app.shop_tagline_my') }}</label>
                    <input type="text" name="tagline" value="{{ old('tagline') }}">
                </div>
                <div class="field">
                    <label>{{ __('app.shop_phone') }}</label>
                    <input type="text" name="phone" value="{{ old('phone') }}">
                </div>
                <div class="field">
                    <label>{{ __('app.shop_address') }}</label>
                    <input type="text" name="address" value="{{ old('address') }}">
                </div>
                <div class="field">
                    <label>{{ __('app.upload_logo') }}</label>
                    <input type="file" name="logo" id="logoInput" accept="image/png,image/jpeg,image/webp,image/gif">
                    <small style="color:var(--muted,#64748b);display:block;margin-top:6px">{{ __('app.logo_hint') }}</small>
                </div>
            </div>
        </div>

        {{-- ဆိုင် Admin အကောင့် --}}
        <div class="card">
            <div class="card-head"><h3>👤 {{ __('app.shop_admin_account') }}</h3></div>
            <div class="card-body">
                <p class="muted small" style="margin-top:0">{{ __('app.shop_admin_hint') }}</p>
                <div class="field">
                    <label>{{ __('app.admin_name') }} <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="admin_name" value="{{ old('admin_name') }}" required>
                </div>
                <div class="field">
                    <label>{{ __('app.admin_email') }} <span style="color:var(--danger)">*</span></label>
                    <input type="email" name="admin_email" value="{{ old('admin_email') }}" required>
                </div>
                <div class="field">
                    <label>{{ __('app.admin_password') }} <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="admin_password" value="{{ old('admin_password') }}" required minlength="6"
                           placeholder="အနည်းဆုံး ၆ လုံး">
                </div>
            </div>
        </div>
    </div>

    <div class="btn-row" style="margin-top:16px">
        <button type="submit" class="btn primary lg">💾 {{ __('app.save') }}</button>
        <a class="btn ghost lg" href="{{ route('shops.index') }}">{{ __('app.cancel') }}</a>
    </div>
</form>
@endsection
