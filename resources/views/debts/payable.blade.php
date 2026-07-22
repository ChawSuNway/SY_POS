@extends('layouts.app')
@section('title', __('app.debts').' — '.__('app.payable'))

@section('content')
<div class="grid cols-2" style="margin-bottom:16px;align-items:start">
    <div class="stat a-amber">
        <div class="accent"></div>
        <div class="label">💳 {{ __('app.total_payable') }}</div>
        <div class="value">{{ mmk($totalDue) }}</div>
        <div class="sub">Ks</div>
    </div>
    <div class="card">
        <div class="card-head"><h3>🚚 {{ __('app.by_supplier') }}</h3></div>
        <div class="card-body tight">
            <div class="table-wrap" style="max-height:180px;overflow-y:auto">
                <table class="tbl">
                    <tbody>
                    @forelse($bySupplier as $s)
                        <tr>
                            <td>
                                @if($s->supplier_id)<a href="{{ route('suppliers.show',$s->supplier_id) }}"><b>{{ $s->supplier_name ?: '—' }}</b></a>
                                @else <b>{{ $s->supplier_name ?: '—' }}</b>@endif
                                <span class="small muted">({{ $s->cnt }})</span>
                            </td>
                            <td class="num strong" style="color:var(--amber)">{{ mmk($s->due) }}</td>
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
            <a href="{{ route('debts.receivable') }}">🧑 {{ __('app.receivable') }}</a>
            <a href="{{ route('debts.payable') }}" class="on">🚚 {{ __('app.payable') }}</a>
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
                    <th>{{ __('app.purchase_no') }}</th>
                    <th>{{ __('app.date') }}</th>
                    <th>{{ __('app.supplier') }}</th>
                    <th class="num">{{ __('app.total') }}</th>
                    <th class="num">{{ __('app.paid') }}</th>
                    <th class="num">{{ __('app.credit_due') }}</th>
                    <th style="min-width:230px">{{ __('app.make_payment') }}</th>
                </tr></thead>
                <tbody>
                @forelse($purchases as $purchase)
                    <tr>
                        <td><a href="{{ route('purchases.show',$purchase) }}"><b>{{ $purchase->purchase_no }}</b></a></td>
                        <td class="small">{{ $purchase->purchase_date->format('d/m/Y') }}</td>
                        <td>
                            @if($purchase->supplier_id)<a href="{{ route('suppliers.show',$purchase->supplier_id) }}">{{ $purchase->supplier_name }}</a>
                            @else {{ $purchase->supplier_name ?: '—' }}@endif
                        </td>
                        <td class="num">{{ mmk($purchase->total_cost) }}</td>
                        <td class="num muted">{{ mmk($purchase->paid_amount) }}</td>
                        <td class="num strong" style="color:var(--amber)">{{ mmk($purchase->credit_due) }}</td>
                        <td>
                            <form method="POST" action="{{ route('debts.payable.pay',$purchase) }}" class="inline-form" style="margin:0;gap:6px">
                                @csrf
                                <input type="number" name="amount" min="1" max="{{ (float)$purchase->credit_due }}" step="1"
                                       placeholder="{{ __('app.pay_amount') }}" required style="width:120px;text-align:right">
                                <button type="submit" class="btn primary sm">💸 {{ __('app.make_payment') }}</button>
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
<div>{{ $purchases->links() }}</div>

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
                        <td>{{ $pay->purchase->purchase_no ?? '—' }} · {{ $pay->purchase->supplier_name ?? '' }}</td>
                        <td class="num strong" style="color:var(--amber)">-{{ mmk($pay->amount) }}</td>
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
