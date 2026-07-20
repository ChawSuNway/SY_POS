@extends('layouts.app')
@section('title', __('app.orders').' — '.$order->order_no)

@section('content')
<div class="spread" style="margin-bottom:14px">
    <a class="btn ghost sm" href="{{ route('orders.index',['status'=>$order->status]) }}">← {{ __('app.back') }}</a>
    <div class="btn-row">
        @if($order->isPending())
            <form method="POST" action="{{ route('orders.deliver',$order) }}" onsubmit="return confirm('{{ __('app.deliver_confirm') }}')">
                @csrf
                <button class="btn green">✅ {{ __('app.mark_delivered') }}</button>
            </form>
            <form method="POST" action="{{ route('orders.cancel',$order) }}" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                @csrf
                <button class="btn ghost">✖️ {{ __('app.cancel_order') }}</button>
            </form>
        @elseif($order->sale_id)
            <a class="btn primary" href="{{ route('sales.show',$order->sale_id) }}">🧾 {{ __('app.linked_sale') }}</a>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-head">
        <h3>📋 {{ $order->order_no }}</h3>
        <span class="badge {{ $order->statusBadge() }}">{{ $order->statusLabel() }}</span>
    </div>
    <div class="card-body">
        <div class="row" style="margin-bottom:14px;gap:26px">
            <div><span class="muted small">{{ __('app.order_date') }}:</span> <b>{{ $order->order_date->format('d/m/Y') }}</b></div>
            <div><span class="muted small">{{ __('app.customer') }}:</span>
                @if($order->customer_id)
                    <a href="{{ route('customers.show',$order->customer_id) }}"><b>{{ $order->customer_name ?: '-' }}</b></a>
                @else <b>{{ $order->customer_name ?: '-' }}</b> @endif
            </div>
            <div><span class="muted small">{{ __('app.taken_by') }}:</span> <b>{{ $order->user->name ?? '-' }}</b></div>
            @if($order->status=='delivered')
                <div><span class="muted small">{{ __('app.delivery_date') }}:</span> <b>{{ optional($order->delivery_date)->format('d/m/Y') }}</b></div>
                <div><span class="muted small">{{ __('app.delivered_by') }}:</span> <b>{{ $order->deliveredBy->name ?? '-' }}</b></div>
            @endif
        </div>
        @if($order->delivery_address)<p class="muted">📍 {{ __('app.delivery_address') }}: {{ $order->delivery_address }}</p>@endif
        @if($order->note)<p class="muted">📝 {{ $order->note }}</p>@endif

        <div class="table-wrap">
            <table class="tbl">
                <thead><tr>
                    <th>{{ __('app.products') }}</th>
                    <th>{{ __('app.unit') }}</th>
                    <th class="num">{{ __('app.qty') }}</th>
                    <th class="num">{{ __('app.price') }}</th>
                    <th class="num">{{ __('app.amount') }}</th>
                </tr></thead>
                <tbody>
                @foreach($order->items as $it)
                    <tr>
                        <td>
                            <span class="badge {{ $it->product->type }}">{{ $it->product->type=='rice'?__('app.rice'):__('app.oil') }}</span>
                            {{ $it->product->displayName() }}
                        </td>
                        <td>{{ $it->unit_label }}</td>
                        <td class="num">{{ qty_fmt($it->qty) }}</td>
                        <td class="num">{{ mmk($it->unit_price) }}</td>
                        <td class="num strong">{{ mmk($it->line_total) }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr><td colspan="4" class="num">{{ __('app.subtotal') }}</td><td class="num">{{ mmk($order->subtotal) }}</td></tr>
                    @if($order->discount>0)
                    <tr><td colspan="4" class="num">{{ __('app.discount') }}</td><td class="num">-{{ mmk($order->discount) }}</td></tr>
                    @endif
                    <tr><td colspan="4" class="num strong">{{ __('app.est_total') }}</td><td class="num strong">{{ mmk($order->total) }} Ks</td></tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
