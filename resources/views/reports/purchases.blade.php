@extends('layouts.app')
@section('title', __('app.purchase_report'))

@section('content')
    <div class="card" style="margin-bottom:16px">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.purchases') }}" class="inline-form">
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

    <div class="grid cols-2" style="margin-bottom:16px">
        <div class="stat a-amber">
            <div class="accent"></div>
            <div class="label">{{ __('app.purchase_report') }}</div>
            <div class="value">{{ mmk($totals['total']) }}</div>
            <div class="sub">Ks</div>
        </div>
        <div class="stat a-blue">
            <div class="accent"></div>
            <div class="label">{{ __('app.purchase_no') }}</div>
            <div class="value">{{ $totals['count'] }}</div>
            <div class="sub">{{ $from->format('d/m/Y') }} — {{ $to->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <h3>📥 {{ __('app.purchase_report') }}</h3>
        </div>
        <div class="card-body tight">
            <div class="table-wrap">
                <table class="tbl">
                    <thead><tr>
                        <th>{{ __('app.purchase_no') }}</th>
                        <th>{{ __('app.date') }}</th>
                        <th>{{ __('app.supplier') }}</th>
                        <th class="num">{{ __('app.total') }}</th>
                    </tr></thead>
                    <tbody>
                    @forelse($purchases as $purchase)
                        <tr>
                            <td class="strong">{{ $purchase->purchase_no }}</td>
                            <td class="small">{{ $purchase->purchase_date->format('d/m/Y') }}</td>
                            <td>{{ $purchase->supplier_name ?: '-' }}</td>
                            <td class="num strong">{{ mmk($purchase->total_cost) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                    </tbody>
                    @if($purchases->count())
                    <tfoot>
                        <tr>
                            <td colspan="3">{{ __('app.grand_total') }}</td>
                            <td class="num">{{ mmk($totals['total']) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
@endsection
