@extends('layouts.app')
@section('title', __('app.sales_report'))

@section('content')
    <div class="card" style="margin-bottom:16px">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.sales') }}" class="inline-form">
                <div class="field" style="margin-bottom:0">
                    <label>{{ __('app.from_date') }}</label>
                    <input type="date" name="from" value="{{ request('from', $from->format('Y-m-d')) }}">
                </div>
                <div class="field" style="margin-bottom:0">
                    <label>{{ __('app.to_date') }}</label>
                    <input type="date" name="to" value="{{ request('to', $to->format('Y-m-d')) }}">
                </div>
                <button type="submit" class="btn primary">{{ __('app.filter') }}</button>
            </form>
        </div>
    </div>

    <div class="grid cols-4" style="margin-bottom:16px">
        <div class="stat a-green">
            <div class="accent"></div>
            <div class="label">{{ __('app.revenue') }}</div>
            <div class="value">{{ mmk($totals['total']) }}</div>
            <div class="sub">Ks</div>
        </div>
        <div class="stat a-blue">
            <div class="accent"></div>
            <div class="label">{{ __('app.profit') }}</div>
            <div class="value">{{ mmk($totals['profit']) }}</div>
            <div class="sub">Ks</div>
        </div>
        <div class="stat a-amber">
            <div class="accent"></div>
            <div class="label">{{ __('app.cogs') }}</div>
            <div class="value">{{ mmk($totals['cost']) }}</div>
            <div class="sub">Ks</div>
        </div>
        <div class="stat a-blue">
            <div class="accent"></div>
            <div class="label">{{ __('app.invoice_no') }}</div>
            <div class="value">{{ $totals['count'] }}</div>
            <div class="sub">{{ $from->format('d/m/Y') }} — {{ $to->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <h3>🧾 {{ __('app.sales_report') }}</h3>
        </div>
        <div class="card-body tight">
            <div class="table-wrap">
                <table class="tbl">
                    <thead><tr>
                        <th>{{ __('app.invoice_no') }}</th>
                        <th>{{ __('app.date') }}</th>
                        <th>{{ __('app.cashier') }}</th>
                        <th>{{ __('app.customer') }}</th>
                        <th class="num">{{ __('app.subtotal') }}</th>
                        <th class="num">{{ __('app.discount') }}</th>
                        <th class="num">{{ __('app.total') }}</th>
                        <th class="num">{{ __('app.profit') }}</th>
                    </tr></thead>
                    <tbody>
                    @forelse($sales as $sale)
                        <tr>
                            <td class="strong"><a href="{{ route('sales.show', $sale->id) }}">{{ $sale->invoice_no }}</a></td>
                            <td class="small">{{ $sale->sold_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $sale->user->name ?? '-' }}</td>
                            <td>{{ $sale->customer_name ?: '-' }}</td>
                            <td class="num">{{ mmk($sale->subtotal) }}</td>
                            <td class="num">{{ mmk($sale->discount) }}</td>
                            <td class="num strong">{{ mmk($sale->total) }}</td>
                            <td class="num" style="color:var(--green)">{{ mmk($sale->profit) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="empty">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                    </tbody>
                    @if($sales->count())
                    <tfoot>
                        <tr>
                            <td colspan="4">{{ __('app.grand_total') }}</td>
                            <td class="num">{{ mmk($totals['subtotal']) }}</td>
                            <td class="num">{{ mmk($totals['discount']) }}</td>
                            <td class="num">{{ mmk($totals['total']) }}</td>
                            <td class="num" style="color:var(--green)">{{ mmk($totals['profit']) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
@endsection
