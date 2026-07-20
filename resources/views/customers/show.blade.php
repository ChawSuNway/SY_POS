@extends('layouts.app')
@section('title', $customer->name)

@section('content')
<div class="spread" style="margin-bottom:14px">
    <a class="btn ghost sm" href="{{ route('customers.index') }}">← {{ __('app.back') }}</a>
    <a class="btn primary sm" href="{{ route('customers.edit',$customer) }}">✏️ {{ __('app.edit') }}</a>
</div>

<div class="grid cols-3" style="margin-bottom:16px">
    <div class="card" style="grid-column:span 1">
        <div class="card-head"><h3>🧑 {{ __('app.profile') }}</h3></div>
        <div class="card-body">
            <div class="strong" style="font-size:1.15rem">{{ $customer->name }}
                @unless($customer->is_active)<span class="badge gray">{{ __('app.inactive') }}</span>@endunless
            </div>
            <hr class="sep">
            <div style="line-height:1.9">
                <div><span class="muted small">📞 {{ __('app.phone') }}:</span> {{ $customer->phone ?: '-' }}</div>
                <div><span class="muted small">📍 {{ __('app.address') }}:</span> {{ $customer->address ?: '-' }}</div>
                @if($customer->note)<div><span class="muted small">📝 {{ __('app.note') }}:</span> {{ $customer->note }}</div>@endif
            </div>
        </div>
    </div>
    <div class="stat a-green">
        <div class="accent"></div>
        <div class="label">{{ __('app.total_spent') }}</div>
        <div class="value">{{ mmk($customer->totalSpent()) }}</div>
        <div class="sub">Ks</div>
    </div>
    <div class="stat a-blue">
        <div class="accent"></div>
        <div class="label">{{ __('app.transactions') }}</div>
        <div class="value">{{ $customer->salesCount() }}</div>
        <div class="sub">{{ __('app.sales') }}</div>
    </div>
</div>

<div class="card">
    <div class="card-head"><h3>🧾 {{ __('app.transaction_history') }}</h3></div>
    <div class="card-body tight">
        <div class="table-wrap">
            <table class="tbl">
                <thead><tr>
                    <th>{{ __('app.invoice_no') }}</th>
                    <th>{{ __('app.date') }}</th>
                    <th>{{ __('app.cashier') }}</th>
                    <th class="num">{{ __('app.total') }}</th>
                    <th class="num">{{ __('app.actions') }}</th>
                </tr></thead>
                <tbody>
                @forelse($sales as $s)
                    <tr>
                        <td class="strong">{{ $s->invoice_no }}</td>
                        <td class="small">{{ $s->sold_at->format('d/m/Y H:i') }}</td>
                        <td class="small">{{ $s->user->name ?? '-' }}</td>
                        <td class="num strong">{{ mmk($s->total) }}</td>
                        <td class="num"><a class="btn ghost sm" href="{{ route('sales.show',$s) }}">{{ __('app.view') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">{{ __('app.no_records') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div>{{ $sales->links() }}</div>
@endsection
