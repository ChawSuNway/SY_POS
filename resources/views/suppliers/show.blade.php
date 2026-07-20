@extends('layouts.app')
@section('title', $supplier->name)

@section('content')
<div class="spread" style="margin-bottom:14px">
    <a class="btn ghost sm" href="{{ route('suppliers.index') }}">← {{ __('app.back') }}</a>
    <a class="btn primary sm" href="{{ route('suppliers.edit',$supplier) }}">✏️ {{ __('app.edit') }}</a>
</div>

<div class="grid cols-3" style="margin-bottom:16px">
    <div class="card" style="grid-column:span 1">
        <div class="card-head"><h3>🚚 {{ __('app.profile') }}</h3></div>
        <div class="card-body">
            <div class="strong" style="font-size:1.15rem">{{ $supplier->name }}
                @unless($supplier->is_active)<span class="badge gray">{{ __('app.inactive') }}</span>@endunless
            </div>
            <hr class="sep">
            <div style="line-height:1.9">
                <div><span class="muted small">📞 {{ __('app.phone') }}:</span> {{ $supplier->phone ?: '-' }}</div>
                <div><span class="muted small">📍 {{ __('app.address') }}:</span> {{ $supplier->address ?: '-' }}</div>
                @if($supplier->note)<div><span class="muted small">📝 {{ __('app.note') }}:</span> {{ $supplier->note }}</div>@endif
            </div>
        </div>
    </div>
    <div class="stat a-amber">
        <div class="accent"></div>
        <div class="label">{{ __('app.total_supplied') }}</div>
        <div class="value">{{ mmk($supplier->totalPurchased()) }}</div>
        <div class="sub">Ks</div>
    </div>
    <div class="stat a-blue">
        <div class="accent"></div>
        <div class="label">{{ __('app.transactions') }}</div>
        <div class="value">{{ $supplier->purchasesCount() }}</div>
        <div class="sub">{{ __('app.purchases') }}</div>
    </div>
</div>

<div class="card">
    <div class="card-head"><h3>📥 {{ __('app.transaction_history') }}</h3></div>
    <div class="card-body tight">
        <div class="table-wrap">
            <table class="tbl">
                <thead><tr>
                    <th>{{ __('app.purchase_no') }}</th>
                    <th>{{ __('app.purchase_date') }}</th>
                    <th>{{ __('app.cashier') }}</th>
                    <th class="num">{{ __('app.total') }}</th>
                    <th class="num">{{ __('app.actions') }}</th>
                </tr></thead>
                <tbody>
                @forelse($purchases as $p)
                    <tr>
                        <td class="strong">{{ $p->purchase_no }}</td>
                        <td class="small">{{ $p->purchase_date->format('d/m/Y') }}</td>
                        <td class="small">{{ $p->user->name ?? '-' }}</td>
                        <td class="num strong">{{ mmk($p->total_cost) }}</td>
                        <td class="num"><a class="btn ghost sm" href="{{ route('purchases.show',$p) }}">{{ __('app.view') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">{{ __('app.no_records') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div>{{ $purchases->links() }}</div>
@endsection
