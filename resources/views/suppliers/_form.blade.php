@php $isEdit = isset($supplier); @endphp
<div class="card" style="max-width:640px">
    <div class="card-head"><h3>🚚 {{ __('app.suppliers') }}</h3></div>
    <div class="card-body">
        <div class="form-grid">
            <div class="field full">
                <label>{{ __('app.name') }} *</label>
                <input type="text" name="name" value="{{ old('name', $isEdit ? $supplier->name : '') }}"
                       required maxlength="150" autofocus class="@error('name') is-invalid @enderror">
                <x-ferr name="name"/>
            </div>
            <div class="field">
                <label>{{ __('app.phone') }}</label>
                <input type="text" name="phone" value="{{ old('phone', $isEdit ? $supplier->phone : '') }}"
                       maxlength="30" class="@error('phone') is-invalid @enderror">
                <x-ferr name="phone"/>
            </div>
            <div class="field">
                <label>{{ __('app.status') }}</label>
                <label class="check" style="margin-top:8px"><input type="checkbox" name="is_active" value="1"
                    {{ old('is_active', $isEdit ? $supplier->is_active : true)?'checked':'' }}> {{ __('app.active') }}</label>
            </div>
            <div class="field full">
                <label>{{ __('app.address') }}</label>
                <input type="text" name="address" value="{{ old('address', $isEdit ? $supplier->address : '') }}"
                       maxlength="255" class="@error('address') is-invalid @enderror">
                <x-ferr name="address"/>
            </div>
            <div class="field full">
                <label>{{ __('app.note') }}</label>
                <textarea name="note" rows="2" maxlength="500" class="@error('note') is-invalid @enderror">{{ old('note', $isEdit ? $supplier->note : '') }}</textarea>
                <x-ferr name="note"/>
            </div>
        </div>
        <div class="btn-row">
            <button type="submit" class="btn primary">💾 {{ __('app.save') }}</button>
            <a class="btn ghost" href="{{ route('suppliers.index') }}">{{ __('app.cancel') }}</a>
        </div>
    </div>
</div>
