@extends('layouts.app')
@section('title', __('app.activity_logs'))

@section('content')
@php
    // action (route name) → မြန်မာ/အင်္ဂလိပ် readable label
    $verb = fn($m) => match($m) {
        'POST'   => ['＋', 'var(--green)'],
        'PUT','PATCH' => ['✎', 'var(--blue)'],
        'DELETE' => ['✕', 'var(--red)'],
        default  => ['·', 'var(--muted)'],
    };
@endphp

<div class="card">
    <div class="card-head">
        <h3>🧭 {{ __('app.activity_logs') }}</h3>
        <form method="GET" class="inline-form" style="margin:0;gap:6px;flex-wrap:wrap">
            <select name="user_id" style="max-width:170px">
                <option value="">— {{ __('app.all_users') }} —</option>
                @foreach($users as $usr)
                    <option value="{{ $usr->id }}" {{ request('user_id')==$usr->id?'selected':'' }}>{{ $usr->name }}</option>
                @endforeach
            </select>
            <select name="shop_id" style="max-width:160px">
                <option value="">— {{ __('app.all_shops') }} —</option>
                @foreach($shops as $sh)
                    <option value="{{ $sh->id }}" {{ request('shop_id')==$sh->id?'selected':'' }}>{{ $sh->displayName() }}</option>
                @endforeach
            </select>
            <input type="text" name="action" value="{{ request('action') }}" placeholder="action…" style="max-width:130px">
            <input type="date" name="from" value="{{ request('from') }}" title="{{ __('app.from_date') }}">
            <input type="date" name="to" value="{{ request('to') }}" title="{{ __('app.to_date') }}">
            <button class="btn ghost sm">{{ __('app.filter') }}</button>
            @if(request()->hasany(['user_id','shop_id','action','from','to']))
                <a class="btn ghost sm" href="{{ route('activity-logs.index') }}">✕</a>
            @endif
        </form>
    </div>
    <div class="card-body tight">
        <div class="table-wrap">
            <table class="tbl">
                <thead><tr>
                    <th style="width:150px">{{ __('app.log_time') }}</th>
                    <th>{{ __('app.log_user') }}</th>
                    <th>{{ __('app.shop') }}</th>
                    <th>{{ __('app.log_action') }}</th>
                    <th style="width:90px">{{ __('app.log_subject') }}</th>
                    <th style="width:120px">IP</th>
                </tr></thead>
                <tbody>
                @forelse($logs as $log)
                    @php [$sym,$col] = $verb($log->method); @endphp
                    <tr>
                        <td class="small muted">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                        <td>
                            <b>{{ $log->user?->name ?? '—' }}</b>
                            @if($log->user)<span class="small muted">· {{ $log->user->roleLabel() }}</span>@endif
                        </td>
                        <td class="small">{{ $log->shop?->displayName() ?? '—' }}</td>
                        <td>
                            <span style="color:{{ $col }};font-weight:700">{{ $sym }}</span>
                            <span class="badge gray">{{ $log->action }}</span>
                        </td>
                        <td class="small muted">{{ $log->subject_type ? $log->subject_type.' #'.$log->subject_id : '—' }}</td>
                        <td class="small muted">{{ $log->ip }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">{{ __('app.no_records') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div style="margin-top:12px">{{ $logs->links() }}</div>
@endsection
