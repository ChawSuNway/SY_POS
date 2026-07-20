@extends('layouts.app')
@section('title', __('app.orders'))

@section('content')
<div class="card">
    <div class="card-head">
        <div class="pill-tabs">
            <a href="{{ route('orders.index',['status'=>'pending']) }}" class="{{ $status=='pending'?'on':'' }}">
                ⏳ {{ __('app.pending') }} <span class="badge amber">{{ $counts['pending'] }}</span></a>
            <a href="{{ route('orders.index',['status'=>'delivered']) }}" class="{{ $status=='delivered'?'on':'' }}">
                ✅ {{ __('app.delivered') }} <span class="badge green">{{ $counts['delivered'] }}</span></a>
            <a href="{{ route('orders.index',['status'=>'cancelled']) }}" class="{{ $status=='cancelled'?'on':'' }}">
                ✖️ {{ __('app.cancelled') }} <span class="badge gray">{{ $counts['cancelled'] }}</span></a>
        </div>
        <a class="btn primary" href="{{ route('orders.create') }}">＋ {{ __('app.new_order') }}</a>
    </div>
    <div class="card-body tight">
        <div class="table-wrap">
            <table class="tbl">
                <thead><tr>
                    <th>{{ __('app.order_no') }}</th>
                    <th>{{ __('app.order_date') }}</th>
                    <th>{{ __('app.customer') }}</th>
                    <th>{{ __('app.taken_by') }}</th>
                    <th class="num">{{ __('app.est_total') }}</th>
                    <th>{{ __('app.order_status') }}</th>
                    <th class="num">{{ __('app.actions') }}</th>
                </tr></thead>
                <tbody>
                @forelse($orders as $o)
                    <tr>
                        <td class="strong">{{ $o->order_no }}</td>
                        <td class="small">{{ $o->order_date->format('d/m/Y') }}</td>
                        <td>{{ $o->customer_name ?: '-' }}</td>
                        <td class="small">{{ $o->user->name ?? '-' }}</td>
                        <td class="num strong">{{ mmk($o->total) }}</td>
                        <td><span class="badge {{ $o->statusBadge() }}">{{ $o->statusLabel() }}</span></td>
                        <td class="num">
                            <div class="btn-row" style="justify-content:flex-end">
                                <a class="btn ghost sm" href="{{ route('orders.show',$o) }}">{{ __('app.view') }}</a>
                                @if($o->isPending())
                                    <form method="POST" action="{{ route('orders.deliver',$o) }}" onsubmit="return confirm('{{ __('app.deliver_confirm') }}')">
                                        @csrf
                                        <button class="btn green sm">✅ {{ __('app.delivered') }}</button>
                                    </form>
                                @endif
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
<div>{{ $orders->links() }}</div>
@endsection
