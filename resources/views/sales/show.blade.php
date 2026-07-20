@extends('layouts.app')
@section('title', __('app.sales').' — '.$sale->invoice_no)

@section('content')
<div class="spread" style="margin-bottom:14px">
    <a class="btn ghost sm" href="{{ route('sales.index') }}">← {{ __('app.back') }}</a>
    <a class="btn primary sm" href="{{ route('sales.receipt',$sale) }}" target="_blank">🧾 {{ __('app.print') }}</a>
</div>

<div class="card">
    <div class="card-head">
        <h3>🧾 {{ $sale->invoice_no }}</h3>
        <span class="badge gray">{{ $sale->sold_at->format('d/m/Y H:i') }}</span>
    </div>
    <div class="card-body">
        <div class="row" style="margin-bottom:14px">
            <div><span class="muted small">{{ __('app.customer') }}:</span>
                @if($sale->customer_id)
                    <a href="{{ route('customers.show',$sale->customer_id) }}"><b>{{ $sale->customer_name ?: '-' }}</b></a>
                @else
                    <b>{{ $sale->customer_name ?: '-' }}</b>
                @endif
            </div>
            <div><span class="muted small">{{ __('app.cashier') }}:</span> <b>{{ $sale->user->name ?? '-' }}</b></div>
        </div>

        <div class="table-wrap">
            <table class="tbl">
                <thead><tr>
                    <th>{{ __('app.products') }}</th>
                    <th>{{ __('app.unit') }}</th>
                    <th class="num">{{ __('app.qty') }}</th>
                    <th class="num">{{ __('app.price') }}</th>
                    <th class="num">{{ __('app.amount') }}</th>
                    @if($u->hasAtLeast('manager'))<th class="num">{{ __('app.profit') }}</th>@endif
                </tr></thead>
                <tbody>
                @foreach($sale->items as $it)
                    <tr>
                        <td>
                            <span class="badge {{ $it->product->type }}">{{ $it->product->type=='rice'?__('app.rice'):__('app.oil') }}</span>
                            {{ $it->product->displayName() }}
                        </td>
                        <td>{{ $it->unit_label }}</td>
                        <td class="num">{{ qty_fmt($it->qty) }}</td>
                        <td class="num">{{ mmk($it->unit_price) }}</td>
                        <td class="num strong">{{ mmk($it->line_total) }}</td>
                        @if($u->hasAtLeast('manager'))<td class="num" style="color:var(--green)">{{ mmk($it->lineProfit()) }}</td>@endif
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <hr class="sep">
        <div style="max-width:320px;margin-left:auto">
            <div class="cart-total-row"><span>{{ __('app.subtotal') }}</span><b>{{ mmk($sale->subtotal) }}</b></div>
            <div class="cart-total-row"><span>{{ __('app.discount') }}</span><b>-{{ mmk($sale->discount) }}</b></div>
            <div class="cart-total-row grand"><span>{{ __('app.total') }}</span><b>{{ mmk($sale->total) }} Ks</b></div>
            <div class="cart-total-row"><span>{{ __('app.paid') }}</span><b>{{ mmk($sale->paid_amount) }}</b></div>
            <div class="cart-total-row"><span>{{ __('app.change') }}</span><b style="color:var(--green)">{{ mmk($sale->change_amount) }}</b></div>
            @if($u->hasAtLeast('manager'))
            <div class="cart-total-row" style="border-top:1px dashed var(--border-strong);margin-top:6px;padding-top:8px">
                <span class="muted">{{ __('app.cogs') }}</span><b class="muted">{{ mmk($sale->total_cost) }}</b></div>
            <div class="cart-total-row"><span>{{ __('app.profit') }}</span><b style="color:var(--green)">{{ mmk($sale->profit) }}</b></div>
            @endif
        </div>
    </div>
</div>
@endsection
