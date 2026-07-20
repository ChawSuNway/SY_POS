@php $isEdit = isset($customer); @endphp
<div class="card" style="max-width:640px">
    <div class="card-head"><h3>🧑 {{ __('app.customers') }}</h3></div>
    <div class="card-body">
        <div class="form-grid">
            <div class="field full">
                <label>{{ __('app.name') }} *</label>
                <input type="text" name="name" value="{{ old('name', $isEdit ? $customer->name : '') }}" required autofocus>
            </div>
            <div class="field">
                <label>{{ __('app.phone') }}</label>
                <input type="text" name="phone" value="{{ old('phone', $isEdit ? $customer->phone : '') }}">
            </div>
            <div class="field">
                <label>{{ __('app.status') }}</label>
                <label class="check" style="margin-top:8px"><input type="checkbox" name="is_active" value="1"
                    {{ old('is_active', $isEdit ? $customer->is_active : true)?'checked':'' }}> {{ __('app.active') }}</label>
            </div>
            <div class="field full">
                <label>{{ __('app.address') }}</label>
                <input type="text" name="address" value="{{ old('address', $isEdit ? $customer->address : '') }}">
            </div>
            <div class="field full">
                <label>{{ __('app.note') }}</label>
                <textarea name="note" rows="2">{{ old('note', $isEdit ? $customer->note : '') }}</textarea>
            </div>
        </div>
        <div class="btn-row">
            <button type="submit" class="btn primary">💾 {{ __('app.save') }}</button>
            <a class="btn ghost" href="{{ route('customers.index') }}">{{ __('app.cancel') }}</a>
        </div>
    </div>
</div>
