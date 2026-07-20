@extends('layouts.app')
@section('title', __('app.users'))

@section('content')
    <div class="card">
        <div class="card-head">
            <h3>👥 {{ __('app.users') }}</h3>
            <a class="btn primary" href="{{ route('users.create') }}">＋ {{ __('app.create') }}</a>
        </div>
        <div class="card-body tight">
            <div class="table-wrap">
                <table class="tbl">
                    <thead><tr>
                        <th>{{ __('app.name') }}</th>
                        <th>{{ __('app.email') }}</th>
                        <th>{{ __('app.role') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th class="num">{{ __('app.actions') }}</th>
                    </tr></thead>
                    <tbody>
                    @forelse($users as $user)
                        @php
                            $roleClass = ['admin' => 'red', 'manager' => 'amber', 'cashier' => 'blue'][$user->role] ?? 'gray';
                        @endphp
                        <tr>
                            <td class="strong">{{ $user->name }}</td>
                            <td class="small muted">{{ $user->email }}</td>
                            <td><span class="badge {{ $roleClass }}">{{ $user->roleLabel() }}</span></td>
                            <td>
                                <span class="badge {{ $user->is_active ? 'green' : 'gray' }}">
                                    {{ $user->is_active ? __('app.active') : __('app.inactive') }}
                                </span>
                            </td>
                            <td class="num">
                                <div class="btn-row" style="justify-content:flex-end">
                                    <a class="btn ghost sm" href="{{ route('users.edit',$user) }}">{{ __('app.edit') }}</a>
                                    <form method="POST" action="{{ route('users.destroy',$user) }}" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn danger sm">{{ __('app.delete') }}</button>
                                    </form>
                                </div>
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

    {{ $users->links() }}
@endsection
