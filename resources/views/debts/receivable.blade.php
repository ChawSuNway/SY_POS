@extends('layouts.app')
@section('title', __('app.debts').' — '.__('app.receivable'))

@section('content')
<div class="grid cols-2" style="margin-bottom:16px;align-items:start">
    <div class="stat a-red">
        <div class="accent"></div>
        <div class="label">💳 {{ __('app.total_receivable') }}</div>
        <div class="value">{{ mmk($totalDue) }}</div>
        <div class="sub">Ks</div>
    </div>
    <div class="card">
        <div class="card-head"><h3>🧑 {{ __('app.by_customer') }}</h3></div>
        <div class="card-body tight">
            <div class="table-wrap" style="max-height:180px;overflow-y:auto">
                <table class="tbl">
                    <tbody>
                    @forelse($byCustomer as $c)
                        <tr>
                            <td>
                                @if($c->customer_id)<a href="{{ route('customers.show',$c->customer_id) }}"><b>{{ $c->customer_name ?: '—' }}</b></a>
                                @else <b>{{ $c->customer_name ?: '—' }}</b>@endif
                                <span class="small muted">({{ $c->cnt }})</span>
                            </td>
                            <td class="num strong" style="color:var(--red)">{{ mmk($c->due) }}</td>
                        </tr>
                    @empty
                        <tr><td class="empty">{{ __('app.no_debts') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <div class="pill-tabs">
            <a href="{{ route('debts.receivable') }}" class="on">🧑 {{ __('app.receivable') }}</a>
            @if($u->hasAtLeast('manager'))
            <a href="{{ route('debts.payable') }}">🚚 {{ __('app.payable') }}</a>
            @endif
        </div>
        <form method="GET" class="inline-form" style="margin:0">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="🔍 {{ __('app.search') }}…" style="max-width:200px">
            <button class="btn ghost sm">{{ __('app.filter') }}</button>
        </form>
    </div>
    <div class="card-body tight">
        <div class="table-wrap">
            <table class="tbl">
                <thead><tr>
                    <th>{{ __('app.invoice_no') }}</th>
                    <th>{{ __('app.date') }}</th>
                    <th>{{ __('app.customer') }}</th>
                    <th class="num">{{ __('app.total') }}</th>
                    <th class="num">{{ __('app.paid') }}</th>
                    <th class="num">{{ __('app.credit_due') }}</th>
                    <th style="min-width:230px">{{ __('app.receive_payment') }}</th>
                </tr></thead>
                <tbody>
                @forelse($sales as $sale)
                    <tr>
                        <td><a href="{{ route('sales.show',$sale) }}"><b>{{ $sale->invoice_no }}</b></a></td>
                        <td class="small">{{ $sale->sold_at->format('d/m/Y') }}</td>
                        <td>
                            @if($sale->customer_id)<a href="{{ route('customers.show',$sale->customer_id) }}">{{ $sale->customer_name }}</a>
                            @else {{ $sale->customer_name ?: '—' }}@endif
                        </td>
                        <td class="num">{{ mmk($sale->total) }}</td>
                        <td class="num muted">{{ mmk($sale->paid_amount) }}</td>
                        <td class="num strong" style="color:var(--red)">{{ mmk($sale->credit_due) }}</td>
                        <td>
                            <form method="POST" action="{{ route('debts.receivable.pay',$sale) }}" class="inline-form" style="margin:0;gap:6px">
                                @csrf
                                <input type="number" name="amount" min="1" max="{{ (float)$sale->credit_due }}" step="1"
                                       placeholder="{{ __('app.pay_amount') }}" required style="width:120px;text-align:right">
                                <button type="submit" class="btn green sm">💵 {{ __('app.receive_payment') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty">{{ __('app.no_debts') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div>{{ $sales->links() }}</div>

@if($recentPayments->isNotEmpty())
<div class="card" style="margin-top:16px">
    <div class="card-head"><h3>🧾 {{ __('app.recent_payments') }}</h3></div>
    <div class="card-body tight">
        <div class="table-wrap">
            <table class="tbl">
                <tbody>
                @foreach($recentPayments as $pay)
                    <tr>
                        <td class="small">{{ $pay->paid_at->format('d/m/Y') }}</td>
                        <td>{{ $pay->sale->invoice_no ?? '—' }} · {{ $pay->sale->customer_name ?? '' }}</td>
                        <td class="num strong" style="color:var(--green)">+{{ mmk($pay->amount) }}</td>
                        <td class="small muted">{{ $pay->user->name ?? '' }} @if($pay->note) · {{ $pay->note }} @endif</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
