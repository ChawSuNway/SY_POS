@extends('layouts.app')
@section('title', __('app.losses'))

@section('content')
<div class="grid cols-4" style="margin-bottom:16px">
    <div class="stat a-red">
        <div class="accent"></div>
        <div class="label">{{ __('app.total_loss') }}</div>
        <div class="value">{{ mmk($totalValue) }}</div>
        <div class="sub">Ks</div>
    </div>
    <div class="stat a-amber">
        <div class="accent"></div>
        <div class="label">{{ __('app.total_loss') }} — {{ __('app.this_month') }}</div>
        <div class="value">{{ mmk($monthValue) }}</div>
        <div class="sub">{{ now()->format('m/Y') }}</div>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <form method="GET" class="inline-form" style="margin:0">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="🔍 {{ __('app.search') }}…" style="max-width:220px">
            <button class="btn ghost sm">{{ __('app.filter') }}</button>
        </form>
        <a class="btn primary" href="{{ route('losses.create') }}">＋ {{ __('app.record_loss') }}</a>
    </div>
    <div class="card-body tight">
        <div class="table-wrap">
            <table class="tbl">
                <thead><tr>
                    <th style="width:110px">{{ __('app.lost_at') }}</th>
                    <th>{{ __('app.products') }}</th>
                    <th class="num">{{ __('app.qty') }}</th>
                    <th class="num">{{ __('app.loss_value') }}</th>
                    <th>{{ __('app.loss_reason') }}</th>
                    <th>{{ __('app.recorded_by') }}</th>
                    <th class="num" style="width:70px">{{ __('app.actions') }}</th>
                </tr></thead>
                <tbody>
                @forelse($losses as $loss)
                    <tr>
                        <td>{{ $loss->lost_at->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge {{ $loss->product->type }}">{{ $loss->product->type=='rice'?__('app.rice'):__('app.oil') }}</span>
                            <span class="strong">{{ $loss->product->displayName() }}</span>
                        </td>
                        <td class="num strong">{{ qty_fmt($loss->qty) }} {{ $loss->unit_label }}</td>
                        <td class="num strong" style="color:var(--red)">{{ mmk($loss->loss_value) }}</td>
                        <td>{{ $loss->reason }}</td>
                        <td class="small muted">{{ $loss->user->name ?? '-' }}</td>
                        <td class="num">
                            <form method="POST" action="{{ route('losses.destroy',$loss) }}"
                                  onsubmit="return confirm('{{ __('app.confirm_delete') }} (လက်ကျန် ပြန်ထည့်ပါမည်)')">
                                @csrf @method('DELETE')
                                <button class="btn danger sm">✕</button>
                            </form>
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
<div>{{ $losses->links() }}</div>
@endsection
