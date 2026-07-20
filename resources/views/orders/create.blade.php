@extends('layouts.app')
@section('title', __('app.new_order'))

@section('content')
@php
    $prodData = $products->map(fn($p)=>[
        'id'=>$p->id, 'name'=>$p->displayName(), 'base_unit'=>$p->base_unit, 'stock'=>(float)$p->stock,
        'units'=>$p->units->map(fn($u)=>['id'=>$u->id,'label'=>$u->label,'factor'=>(float)$u->factor,'price'=>(float)$u->selling_price])->values(),
    ])->values();
@endphp

<form method="POST" action="{{ route('orders.store') }}">
    @csrf
    <div class="card" style="margin-bottom:16px">
        <div class="card-head"><h3>📋 {{ __('app.new_order') }}</h3></div>
        <div class="card-body">
            <div class="form-grid">
                <div class="field">
                    <label>{{ __('app.order_date') }}</label>
                    <input type="date" name="order_date" value="{{ old('order_date', date('Y-m-d')) }}" required>
                </div>
                <div class="field">
                    <label>{{ __('app.customer') }}</label>
                    <select name="customer_id" id="customerSel">
                        <option value="">— {{ __('app.walk_in') }} —</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" data-address="{{ $c->address }}">{{ $c->name }}@if($c->phone) · {{ $c->phone }}@endif</option>
                        @endforeach
                    </select>
                    <input type="text" name="customer_name" id="customerName" value="{{ old('customer_name') }}"
                           placeholder="{{ __('app.or_new_name') }}" style="margin-top:6px">
                </div>
                <div class="field full">
                    <label>📍 {{ __('app.delivery_address') }}</label>
                    <textarea name="delivery_address" id="deliveryAddress" rows="2"
                              placeholder="{{ __('app.delivery_address_hint') }}">{{ old('delivery_address') }}</textarea>
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
                        <th style="min-width:150px">{{ __('app.unit') }}</th>
                        <th class="num" style="min-width:90px">{{ __('app.qty') }}</th>
                        <th class="num" style="min-width:110px">{{ __('app.price') }}</th>
                        <th class="num" style="min-width:120px">{{ __('app.amount') }}</th>
                        <th></th>
                    </tr></thead>
                    <tbody id="itemRows"></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="num">{{ __('app.subtotal') }}</td>
                            <td class="num" id="subtotalCell">0</td><td></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="num">{{ __('app.discount') }}</td>
                            <td class="num"><input type="number" name="discount" id="discount" value="0" min="0" step="1" style="width:110px;text-align:right"></td><td></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="num strong">{{ __('app.est_total') }}</td>
                            <td class="num strong" id="grandTotal">0</td><td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="btn-row" style="margin-top:16px">
        <button type="submit" class="btn primary lg">💾 {{ __('app.save') }}</button>
        <a class="btn ghost lg" href="{{ route('orders.index') }}">{{ __('app.cancel') }}</a>
    </div>
</form>

@push('scripts')
<script>
const PRODS = @json($prodData);
let i = 0;
function money(n){ return Math.round(n).toLocaleString('en-US'); }

function unitOptions(p){
    return p.units.map(u=>`<option value="${u.id}" data-price="${u.price}">${u.label} · ${money(u.price)} Ks</option>`).join('');
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
        <td><select name="items[${idx}][product_unit_id]" class="unitSel" data-idx="${idx}" required></select></td>
        <td><input type="number" name="items[${idx}][qty]" class="qty" min="1" step="1" value="1" style="text-align:right"></td>
        <td class="num priceCell">0</td>
        <td class="num strong lineAmt">0</td>
        <td class="num"><button type="button" class="btn danger sm rm">✕</button></td>`;
    document.getElementById('itemRows').appendChild(tr);
}
function rowPrice(tr){
    const us = tr.querySelector('.unitSel');
    const opt = us.selectedOptions[0];
    return opt ? (+opt.dataset.price||0) : 0;
}
function recalc(){
    let sub = 0;
    document.querySelectorAll('#itemRows tr').forEach(tr=>{
        const qty = +tr.querySelector('.qty').value || 0;
        const price = rowPrice(tr);
        const amt = qty*price; sub += amt;
        tr.querySelector('.priceCell').textContent = money(price);
        tr.querySelector('.lineAmt').textContent = money(amt);
    });
    document.getElementById('subtotalCell').textContent = money(sub);
    const disc = +document.getElementById('discount').value || 0;
    document.getElementById('grandTotal').textContent = money(Math.max(0, sub-disc));
}
const body = document.getElementById('itemRows');
body.addEventListener('change', e=>{
    if(e.target.classList.contains('prodSel')){
        const p = PRODS.find(x=>x.id==e.target.value);
        const us = e.target.closest('tr').querySelector('.unitSel');
        us.innerHTML = p ? unitOptions(p) : '';
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
document.getElementById('discount').addEventListener('input', recalc);
addRow();

// customer select toggle + လိပ်စာ auto-fill
const custSel = document.getElementById('customerSel');
const custName = document.getElementById('customerName');
const delivAddr = document.getElementById('deliveryAddress');
function syncCust(){
    const c = !!custSel.value;
    custName.style.display = c ? 'none' : 'block';
    if(c){
        custName.value = '';
        // ဖောက်သည် လိပ်စာ ရှိလျှင် — လိပ်စာကွက် ဗလာဖြစ်နေမှသာ ဖြည့်ပေး
        const addr = custSel.options[custSel.selectedIndex].dataset.address || '';
        if(addr && !delivAddr.value.trim()) delivAddr.value = addr;
    }
}
custSel.addEventListener('change', syncCust); syncCust();
</script>
@endpush
@endsection
