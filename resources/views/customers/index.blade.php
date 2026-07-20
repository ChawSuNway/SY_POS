@extends('layouts.app')
@section('title', __('app.customers'))

@section('content')
<div class="card">
    <div class="card-head">
        <form method="GET" class="inline-form" style="margin:0">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="🔍 {{ __('app.search') }}… ({{ __('app.name') }}/{{ __('app.phone') }})" style="max-width:260px">
            <button class="btn ghost sm">{{ __('app.filter') }}</button>
        </form>
        <a class="btn primary" href="{{ route('customers.create') }}">＋ {{ __('app.create') }}</a>
    </div>
    <div class="card-body tight">
        <div class="table-wrap">
            <table class="tbl">
                <thead><tr>
                    <th>{{ __('app.name') }}</th>
                    <th>{{ __('app.phone') }}</th>
                    <th>{{ __('app.address') }}</th>
                    <th class="num">{{ __('app.transactions') }}</th>
                    <th class="num">{{ __('app.total_spent') }}</th>
                    <th class="num">{{ __('app.actions') }}</th>
                </tr></thead>
                <tbody>
                @forelse($customers as $c)
                    <tr>
                        <td class="strong">
                            <a href="{{ route('customers.show',$c) }}">{{ $c->name }}</a>
                            @unless($c->is_active)<span class="badge gray">{{ __('app.inactive') }}</span>@endunless
                        </td>
                        <td>{{ $c->phone ?: '-' }}</td>
                        <td class="small muted">{{ $c->address ?: '-' }}</td>
                        <td class="num">{{ $c->sales_count }}</td>
                        <td class="num strong">{{ mmk($c->sales_sum_total ?? 0) }}</td>
                        <td class="num">
                            <div class="btn-row" style="justify-content:flex-end">
                                <a class="btn ghost sm" href="{{ route('customers.show',$c) }}">{{ __('app.view') }}</a>
                                <a class="btn ghost sm" href="{{ route('customers.edit',$c) }}">{{ __('app.edit') }}</a>
                                <form method="POST" action="{{ route('customers.destroy',$c) }}" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                                    @csrf @method('DELETE')
                                    <button class="btn danger sm">✕</button>
                                </form>
                            </div>
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
<div>{{ $customers->links() }}</div>
@endsection
