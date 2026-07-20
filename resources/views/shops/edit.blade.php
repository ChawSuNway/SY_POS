@extends('layouts.app')
@section('title', __('app.edit_shop').' — '.$shop->name)

@section('content')
<form method="POST" action="{{ route('shops.update',$shop) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="grid cols-2" style="align-items:start">
        {{-- ဆိုင် အချက်အလက် --}}
        <div class="card">
            <div class="card-head"><h3>🏬 {{ __('app.shop_info') }}</h3></div>
            <div class="card-body">
                <div class="field">
                    <label>{{ __('app.shop_name_my') }} <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $shop->name) }}" required>
                </div>
                <div class="field">
                    <label>{{ __('app.shop_name_en') }}</label>
                    <input type="text" name="name_en" value="{{ old('name_en', $shop->name_en) }}">
                </div>
                <div class="field">
                    <label>{{ __('app.shop_tagline_my') }}</label>
                    <input type="text" name="tagline" value="{{ old('tagline', $shop->tagline) }}">
                </div>
                <div class="field">
                    <label>{{ __('app.shop_tagline_en') }}</label>
                    <input type="text" name="tagline_en" value="{{ old('tagline_en', $shop->tagline_en) }}">
                </div>
                <div class="field">
                    <label>{{ __('app.shop_phone') }}</label>
                    <input type="text" name="phone" value="{{ old('phone', $shop->phone) }}">
                </div>
                <div class="field">
                    <label>{{ __('app.shop_address') }}</label>
                    <input type="text" name="address" value="{{ old('address', $shop->address) }}">
                </div>
                <label class="check">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $shop->is_active) ? 'checked' : '' }}>
                    {{ __('app.active') }}
                </label>
            </div>
        </div>

        {{-- Logo --}}
        <div class="card">
            <div class="card-head"><h3>🖼️ {{ __('app.logo') }}</h3></div>
            <div class="card-body">
                <div style="display:flex;flex-direction:column;align-items:center;gap:14px">
                    <div style="width:120px;height:120px;border-radius:16px;border:1px solid var(--border);background:var(--surface-2,#f1f5f9);display:flex;align-items:center;justify-content:center;overflow:hidden">
                        @if($shop->logoUrl())
                            <img id="logoPreview" src="{{ $shop->logoUrl() }}" alt="logo" style="max-width:100%;max-height:100%;object-fit:contain">
                        @else
                            <img id="logoPreview" src="" alt="" style="max-width:100%;max-height:100%;object-fit:contain;display:none">
                            <span id="logoEmoji" style="font-size:3rem">🏪</span>
                        @endif
                    </div>
                    <div class="field" style="width:100%;margin-bottom:0">
                        <label>{{ __('app.upload_logo') }}</label>
                        <input type="file" name="logo" id="logoInput" accept="image/png,image/jpeg,image/webp,image/gif">
                        <small style="color:var(--muted,#64748b);display:block;margin-top:6px">{{ __('app.logo_hint') }}</small>
                    </div>
                    @if($shop->logo)
                        <label class="check" style="align-self:flex-start">
                            <input type="checkbox" name="remove_logo" value="1">
                            {{ __('app.remove_logo') }}
                        </label>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="btn-row" style="margin-top:16px">
        <button type="submit" class="btn primary lg">💾 {{ __('app.save') }}</button>
        <a class="btn ghost lg" href="{{ route('shops.index') }}">{{ __('app.cancel') }}</a>
    </div>
</form>

@push('scripts')
<script>
document.getElementById('logoInput').addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (!file) return;
    const img = document.getElementById('logoPreview');
    const emoji = document.getElementById('logoEmoji');
    img.src = URL.createObjectURL(file);
    img.style.display = 'block';
    if (emoji) emoji.style.display = 'none';
});
</script>
@endpush
@endsection
