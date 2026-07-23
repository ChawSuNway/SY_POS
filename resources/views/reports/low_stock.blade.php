@extends('layouts.app')
@section('title', __('app.low_stock_report'))

@section('content')
    <div class="card">
        <div class="card-head">
            <h3>⚠️ {{ __('app.low_stock_report') }}</h3>
        </div>
        <div class="card-body tight">
            @if($products->count())
            <div class="table-wrap">
                <table class="tbl">
                    <thead><tr>
                        <th>{{ __('app.type') }}</th>
                        <th>{{ app()->getLocale()=='my' ? 'ကုန်ပစ္စည်း' : 'Product' }}</th>
                        <th class="num">{{ __('app.stock') }}</th>
                        <th class="num">{{ __('app.low_stock_threshold') }}</th>
                    </tr></thead>
                    <tbody>
                    @foreach($products as $p)
                        <tr>
                            <td><span class="badge {{ $p->type }}">{{ $p->type=='rice'?__('app.rice'):__('app.oil') }}</span></td>
                            <td class="strong">{{ $p->displayName() }}</td>
                            <td class="num"><span class="badge red">{{ $p->stockBreakdown() ?? qty_fmt($p->stock).' '.$p->base_unit }}</span></td>
                            <td class="num">{{ qty_fmt($p->low_stock_threshold) }} {{ $p->base_unit }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @else
                <div class="empty">
                    <div class="big">✅</div>
                    {{ app()->getLocale()=='my' ? 'လက်ကျန်နည်း ပစ္စည်း မရှိပါ' : 'No low-stock items' }}
                </div>
            @endif
        </div>
    </div>
@endsection
