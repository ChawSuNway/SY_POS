@extends('layouts.app')
@section('title', __('app.expenses'))

@section('content')
<div class="grid cols-2" style="margin-bottom:16px;align-items:start">
    <div class="stat a-amber">
        <div class="accent"></div>
        <div class="label">💸 {{ __('app.month_total') }} — {{ $month }}</div>
        <div class="value">{{ mmk($monthTotal) }}</div>
        <div class="sub">Ks</div>
    </div>
    <div class="card">
        <div class="card-head"><h3>🏷️ {{ __('app.by_category') }}</h3></div>
        <div class="card-body tight">
            <div class="table-wrap" style="max-height:180px;overflow-y:auto">
                <table class="tbl">
                    <tbody>
                    @forelse($byCategory as $c)
                        <tr>
                            <td><b>{{ $c->category }}</b> <span class="small muted">({{ $c->cnt }})</span></td>
                            <td class="num strong">{{ mmk($c->total) }}</td>
                        </tr>
                    @empty
                        <tr><td class="empty">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- အသုံးစရိတ် အသစ် မှတ်ရန် --}}
<div class="card" style="margin-bottom:16px">
    <div class="card-head"><h3>＋ {{ __('app.add_expense') }}</h3></div>
    <div class="card-body">
        <form method="POST" action="{{ route('expenses.store') }}">
            @csrf
            <div class="form-grid" style="grid-template-columns:150px 1fr 160px 1fr auto;align-items:end">
                <div class="field" style="margin-bottom:0">
                    <label>{{ __('app.expense_date') }}</label>
                    <input type="date" name="expense_date" value="{{ old('expense_date', date('Y-m-d')) }}" required>
                </div>
                <div class="field" style="margin-bottom:0">
                    <label>{{ __('app.expense_category') }}</label>
                    <input type="text" name="category" list="catList" value="{{ old('category') }}"
                           placeholder="၀န်ထမ်းလစာ / အိတ်ဖိုး / သယ်ယူစရိတ် …" required>
                    <datalist id="catList">
                        @foreach($categories as $cat)<option value="{{ $cat }}">@endforeach
                    </datalist>
                </div>
                <div class="field" style="margin-bottom:0">
                    <label>{{ __('app.pay_amount') }} (Ks)</label>
                    <input type="number" name="amount" min="1" step="1" value="{{ old('amount') }}" required style="text-align:right">
                </div>
                <div class="field" style="margin-bottom:0">
                    <label>{{ __('app.note') }}</label>
                    <input type="text" name="note" value="{{ old('note') }}">
                </div>
                <button type="submit" class="btn primary">💾 {{ __('app.save') }}</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <h3>💸 {{ __('app.expenses') }}</h3>
        <form method="GET" class="inline-form" style="margin:0">
            <input type="month" name="month" value="{{ $month }}">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="🔍 {{ __('app.search') }}…" style="max-width:170px">
            <button class="btn ghost sm">{{ __('app.filter') }}</button>
        </form>
    </div>
    <div class="card-body tight">
        <div class="table-wrap">
            <table class="tbl">
                <thead><tr>
                    <th style="width:110px">{{ __('app.expense_date') }}</th>
                    <th>{{ __('app.expense_category') }}</th>
                    <th>{{ __('app.note') }}</th>
                    <th class="num">{{ __('app.pay_amount') }}</th>
                    <th>{{ __('app.recorded_by') }}</th>
                    <th class="num" style="width:70px"></th>
                </tr></thead>
                <tbody>
                @forelse($expenses as $e)
                    <tr>
                        <td class="small">{{ $e->expense_date->format('d/m/Y') }}</td>
                        <td><span class="badge blue">{{ $e->category }}</span></td>
                        <td class="small muted">{{ $e->note ?: '—' }}</td>
                        <td class="num strong">{{ mmk($e->amount) }}</td>
                        <td class="small muted">{{ $e->user->name ?? '—' }}</td>
                        <td class="num">
                            <form method="POST" action="{{ route('expenses.destroy',$e) }}" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button class="btn danger sm">✕</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">{{ __('app.no_records') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div>{{ $expenses->links() }}</div>
@endsection
