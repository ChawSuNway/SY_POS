@extends('layouts.app')
@section('title', __('app.pos'))

@section('content')
@php
    $productData = $products->map(fn($p) => [
        'id'        => $p->id,
        'name'      => $p->displayName(),
        'type'      => $p->type,
        'base_unit' => $p->base_unit,
        'stock'     => (float) $p->stock,
        'units'     => $p->units->map(fn($u) => [
            'id' => $u->id, 'label' => $u->label,
            'factor' => (float) $u->factor, 'price' => (float) $u->selling_price,
        ])->values(),
    ])->values();
@endphp

<div class="pos-layout">
    {{-- Left: product picker --}}
    <div>
        <div class="card" style="margin-bottom:14px">
            <div class="card-body" style="padding:12px 14px">
                <div class="spread">
                    <div class="pill-tabs" id="typeTabs">
                        <a href="#" class="on" data-type="all">{{ app()->getLocale()=='my'?'အားလုံး':'All' }}</a>
                        <a href="#" data-type="rice">🌾 {{ __('app.rice') }}</a>
                        <a href="#" data-type="oil">🛢️ {{ __('app.oil') }}</a>
                    </div>
                    <input type="text" id="prodSearch" placeholder="🔍 {{ __('app.search') }}…" style="max-width:220px">
                </div>
            </div>
        </div>

        <div class="pos-products" id="prodGrid">
            @foreach($products as $p)
                <button type="button" class="pos-card" data-id="{{ $p->id }}" data-type="{{ $p->type }}"
                        data-name="{{ $p->displayName() }}" @if($p->stock <= 0) disabled style="opacity:.45" @endif>
                    <div class="pc-name">{{ $p->displayName() }}</div>
                    <div class="pc-meta">
                        <span class="badge {{ $p->type }}">{{ $p->type=='rice'?__('app.rice'):__('app.oil') }}</span>
                    </div>
                    <div class="pc-stock">
                        @if($p->stock > 0)
                            <span class="muted">{{ __('app.stock') }}: {{ qty_fmt($p->stock) }} {{ $p->base_unit }}</span>
                        @else
                            <span class="badge red">{{ __('app.inactive') }} · 0</span>
                        @endif
                    </div>
                </button>
            @endforeach
        </div>
    </div>

    {{-- Right: cart --}}
    <div class="cart">
        <form method="POST" action="{{ route('sales.store') }}" id="saleForm">
            @csrf
            <div class="card">
                <div class="card-head"><h3>🛒 {{ __('app.receipt') }}</h3><span class="badge gray" id="itemCount">0</span></div>
                <div class="card-body">
                    {{-- ဝယ်သူ — optional (လက်လီ/လက်ကား quick sale — အမည် ဖြည့်စရာမလို) --}}
                    <div class="field" style="margin-bottom:10px">
                        <button type="button" class="btn ghost sm" id="custToggle"
                                style="width:100%;display:flex;justify-content:space-between;align-items:center">
                            <span id="custLabel">🧑 {{ __('app.walk_in') }}</span>
                            <span style="opacity:.6">＋ {{ __('app.customer') }}</span>
                        </button>
                        <div id="custPanel" style="display:none;margin-top:8px">
                            <select name="customer_id" id="customerSel">
                                <option value="">— {{ __('app.walk_in') }} —</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" {{ old('customer_id')==$c->id?'selected':'' }}>{{ $c->name }}@if($c->phone) · {{ $c->phone }}@endif</option>
                                @endforeach
                            </select>
                            <input type="text" name="customer_name" id="customerNameInput" value="{{ old('customer_name') }}"
                                   placeholder="{{ __('app.customer') }} — {{ __('app.or_new_name') }}" style="margin-top:6px">
                        </div>
                    </div>

                    <div class="cart-items" id="cartItems">
                        <div class="empty" id="cartEmpty"><div class="big">🧺</div>{{ __('app.cart_empty') }}</div>
                    </div>

                    <hr class="sep">

                    <div class="cart-total-row"><span>{{ __('app.subtotal') }}</span><b id="subtotalTxt">0</b></div>
                    <div class="cart-total-row">
                        <span>{{ __('app.discount') }}</span>
                        <input type="number" name="discount" id="discount" value="0" min="0" step="1" style="width:120px;text-align:right">
                    </div>
                    <div class="cart-total-row grand"><span>{{ __('app.total') }}</span><b id="totalTxt">0</b> <span class="muted">Ks</span></div>
                    <div class="cart-total-row">
                        <span>{{ __('app.paid') }}</span>
                        <input type="number" name="paid_amount" id="paid" value="0" min="0" step="1" style="width:120px;text-align:right">
                    </div>
                    <div class="cart-total-row"><span>{{ __('app.change') }}</span><b id="changeTxt" style="color:var(--green)">0</b></div>

                    <button type="submit" class="btn primary block lg" id="checkoutBtn" style="margin-top:14px" disabled>
                        💾 {{ __('app.checkout') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const PRODUCTS = @json($productData);
const T = {
    selectUnit: @json(__('app.select_unit')),
    remove: @json(__('app.remove')),
    stock: @json(__('app.stock')),
    cartEmpty: @json(__('app.cart_empty')),
    overStock: @json(app()->getLocale()=='my' ? 'လက်ကျန် မလုံလောက်ပါ' : 'Not enough stock'),
};
const byId = id => PRODUCTS.find(p => p.id == id);
let cart = []; // {pid, unitId, qty}

function money(n){ return Math.round(n).toLocaleString('en-US'); }

function render(){
    const wrap = document.getElementById('cartItems');
    wrap.querySelectorAll('.cart-line').forEach(e=>e.remove());
    const empty = document.getElementById('cartEmpty');
    empty.style.display = cart.length ? 'none' : 'block';

    let subtotal = 0;
    cart.forEach((line, i) => {
        const p = byId(line.pid);
        const unit = p.units.find(u => u.id == line.unitId) || p.units[0];
        line.unitId = unit.id;
        const lineTotal = line.qty * unit.price;
        subtotal += lineTotal;

        const usedBase = cart.reduce((s,l)=>{
            if(l.pid!=line.pid) return s;
            const u = p.units.find(x=>x.id==l.unitId)||p.units[0];
            return s + l.qty*u.factor;
        },0);
        const over = usedBase > p.stock + 0.0001;

        const row = document.createElement('div');
        row.className = 'cart-line';
        row.innerHTML = `
            <div class="cl-main">
                <div class="cl-name">${p.name}</div>
                <div class="cl-sub">
                    <select data-i="${i}" class="unitSel" style="width:auto;padding:3px 6px;font-size:.78rem">
                        ${p.units.map(u=>`<option value="${u.id}" ${u.id==unit.id?'selected':''}>${u.label} · ${money(u.price)} Ks</option>`).join('')}
                    </select>
                </div>
                ${over?`<div class="err">${T.overStock} (${T.stock}: ${(+p.stock).toLocaleString()} ${p.base_unit})</div>`:''}
                <input type="hidden" name="items[${i}][product_id]" value="${p.id}">
                <input type="hidden" name="items[${i}][product_unit_id]" value="${unit.id}">
            </div>
            <div class="qtybox">
                <button type="button" class="btn ghost sm qminus" data-i="${i}">−</button>
                <input type="number" min="1" step="1" class="qtyInput" data-i="${i}" name="items[${i}][qty]" value="${line.qty}">
                <button type="button" class="btn ghost sm qplus" data-i="${i}">＋</button>
            </div>
            <div style="text-align:right;min-width:78px">
                <div class="strong">${money(lineTotal)}</div>
                <span class="link-x" data-i="${i}">✕</span>
            </div>`;
        wrap.insertBefore(row, empty);
    });

    document.getElementById('itemCount').textContent = cart.length;
    const discount = +document.getElementById('discount').value || 0;
    const total = Math.max(0, subtotal - discount);
    document.getElementById('subtotalTxt').textContent = money(subtotal);
    document.getElementById('totalTxt').textContent = money(total);
    const paid = +document.getElementById('paid').value || 0;
    document.getElementById('changeTxt').textContent = money(Math.max(0, paid - total));

    const anyOver = cart.some(line=>{
        const p = byId(line.pid);
        const usedBase = cart.filter(l=>l.pid==line.pid).reduce((s,l)=>{
            const u=p.units.find(x=>x.id==l.unitId)||p.units[0]; return s+l.qty*u.factor;},0);
        return usedBase > p.stock + 0.0001;
    });
    document.getElementById('checkoutBtn').disabled = cart.length===0 || anyOver;
}

function addProduct(pid){
    const p = byId(pid);
    if(!p || !p.units.length) return;
    const existing = cart.find(l => l.pid==pid && l.unitId==p.units[0].id);
    if(existing){ existing.qty += 1; } else { cart.push({pid, unitId:p.units[0].id, qty:1}); }
    render();
}

// product card click
document.getElementById('prodGrid').addEventListener('click', e => {
    const card = e.target.closest('.pos-card'); if(!card || card.disabled) return;
    addProduct(card.dataset.id);
});

// cart interactions (event delegation)
document.getElementById('cartItems').addEventListener('click', e => {
    const i = e.target.dataset.i;
    if(e.target.classList.contains('link-x')){ cart.splice(i,1); render(); }
    if(e.target.classList.contains('qplus')){ cart[i].qty = +(cart[i].qty + 1); render(); }
    if(e.target.classList.contains('qminus')){ cart[i].qty = Math.max(1, cart[i].qty - 1); render(); }
});
document.getElementById('cartItems').addEventListener('change', e => {
    const i = e.target.dataset.i;
    if(e.target.classList.contains('unitSel')){ cart[i].unitId = +e.target.value; render(); }
    if(e.target.classList.contains('qtyInput')){ cart[i].qty = Math.max(1, Math.round(+e.target.value||1)); render(); }
});
document.getElementById('discount').addEventListener('input', render);
document.getElementById('paid').addEventListener('input', render);

// ဝယ်သူ — optional။ default ခေါက်သိမ်းထား (walk-in)၊ လိုမှ ဖွင့်ဖြည့်
const custSel = document.getElementById('customerSel');
const custPanel = document.getElementById('custPanel');
const custLabel = document.getElementById('custLabel');
const custNameInput = document.getElementById('customerNameInput');
const WALK_IN = '🧑 {{ __('app.walk_in') }}';

document.getElementById('custToggle').addEventListener('click', ()=>{
    custPanel.style.display = custPanel.style.display==='none' ? 'block' : 'none';
});
function syncCustomer(){
    if(custSel.value){
        custNameInput.value = '';   // ရှိပြီးသား ဖောက်သည် ရွေးထား — free-text ရှင်း
        custLabel.textContent = '🧑 ' + custSel.options[custSel.selectedIndex].text;
    } else if(custNameInput.value.trim()){
        custLabel.textContent = '🧑 ' + custNameInput.value.trim();
    } else {
        custLabel.textContent = WALK_IN;
    }
}
custSel.addEventListener('change', syncCustomer);
custNameInput.addEventListener('input', syncCustomer);
// validation error ပြန်လာလျှင် value ရှိရင် panel ဖွင့်ပြ
if(custSel.value || custNameInput.value.trim()){ custPanel.style.display = 'block'; }
syncCustomer();

// search + type filter
function applyFilter(){
    const q = (document.getElementById('prodSearch').value||'').toLowerCase();
    const type = document.querySelector('#typeTabs a.on').dataset.type;
    document.querySelectorAll('#prodGrid .pos-card').forEach(c=>{
        const okType = type==='all' || c.dataset.type===type;
        const okQ = !q || c.dataset.name.toLowerCase().includes(q);
        c.style.display = (okType && okQ) ? '' : 'none';
    });
}
document.getElementById('prodSearch').addEventListener('input', applyFilter);
document.getElementById('typeTabs').addEventListener('click', e => {
    const a = e.target.closest('a'); if(!a) return; e.preventDefault();
    document.querySelectorAll('#typeTabs a').forEach(x=>x.classList.remove('on'));
    a.classList.add('on'); applyFilter();
});

render();
</script>
@endpush
@endsection
