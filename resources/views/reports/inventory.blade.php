@extends('layouts.app')
@section('title', __('app.inventory_report'))

@section('content')
    <div class="spread" style="margin-bottom:16px">
        <div class="pill-tabs">
            <a href="{{ route('reports.inventory') }}" class="{{ !request('type') ? 'on' : '' }}">{{ app()->getLocale()=='my' ? 'အားလုံး' : 'All' }}</a>
            <a href="{{ route('reports.inventory', ['type' => 'rice']) }}" class="{{ request('type')=='rice' ? 'on' : '' }}">{{ __('app.rice') }}</a>
            <a href="{{ route('reports.inventory', ['type' => 'oil']) }}" class="{{ request('type')=='oil' ? 'on' : '' }}">{{ __('app.oil') }}</a>
        </div>
        <div class="stat a-green" style="min-width:220px">
            <div class="accent"></div>
            <div class="label">{{ __('app.stock_value') }}</div>
            <div class="value">{{ mmk($totalValue) }}</div>
            <div class="sub">Ks</div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <h3>📦 {{ __('app.inventory_report') }}</h3>
        </div>
        <div class="card-body tight">
            <div class="table-wrap">
                <table class="tbl">
                    <thead><tr>
                        <th>{{ __('app.type') }}</th>
                        <th>{{ app()->getLocale()=='my' ? 'ကုန်ပစ္စည်း' : 'Product' }}</th>
                        <th class="num">{{ __('app.stock') }}</th>
                        <th class="num">{{ __('app.avg_cost') }}</th>
                        <th class="num">{{ __('app.stock_value') }}</th>
                        <th></th>
                    </tr></thead>
                    <tbody>
                    @forelse($products as $p)
                        <tr>
                            <td><span class="badge {{ $p->type }}">{{ $p->type=='rice'?__('app.rice'):__('app.oil') }}</span></td>
                            <td class="strong">{{ $p->displayName() }}</td>
                            <td class="num">{{ qty_fmt($p->stock) }} {{ $p->base_unit }}</td>
                            <td class="num">{{ mmk($p->avg_cost) }}</td>
                            <td class="num strong">{{ mmk($p->stockValue()) }}</td>
                            <td>@if($p->isLowStock())<span class="badge red">{{ __('app.low_stock') }}</span>@endif</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                    </tbody>
                    @if($products->count())
                    <tfoot>
                        <tr>
                            <td colspan="4">{{ __('app.grand_total') }}</td>
                            <td class="num">{{ mmk($totalValue) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
@endsection
