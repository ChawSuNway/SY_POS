@extends('layouts.app')
@section('title', __('app.sales'))

@section('content')
<div class="card">
    <div class="card-head">
        <form method="GET" class="inline-form" style="margin:0">
            <div class="field" style="margin:0">
                <label>{{ __('app.from_date') }}</label>
                <input type="date" name="from" value="{{ request('from') }}">
            </div>
            <div class="field" style="margin:0">
                <label>{{ __('app.to_date') }}</label>
                <input type="date" name="to" value="{{ request('to') }}">
            </div>
            <button class="btn ghost">{{ __('app.filter') }}</button>
        </form>
        <a class="btn primary" href="{{ route('sales.create') }}">🛒 {{ __('app.pos') }}</a>
    </div>
    <div class="card-body tight">
        <div class="table-wrap">
            <table class="tbl">
                <thead><tr>
                    <th>{{ __('app.invoice_no') }}</th>
                    <th>{{ __('app.date') }}</th>
                    <th>{{ __('app.cashier') }}</th>
                    <th>{{ __('app.customer') }}</th>
                    <th class="num">{{ __('app.total') }}</th>
                    @if($u->hasAtLeast('manager'))<th class="num">{{ __('app.profit') }}</th>@endif
                    <th class="num">{{ __('app.actions') }}</th>
                </tr></thead>
                <tbody>
                @forelse($sales as $s)
                    <tr>
                        <td class="strong">{{ $s->invoice_no }}</td>
                        <td class="small">{{ $s->sold_at->format('d/m/Y H:i') }}</td>
                        <td class="small">{{ $s->user->name ?? '-' }}</td>
                        <td>{{ $s->customer_name ?: '-' }}</td>
                        <td class="num strong">{{ mmk($s->total) }}</td>
                        @if($u->hasAtLeast('manager'))<td class="num" style="color:var(--green)">{{ mmk($s->profit) }}</td>@endif
                        <td class="num">
                            <div class="btn-row" style="justify-content:flex-end">
                                <a class="btn ghost sm" href="{{ route('sales.show',$s) }}">{{ __('app.view') }}</a>
                                <a class="btn ghost sm" href="{{ route('sales.receipt',$s) }}" target="_blank">🧾</a>
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
<div>{{ $sales->links() }}</div>
@endsection
