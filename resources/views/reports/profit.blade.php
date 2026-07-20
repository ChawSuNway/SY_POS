@extends('layouts.app')
@section('title', __('app.profit_report'))

@section('content')
    <div class="card" style="margin-bottom:16px">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.profit') }}" class="inline-form">
                <div class="field" style="margin-bottom:0">
                    <label>{{ __('app.from_date') }}</label>
                    <input type="date" name="from" value="{{ request('from', $from->format('Y-m-d')) }}">
                </div>
                <div class="field" style="margin-bottom:0">
                    <label>{{ __('app.to_date') }}</label>
                    <input type="date" name="to" value="{{ request('to', $to->format('Y-m-d')) }}">
                </div>
                <div class="field" style="margin-bottom:0">
                    <label>{{ app()->getLocale()=='my' ? 'ခွဲခြမ်းရန်' : 'Group by' }}</label>
                    <select name="group">
                        <option value="product" {{ $groupBy=='product'?'selected':'' }}>{{ app()->getLocale()=='my' ? 'ကုန်ပစ္စည်း' : 'Product' }}</option>
                        <option value="brand" {{ $groupBy=='brand'?'selected':'' }}>{{ app()->getLocale()=='my' ? 'တံဆိပ်' : 'Brand' }}</option>
                        <option value="category" {{ $groupBy=='category'?'selected':'' }}>{{ app()->getLocale()=='my' ? 'အမျိုးအစား' : 'Category' }}</option>
                        <option value="type" {{ $groupBy=='type'?'selected':'' }}>{{ app()->getLocale()=='my' ? 'ဆန်/ဆီ' : 'Type' }}</option>
                    </select>
                </div>
                <button type="submit" class="btn primary">{{ __('app.filter') }}</button>
            </form>
        </div>
    </div>

    <div class="grid cols-3" style="margin-bottom:16px">
        <div class="stat a-green">
            <div class="accent"></div>
            <div class="label">{{ __('app.revenue') }}</div>
            <div class="value">{{ mmk($totals['revenue']) }}</div>
            <div class="sub">Ks</div>
        </div>
        <div class="stat a-amber">
            <div class="accent"></div>
            <div class="label">{{ __('app.cogs') }}</div>
            <div class="value">{{ mmk($totals['cost']) }}</div>
            <div class="sub">Ks</div>
        </div>
        <div class="stat a-blue">
            <div class="accent"></div>
            <div class="label">{{ __('app.profit') }}</div>
            <div class="value">{{ mmk($totals['profit']) }}</div>
            <div class="sub">{{ $from->format('d/m/Y') }} — {{ $to->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <h3>💰 {{ __('app.profit_report') }}</h3>
        </div>
        <div class="card-body tight">
            <div class="table-wrap">
                <table class="tbl">
                    <thead><tr>
                        <th>{{ app()->getLocale()=='my' ? 'အမည်' : 'Label' }}</th>
                        <th class="num">{{ __('app.qty') }}</th>
                        <th class="num">{{ __('app.revenue') }}</th>
                        <th class="num">{{ __('app.cogs') }}</th>
                        <th class="num">{{ __('app.profit') }}</th>
                        <th class="num">{{ app()->getLocale()=='my' ? 'အမြတ် %' : 'Margin %' }}</th>
                    </tr></thead>
                    <tbody>
                    @forelse($grouped as $row)
                        <tr>
                            <td class="strong">{{ $row['label'] }}</td>
                            <td class="num">{{ qty_fmt($row['qty_base']) }}</td>
                            <td class="num">{{ mmk($row['revenue']) }}</td>
                            <td class="num">{{ mmk($row['cost']) }}</td>
                            <td class="num strong" style="color:{{ $row['profit'] >= 0 ? 'var(--green)' : 'var(--red)' }}">{{ mmk($row['profit']) }}</td>
                            <td class="num">{{ $row['revenue'] > 0 ? round($row['profit'] / $row['revenue'] * 100, 1).'%' : '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                    </tbody>
                    @if($grouped->count())
                    <tfoot>
                        <tr>
                            <td>{{ __('app.grand_total') }}</td>
                            <td class="num"></td>
                            <td class="num">{{ mmk($totals['revenue']) }}</td>
                            <td class="num">{{ mmk($totals['cost']) }}</td>
                            <td class="num" style="color:{{ $totals['profit'] >= 0 ? 'var(--green)' : 'var(--red)' }}">{{ mmk($totals['profit']) }}</td>
                            <td class="num">{{ $totals['revenue'] > 0 ? round($totals['profit'] / $totals['revenue'] * 100, 1).'%' : '-' }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
@endsection
