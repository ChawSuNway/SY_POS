@extends('layouts.app')
@section('title', __('app.opening_stock'))

@section('content')
<div class="alert" style="background:#fffbeb;border:1px solid #fcd34d;color:#92400e;margin-bottom:14px">
    {{ __('app.opening_warning') }}
</div>

{{-- Filter bar (POST form ၏ ပြင်ပ — nested form မဖြစ်စေရန်) --}}
<div class="card" style="margin-bottom:14px">
    <div class="card-head">
        <form method="GET" class="inline-form" style="margin:0">
            <div class="pill-tabs">
                <a href="{{ route('opening-stock.index') }}" class="{{ !request('type')?'on':'' }}">{{ app()->getLocale()=='my'?'အားလုံး':'All' }}</a>
                <a href="{{ route('opening-stock.index',['type'=>'rice']) }}" class="{{ request('type')=='rice'?'on':'' }}">🌾 {{ __('app.rice') }}</a>
                <a href="{{ route('opening-stock.index',['type'=>'oil']) }}" class="{{ request('type')=='oil'?'on':'' }}">🛢️ {{ __('app.oil') }}</a>
            </div>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="🔍 {{ __('app.search') }}…" style="max-width:200px">
            <button class="btn ghost sm">{{ __('app.filter') }}</button>
        </form>
    </div>
    <div style="padding:12px 18px;color:var(--muted,#64748b);font-size:.85rem">
        {{ __('app.opening_stock_hint') }}
    </div>
</div>

<form method="POST" action="{{ route('opening-stock.store') }}">
    @csrf
    <div class="card">
        <div class="card-body tight">
            <div class="table-wrap">
                <table class="tbl">
                    <thead><tr>
                        <th>{{ __('app.products') }}</th>
                        <th class="num" style="width:120px">{{ __('app.current_stock') }}</th>
                        <th style="width:140px">{{ __('app.unit') }}</th>
                        <th class="num" style="width:120px">{{ __('app.opening_qty') }}</th>
                        <th class="num" style="width:140px">{{ __('app.cost_per_unit') }}</th>
                        <th class="num" style="width:130px">{{ __('app.base_qty') }}</th>
                    </tr></thead>
                    <tbody>
                    @forelse($products as $p)
                        <tr data-row="{{ $p->id }}">
                            <td>
                                <span class="badge {{ $p->type }}">{{ $p->type=='rice'?__('app.rice'):__('app.oil') }}</span>
                                <span class="strong">{{ $p->displayName() }}</span>
                                <div class="small muted">{{ __('app.base_unit') }}: {{ $p->base_unit }}</div>
                            </td>
                            <td class="num muted">{{ qty_fmt($p->stock) }} {{ $p->base_unit }}</td>
                            <td>
                                <select name="rows[{{ $p->id }}][unit_id]" class="os-unit" data-row="{{ $p->id }}">
                                    <option value="" data-factor="1">{{ $p->base_unit }} ({{ __('app.base_unit') }})</option>
                                    @foreach($p->units as $unit)
                                        <option value="{{ $unit->id }}" data-factor="{{ (float) $unit->factor }}">
                                            {{ $unit->label }} ({{ qty_fmt($unit->factor) }} {{ $p->base_unit }})
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="num">
                                <input type="number" step="1" min="0" name="rows[{{ $p->id }}][qty]"
                                       class="os-qty" data-row="{{ $p->id }}" placeholder="0" style="text-align:right">
                            </td>
                            <td class="num">
                                <input type="number" step="0.01" min="0" name="rows[{{ $p->id }}][unit_cost]"
                                       placeholder="0" style="text-align:right">
                            </td>
                            <td class="num strong os-base" data-row="{{ $p->id }}" style="color:var(--muted,#64748b)">—</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($products->isNotEmpty())
    <div class="btn-row" style="margin-top:16px">
        <button type="submit" class="btn primary lg">💾 {{ __('app.save_opening') }}</button>
        <a class="btn ghost lg" href="{{ route('products.index') }}">{{ __('app.cancel') }}</a>
    </div>
    @endif
</form>

@push('scripts')
<script>
function fmtQty(n){
    return (Math.round(n * 1000) / 1000).toString();
}
function recalc(rowId){
    const unit = document.querySelector('.os-unit[data-row="'+rowId+'"]');
    const qty  = document.querySelector('.os-qty[data-row="'+rowId+'"]');
    const base = document.querySelector('.os-base[data-row="'+rowId+'"]');
    const factor = parseFloat(unit.selectedOptions[0].dataset.factor) || 1;
    const q = parseFloat(qty.value) || 0;
    base.textContent = q > 0 ? fmtQty(q * factor) : '—';
}
document.querySelectorAll('.os-qty, .os-unit').forEach(function (el){
    const evt = el.classList.contains('os-unit') ? 'change' : 'input';
    el.addEventListener(evt, function (){ recalc(el.dataset.row); });
});
</script>
@endpush
@endsection
