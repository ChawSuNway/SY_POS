@extends('layouts.app')
@section('title', __('app.brands'))

@section('content')
    <div class="grid cols-2">
        @foreach(['rice','oil'] as $type)
            <div class="card">
                <div class="card-head">
                    <h3>
                        <span class="badge {{ $type }}">{{ $type=='rice' ? __('app.rice') : __('app.oil') }}</span>
                        {{ __('app.brands') }}
                    </h3>
                </div>
                <div class="card-body tight">
                    <div class="table-wrap">
                        <table class="tbl">
                            <thead><tr>
                                <th>{{ __('app.name') }}</th>
                                <th style="width:80px">{{ __('app.active') }}</th>
                                <th class="num" style="width:150px">{{ __('app.actions') }}</th>
                            </tr></thead>
                            <tbody>
                            @forelse(($brands[$type] ?? []) as $brand)
                                <tr>
                                    <td>
                                        <input type="text" name="name" value="{{ $brand->name }}" form="brand-edit-{{ $brand->id }}" required>
                                    </td>
                                    <td>
                                        <label class="check">
                                            <input type="checkbox" name="is_active" value="1" form="brand-edit-{{ $brand->id }}" {{ $brand->is_active ? 'checked' : '' }}>
                                        </label>
                                    </td>
                                    <td class="num">
                                        <form id="brand-edit-{{ $brand->id }}" method="POST" action="{{ route('brands.update',$brand) }}" style="display:none">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="type" value="{{ $brand->type }}">
                                        </form>
                                        <div class="btn-row" style="justify-content:flex-end">
                                            <button type="submit" form="brand-edit-{{ $brand->id }}" class="btn primary sm">{{ __('app.save') }}</button>
                                            <form method="POST" action="{{ route('brands.destroy',$brand) }}" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn danger sm">{{ __('app.delete') }}</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="empty">{{ __('app.no_records') }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div style="padding:14px 18px;border-top:1px solid var(--border)">
                        <form method="POST" action="{{ route('brands.store') }}" class="inline-form">
                            @csrf
                            <input type="hidden" name="type" value="{{ $type }}">
                            <div class="field" style="flex:1;margin-bottom:0">
                                <input type="text" name="name" placeholder="{{ __('app.name') }}" required>
                            </div>
                            <button type="submit" class="btn primary">＋ {{ __('app.create') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
