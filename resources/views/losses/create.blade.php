@extends('layouts.app')
@section('title', __('app.record_loss'))

@section('content')
@php
    $prodData = $products->map(fn($p)=>[
        'id'=>$p->id, 'name'=>$p->displayName(), 'base_unit'=>$p->base_unit, 'stock'=>(float)$p->stock,
        'units'=>$p->units->map(fn($u)=>['id'=>$u->id,'label'=>$u->label,'factor'=>(float)$u->factor])->values(),
    ])->values();
@endphp

<form method="POST" action="{{ route('losses.store') }}">
    @csrf
    <div class="card" style="max-width:640px">
        <div class="card-head"><h3>⚠️ {{ __('app.record_loss') }}</h3></div>
        <div class="card-body">
            <div class="field">
                <label>{{ __('app.lost_at') }} <span style="color:var(--danger)">*</span></label>
                <input type="date" name="lost_at" value="{{ old('lost_at', date('Y-m-d')) }}" required
                       class="@error('lost_at') is-invalid @enderror">
                <x-ferr name="lost_at"/>
            </div>
            <div class="field">
                <label>{{ __('app.products') }} <span style="color:var(--danger)">*</span></label>
                <select name="product_id" id="prodSel" required class="@error('product_id') is-invalid @enderror">
                    <option value="">—</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ old('product_id')==$p->id?'selected':'' }}>{{ $p->displayName() }}</option>
                    @endforeach
                </select>
                <small class="muted" id="stockHint" style="display:block;margin-top:5px"></small>
                <x-ferr name="product_id"/>
            </div>
            <div class="form-grid">
                <div class="field">
                    <label>{{ __('app.unit') }}</label>
                    <select name="unit_id" id="unitSel"></select>
                </div>
                <div class="field">
                    <label>{{ __('app.qty') }} <span style="color:var(--danger)">*</span></label>
                    <input type="number" name="qty" id="qtyInput" min="1" step="1" value="{{ old('qty', 1) }}" required
                           class="@error('qty') is-invalid @enderror" style="text-align:right">
                    <x-ferr name="qty"/>
                </div>
            </div>
            <div class="field">
                <label>{{ __('app.loss_reason') }} <span style="color:var(--danger)">*</span></label>
                <input type="text" name="reason" value="{{ old('reason') }}" required maxlength="200"
                       class="@error('reason') is-invalid @enderror"
                       placeholder="{{ __('app.loss_reason_hint') }}">
                <x-ferr name="reason"/>
            </div>
            <div class="small muted" id="baseInfo"></div>
        </div>
    </div>

    <div class="btn-row" style="margin-top:16px">
        <button type="submit" class="btn danger lg">⚠️ {{ __('app.save') }}</button>
        <a class="btn ghost lg" href="{{ route('losses.index') }}">{{ __('app.cancel') }}</a>
    </div>
</form>

@push('scripts')
<script>
const PRODS = @json($prodData);
const prodSel = document.getElementById('prodSel');
const unitSel = document.getElementById('unitSel');
const qtyInput = document.getElementById('qtyInput');
const stockHint = document.getElementById('stockHint');
const baseInfo = document.getElementById('baseInfo');

function currentProd(){ return PRODS.find(p=>p.id==prodSel.value); }

function syncUnits(){
    const p = currentProd();
    unitSel.innerHTML = '';
    stockHint.textContent = '';
    if(!p) { syncBase(); return; }
    // base unit option (unit_id ဗလာ = base)
    unitSel.add(new Option(p.base_unit + ' ({{ __('app.base_unit') }})', ''));
    p.units.forEach(u=>{
        if(u.factor != 1) unitSel.add(new Option(u.label + ' (' + u.factor + ' ' + p.base_unit + ')', u.id));
        else if(u.label !== p.base_unit) unitSel.add(new Option(u.label, u.id));
    });
    stockHint.textContent = '{{ __('app.current_stock') }}: ' + p.stock.toLocaleString() + ' ' + p.base_unit;
    syncBase();
}
function syncBase(){
    const p = currentProd();
    if(!p){ baseInfo.textContent=''; return; }
    const u = p.units.find(x=>x.id==unitSel.value);
    const factor = u ? u.factor : 1;
    const q = parseInt(qtyInput.value)||0;
    baseInfo.textContent = q>0 ? ('= ' + (q*factor).toLocaleString() + ' ' + p.base_unit) : '';
}
prodSel.addEventListener('change', syncUnits);
unitSel.addEventListener('change', syncBase);
qtyInput.addEventListener('input', syncBase);
syncUnits();
</script>
@endpush
@endsection
