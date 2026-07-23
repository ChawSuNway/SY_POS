@extends('layouts.app')
@section('title', __('app.dashboard'))

@section('content')
    <div class="grid cols-4" style="margin-bottom:16px">
        <div class="stat a-green">
            <div class="accent"></div>
            <div class="label">{{ __('app.today_sales') }}</div>
            <div class="value">{{ mmk($todaySales) }}</div>
            <div class="sub">{{ $todayCount }} {{ __('app.invoice_no') }}</div>
        </div>
        <div class="stat a-blue">
            <div class="accent"></div>
            <div class="label">{{ __('app.today_profit') }}</div>
            <div class="value">{{ mmk($todayProfit) }}</div>
            <div class="sub">Ks</div>
        </div>
        <a class="stat a-amber" href="{{ route('orders.index',['status'=>'pending']) }}" style="text-decoration:none;color:inherit">
            <div class="accent"></div>
            <div class="label">⏳ {{ __('app.pending') }} — {{ __('app.orders') }}</div>
            <div class="value">{{ $pendingOrders }}</div>
            <div class="sub">{{ $productCount }} {{ __('app.products') }}</div>
        </a>
        <div class="stat a-red">
            <div class="accent"></div>
            <div class="label">{{ __('app.low_stock_count') }}</div>
            <div class="value">{{ $lowStock->count() }}</div>
            <div class="sub">{{ __('app.low_stock') }}</div>
        </div>
    </div>

    <div class="grid cols-2">
        <div class="card">
            <div class="card-head"><h3>📈 {{ __('app.revenue') }} — 7 {{ app()->getLocale()=='my'?'ရက်':'days' }}</h3></div>
            <div class="card-body">
                @php $max = max($days->max('revenue'), 1); @endphp
                <table class="tbl">
                    <tbody>
                    @foreach($days as $d)
                        <tr>
                            <td style="width:90px">{{ \Illuminate\Support\Carbon::parse($d['date'])->format('D d/m') }}</td>
                            <td><div class="bar"><i style="width:{{ round($d['revenue']/$max*100) }}%"></i></div></td>
                            <td class="num strong">{{ mmk($d['revenue']) }}</td>
                            <td class="num small muted">+{{ mmk($d['profit']) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <h3>⚠️ {{ __('app.low_stock_report') }}</h3>
                @if($u->hasAtLeast('manager'))<a class="btn ghost sm" href="{{ route('reports.low_stock') }}">{{ __('app.view') }}</a>@endif
            </div>
            <div class="card-body tight">
                @forelse($lowStock->take(6) as $p)
                    <div class="spread" style="padding:10px 16px;border-bottom:1px solid var(--border)">
                        <div>
                            <span class="badge {{ $p->type }}">{{ $p->type=='rice'?__('app.rice'):__('app.oil') }}</span>
                            {{ $p->displayName() }}
                        </div>
                        <span class="badge red">{{ $p->stockBreakdown() ?? qty_fmt($p->stock).' '.$p->base_unit }}</span>
                    </div>
                @empty
                    <div class="empty"><div class="big">✅</div>{{ app()->getLocale()=='my'?'လက်ကျန်နည်း ပစ္စည်း မရှိပါ':'No low-stock items' }}</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="card" style="margin-top:16px">
        <div class="card-head">
            <h3>🧾 {{ __('app.sales') }}</h3>
            <a class="btn ghost sm" href="{{ route('sales.index') }}">{{ __('app.view') }}</a>
        </div>
        <div class="card-body tight">
            <div class="table-wrap">
                <table class="tbl">
                    <thead><tr>
                        <th>{{ __('app.invoice_no') }}</th><th>{{ __('app.date') }}</th>
                        <th>{{ __('app.cashier') }}</th><th class="num">{{ __('app.total') }}</th>
                        <th class="num">{{ __('app.profit') }}</th><th></th>
                    </tr></thead>
                    <tbody>
                    @forelse($recentSales as $s)
                        <tr>
                            <td class="strong">{{ $s->invoice_no }}</td>
                            <td class="small">{{ $s->sold_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $s->user->name ?? '-' }}</td>
                            <td class="num strong">{{ mmk($s->total) }}</td>
                            <td class="num" style="color:var(--green)">{{ mmk($s->profit) }}</td>
                            <td class="num"><a class="btn ghost sm" href="{{ route('sales.show',$s) }}">{{ __('app.view') }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
