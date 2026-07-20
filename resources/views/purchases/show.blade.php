@extends('layouts.app')
@section('title', __('app.purchases').' — '.$purchase->purchase_no)

@section('content')
<div class="spread" style="margin-bottom:14px">
    <a class="btn ghost sm" href="{{ route('purchases.index') }}">← {{ __('app.back') }}</a>
</div>

<div class="card">
    <div class="card-head">
        <h3>📥 {{ $purchase->purchase_no }}</h3>
        <span class="badge gray">{{ $purchase->purchase_date->format('d/m/Y') }}</span>
    </div>
    <div class="card-body">
        <div class="row" style="margin-bottom:14px">
            <div><span class="muted small">{{ __('app.supplier') }}:</span>
                @if($purchase->supplier_id)
                    <a href="{{ route('suppliers.show',$purchase->supplier_id) }}"><b>{{ $purchase->supplier_name ?: '-' }}</b></a>
                @else
                    <b>{{ $purchase->supplier_name ?: '-' }}</b>
                @endif
            </div>
            <div><span class="muted small">{{ __('app.cashier') }}:</span> <b>{{ $purchase->user->name ?? '-' }}</b></div>
        </div>
        @if($purchase->note)<p class="muted">📝 {{ $purchase->note }}</p>@endif

        <div class="table-wrap">
            <table class="tbl">
                <thead><tr>
                    <th>{{ __('app.products') }}</th>
                    <th>{{ __('app.unit') }}</th>
                    <th class="num">{{ __('app.qty') }}</th>
                    <th class="num">{{ __('app.unit_cost') }}</th>
                    <th class="num">{{ __('app.amount') }}</th>
                </tr></thead>
                <tbody>
                @foreach($purchase->items as $it)
                    <tr>
                        <td>
                            <span class="badge {{ $it->product->type }}">{{ $it->product->type=='rice'?__('app.rice'):__('app.oil') }}</span>
                            {{ $it->product->displayName() }}
                        </td>
                        <td>{{ $it->unit_label }} <span class="muted small">(×{{ qty_fmt($it->factor) }})</span></td>
                        <td class="num">{{ qty_fmt($it->qty) }}</td>
                        <td class="num">{{ mmk($it->unit_cost) }}</td>
                        <td class="num strong">{{ mmk($it->line_cost) }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot><tr>
                    <td colspan="4" class="num">{{ __('app.grand_total') }}</td>
                    <td class="num">{{ mmk($purchase->total_cost) }} Ks</td>
                </tr></tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
