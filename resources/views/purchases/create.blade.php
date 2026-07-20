@extends('layouts.app')
@section('title', __('app.purchases').' — '.__('app.create'))

@section('content')
@php
    $prodData = $products->map(fn($p)=>[
        'id'=>$p->id, 'name'=>$p->displayName(), 'type'=>$p->type, 'base_unit'=>$p->base_unit,
        'units'=>$p->units->map(fn($u)=>['id'=>$u->id,'label'=>$u->label,'factor'=>(float)$u->factor])->values(),
    ])->values();
@endphp

<form method="POST" action="{{ route('purchases.store') }}">
    @csrf
    <div class="card" style="margin-bottom:16px">
        <div class="card-head"><h3>📥 {{ __('app.record_purchase') }}</h3></div>
        <div class="card-body">
            <div class="form-grid">
                <div class="field">
                    <label>{{ __('app.purchase_date') }}</label>
                    <input type="date" name="purchase_date" value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                </div>
                <div class="field">
                    <label>{{ __('app.supplier') }}</label>
                    <select name="supplier_id" id="supplierSel">
                        <option value="">— {{ __('app.walk_in') }} —</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ old('supplier_id')==$sup->id?'selected':'' }}>{{ $sup->name }}@if($sup->phone) · {{ $sup->phone }}@endif</option>
                        @endforeach
                    </select>
                    <input type="text" name="supplier_name" id="supplierName" value="{{ old('supplier_name') }}"
                           placeholder="{{ __('app.supplier') }} — {{ __('app.or_new_name') }}" style="margin-top:6px">
                </div>
                <div class="field full">
                    <label>{{ __('app.note') }}</label>
                    <input type="text" name="note" value="{{ old('note') }}">
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <h3>🧾 {{ __('app.products') }}</h3>
            <button type="button" class="btn ghost sm" id="addItem">＋ {{ __('app.add_row') }}</button>
        </div>
        <div class="card-body tight">
            <div class="table-wrap">
                <table class="tbl" id="itemsTbl">
                    <thead><tr>
                        <th style="min-width:200px">{{ __('app.products') }}</th>
                        <th style="min-width:130px">{{ __('app.unit') }}</th>
                        <th class="num" style="min-width:90px">{{ __('app.qty') }}</th>
                        <th class="num" style="min-width:120px">{{ __('app.unit_cost') }}</th>
                        <th class="num" style="min-width:120px">{{ __('app.amount') }}</th>
                        <th></th>
                    </tr></thead>
                    <tbody id="itemRows"></tbody>
                    <tfoot><tr>
                        <td colspan="4" class="num">{{ __('app.grand_total') }}</td>
                        <td class="num" id="grandTotal">0</td><td></td>
                    </tr></tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="btn-row" style="margin-top:16px">
        <button type="submit" class="btn primary lg">💾 {{ __('app.save') }}</button>
        <a class="btn ghost lg" href="{{ route('purchases.index') }}">{{ __('app.cancel') }}</a>
    </div>
</form>

@push('scripts')
<script>
const PRODS = @json($prodData);
let i = 0;
function money(n){ return Math.round(n).toLocaleString('en-US'); }

function optionsFor(p){
    // base unit + defined units
    let opts = `<option value="" data-factor="1">${p.base_unit} (${@json(__('app.base_unit'))})</option>`;
    opts += p.units.map(u=>`<option value="${u.id}" data-factor="${u.factor}">${u.label} (×${u.factor})</option>`).join('');
    return opts;
}

function addRow(){
    const idx = i++;
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td>
            <select name="items[${idx}][product_id]" class="prodSel" data-idx="${idx}" required>
                <option value="">— {{ __('app.select_product') }} —</option>
                ${PRODS.map(p=>`<option value="${p.id}">${p.name}</option>`).join('')}
            </select>
        </td>
        <td><select name="items[${idx}][product_unit_id]" class="unitSel" data-idx="${idx}"></select></td>
        <td><input type="number" name="items[${idx}][qty]" class="qty" data-idx="${idx}" min="1" step="1" value="1" style="text-align:right"></td>
        <td><input type="number" name="items[${idx}][unit_cost]" class="cost" data-idx="${idx}" min="0" step="1" value="0" style="text-align:right"></td>
        <td class="num strong lineAmt" data-idx="${idx}">0</td>
        <td class="num"><button type="button" class="btn danger sm rm">✕</button></td>`;
    document.getElementById('itemRows').appendChild(tr);
}

function recalc(){
    let grand = 0;
    document.querySelectorAll('#itemRows tr').forEach(tr=>{
        const qty = +tr.querySelector('.qty').value || 0;
        const cost = +tr.querySelector('.cost').value || 0;
        const amt = qty*cost; grand += amt;
        tr.querySelector('.lineAmt').textContent = money(amt);
    });
    document.getElementById('grandTotal').textContent = money(grand);
}

const body = document.getElementById('itemRows');
body.addEventListener('change', e=>{
    if(e.target.classList.contains('prodSel')){
        const p = PRODS.find(x=>x.id==e.target.value);
        const unitSel = e.target.closest('tr').querySelector('.unitSel');
        unitSel.innerHTML = p ? optionsFor(p) : '';
    }
    recalc();
});
body.addEventListener('input', recalc);
body.addEventListener('click', e=>{
    if(e.target.classList.contains('rm')){
        if(document.querySelectorAll('#itemRows tr').length>1) e.target.closest('tr').remove();
        recalc();
    }
});
document.getElementById('addItem').addEventListener('click', addRow);
addRow();

// supplier select — ရွေးထားလျှင် အမည်ကွက် ဖျောက်
const supSel = document.getElementById('supplierSel');
const supName = document.getElementById('supplierName');
function syncSupplier(){
    const chosen = !!supSel.value;
    supName.style.display = chosen ? 'none' : 'block';
    if(chosen) supName.value = '';
}
supSel.addEventListener('change', syncSupplier);
syncSupplier();
</script>
@endpush
@endsection
