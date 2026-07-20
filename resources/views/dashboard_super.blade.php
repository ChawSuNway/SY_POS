@extends('layouts.app')
@section('title', __('app.dashboard'))

@section('content')
    <div class="grid cols-4" style="margin-bottom:16px">
        <div class="stat a-blue">
            <div class="accent"></div>
            <div class="label">{{ __('app.total_shops') }}</div>
            <div class="value">{{ $totals['shops'] }}</div>
            <div class="sub">{{ $totals['active'] }} {{ __('app.active_shops') }}</div>
        </div>
        <div class="stat a-green">
            <div class="accent"></div>
            <div class="label">{{ __('app.today_revenue') }} — {{ __('app.all_shops') }}</div>
            <div class="value">{{ mmk($totals['revenue']) }}</div>
            <div class="sub">Ks</div>
        </div>
        <div class="stat a-amber">
            <div class="accent"></div>
            <div class="label">{{ __('app.sales_count') }} — {{ __('app.all_shops') }}</div>
            <div class="value">{{ $totals['sales'] }}</div>
            <div class="sub">{{ $today->format('d/m/Y') }}</div>
        </div>
        <a class="stat a-red" href="{{ route('shops.create') }}" style="text-decoration:none;color:inherit">
            <div class="accent"></div>
            <div class="label">＋ {{ __('app.add_shop') }}</div>
            <div class="value">🏬</div>
            <div class="sub">{{ __('app.manage') }}</div>
        </a>
    </div>

    <div class="card">
        <div class="card-head">
            <h3>🏬 {{ __('app.shops') }}</h3>
            <a class="btn primary sm" href="{{ route('shops.index') }}">{{ __('app.manage') }} →</a>
        </div>
        <div class="card-body tight">
            <div class="table-wrap">
                <table class="tbl">
                    <thead><tr>
                        <th>{{ __('app.shop') }}</th>
                        <th class="num">{{ __('app.staff_count') }}</th>
                        <th class="num">{{ __('app.product_count') }}</th>
                        <th class="num">{{ __('app.today_revenue') }}</th>
                        <th>{{ __('app.status') }}</th>
                    </tr></thead>
                    <tbody>
                    @forelse($shops as $shop)
                        @php $st = $salesToday->get($shop->id); @endphp
                        <tr>
                            <td>
                                <a href="{{ route('shops.edit',$shop) }}" style="text-decoration:none;color:inherit">
                                    <span class="strong">{{ $shop->name }}</span>
                                    @if($shop->name_en)<span class="small muted"> · {{ $shop->name_en }}</span>@endif
                                </a>
                            </td>
                            <td class="num">{{ $shop->users_count }}</td>
                            <td class="num">{{ $shop->products_count }}</td>
                            <td class="num strong">{{ mmk($st->revenue ?? 0) }}</td>
                            <td>
                                @if($shop->is_active)<span class="badge green">{{ __('app.active') }}</span>
                                @else<span class="badge gray">{{ __('app.inactive') }}</span>@endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
