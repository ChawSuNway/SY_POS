@extends('layouts.app')
@section('title', __('app.purchases'))

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
        <a class="btn primary" href="{{ route('purchases.create') }}">＋ {{ __('app.record_purchase') }}</a>
    </div>
    <div class="card-body tight">
        <div class="table-wrap">
            <table class="tbl">
                <thead><tr>
                    <th>{{ __('app.purchase_no') }}</th>
                    <th>{{ __('app.purchase_date') }}</th>
                    <th>{{ __('app.supplier') }}</th>
                    <th>{{ __('app.cashier') }}</th>
                    <th class="num">{{ __('app.total') }}</th>
                    <th class="num">{{ __('app.actions') }}</th>
                </tr></thead>
                <tbody>
                @forelse($purchases as $pur)
                    <tr>
                        <td class="strong">{{ $pur->purchase_no }}</td>
                        <td>{{ $pur->purchase_date->format('d/m/Y') }}</td>
                        <td>{{ $pur->supplier_name ?: '-' }}</td>
                        <td class="small">{{ $pur->user->name ?? '-' }}</td>
                        <td class="num strong">{{ mmk($pur->total_cost) }}</td>
                        <td class="num"><a class="btn ghost sm" href="{{ route('purchases.show',$pur) }}">{{ __('app.view') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">{{ __('app.no_records') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div>{{ $purchases->links() }}</div>
@endsection
