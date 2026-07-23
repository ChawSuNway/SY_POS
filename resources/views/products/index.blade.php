@extends('layouts.app')
@section('title', __('app.products'))

@section('content')
<div class="card">
    <div class="card-head">
        <form method="GET" class="inline-form" style="margin:0">
            <div class="pill-tabs">
                <a href="{{ route('products.index') }}" class="{{ !request('type')?'on':'' }}">{{ app()->getLocale()=='my'?'အားလုံး':'All' }}</a>
                <a href="{{ route('products.index',['type'=>'rice']) }}" class="{{ request('type')=='rice'?'on':'' }}">🌾 {{ __('app.rice') }}</a>
                <a href="{{ route('products.index',['type'=>'oil']) }}" class="{{ request('type')=='oil'?'on':'' }}">🛢️ {{ __('app.oil') }}</a>
            </div>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="🔍 {{ __('app.search') }}…" style="max-width:200px">
            <button class="btn ghost sm">{{ __('app.filter') }}</button>
        </form>
        <a class="btn primary" href="{{ route('products.create') }}">＋ {{ __('app.create') }}</a>
    </div>
    <div class="card-body tight">
        <div class="table-wrap">
            <table class="tbl">
                <thead><tr>
                    <th>{{ __('app.type') }}</th>
                    <th>{{ __('app.products') }}</th>
                    <th>{{ __('app.units') }} / {{ __('app.selling_price') }}</th>
                    <th class="num">{{ __('app.stock') }}</th>
                    <th class="num">{{ __('app.avg_cost') }}</th>
                    <th>{{ __('app.status') }}</th>
                    <th class="num">{{ __('app.actions') }}</th>
                </tr></thead>
                <tbody>
                @forelse($products as $p)
                    <tr>
                        <td><span class="badge {{ $p->type }}">{{ $p->type=='rice'?__('app.rice'):__('app.oil') }}</span></td>
                        <td>
                            <div class="strong">{{ $p->displayName() }}</div>
                            <div class="small muted">{{ __('app.base_unit') }}: {{ $p->base_unit }}</div>
                        </td>
                        <td class="small">
                            @foreach($p->units as $unit)
                                <span class="badge gray" style="margin:1px">{{ $unit->label }}: {{ mmk($unit->selling_price) }}</span>
                            @endforeach
                        </td>
                        <td class="num strong {{ $p->isLowStock()?'':'' }}" style="{{ $p->isLowStock()?'color:var(--red)':'' }}">
                            {{ $p->stockBreakdown() ?? qty_fmt($p->stock).' '.$p->base_unit }}
                            @if($p->stockBreakdown())<div class="small muted">({{ qty_fmt($p->stock) }} {{ $p->base_unit }})</div>@endif
                            @if($p->isLowStock())<div class="badge red" style="margin-top:3px">⚠️</div>@endif
                        </td>
                        <td class="num muted">{{ mmk($p->avg_cost) }}</td>
                        <td>
                            @if($p->is_active)<span class="badge green">{{ __('app.active') }}</span>
                            @else<span class="badge gray">{{ __('app.inactive') }}</span>@endif
                        </td>
                        <td class="num">
                            <div class="btn-row" style="justify-content:flex-end">
                                <a class="btn ghost sm" href="{{ route('products.edit',$p) }}">{{ __('app.edit') }}</a>
                                <form method="POST" action="{{ route('products.destroy',$p) }}" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                                    @csrf @method('DELETE')
                                    <button class="btn danger sm">✕</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty">{{ __('app.no_records') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div>{{ $products->links() }}</div>
@endsection
