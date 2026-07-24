@extends('layouts.app')
@section('title', __('app.shops'))

@section('content')
<div class="card">
    <div class="card-head">
        <h3>🏬 {{ __('app.shops') }}</h3>
        <a class="btn primary" href="{{ route('shops.create') }}">＋ {{ __('app.add_shop') }}</a>
    </div>
    <div class="card-body tight">
        <div class="table-wrap">
            <table class="tbl">
                <thead><tr>
                    <th>{{ __('app.shop') }}</th>
                    <th>{{ __('app.phone') }}</th>
                    <th class="num">{{ __('app.staff_count') }}</th>
                    <th class="num">{{ __('app.product_count') }}</th>
                    <th class="num">{{ __('app.sales_count') }}</th>
                    <th>{{ __('app.status') }}</th>
                    <th class="num">{{ __('app.actions') }}</th>
                </tr></thead>
                <tbody>
                @forelse($shops as $shop)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px">
                                @if($shop->logoUrl())
                                    <img src="{{ $shop->logoUrl() }}" alt="" style="height:34px;width:34px;object-fit:contain;border-radius:8px;border:1px solid var(--border)">
                                @else
                                    <span style="font-size:1.4rem">🏪</span>
                                @endif
                                <div>
                                    <div class="strong">{{ $shop->name }}</div>
                                    @if($shop->name_en)<div class="small muted">{{ $shop->name_en }}</div>@endif
                                </div>
                            </div>
                        </td>
                        <td>{{ $shop->phone ?: '—' }}</td>
                        <td class="num">{{ $shop->users_count }}</td>
                        <td class="num">{{ $shop->products_count }}</td>
                        <td class="num">{{ $shop->sales_count }}</td>
                        <td>
                            @if($shop->is_active)<span class="badge green">{{ __('app.active') }}</span>
                            @else<span class="badge gray">{{ __('app.inactive') }}</span>@endif
                        </td>
                        <td class="num">
                            <div class="btn-row" style="justify-content:flex-end">
                                <form method="POST" action="{{ route('shops.enter',$shop) }}">
                                    @csrf
                                    <button class="btn primary sm" title="{{ __('app.enter_shop_hint') }}">⚙ {{ __('app.enter_shop') }}</button>
                                </form>
                                <a class="btn ghost sm" href="{{ route('shops.edit',$shop) }}">✎ {{ __('app.edit') }}</a>
                                <form method="POST" action="{{ route('shops.destroy',$shop) }}" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                                    @csrf @method('DELETE')
                                    <button class="btn danger sm">✕</button>
                                </form>
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
@endsection
