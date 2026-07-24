@extends('layouts.app')
@section('title', __('app.categories'))

@section('content')
<div class="grid cols-2" style="align-items:start">
    @foreach(['rice','oil'] as $type)
        <div class="card">
            <div class="card-head">
                <h3>
                    <span class="badge {{ $type }}">{{ $type=='rice' ? __('app.rice') : __('app.oil') }}</span>
                    {{ __('app.categories') }}
                </h3>
            </div>
            <div class="card-body">
                @forelse(($parents[$type] ?? []) as $parent)
                    <div style="border:1px solid var(--border);border-radius:10px;margin-bottom:12px;overflow:hidden">
                        {{-- Parent row --}}
                        <div style="display:flex;gap:8px;align-items:center;padding:10px 12px;background:var(--surface-2,#f8fafc)">
                            <span style="font-size:1.05rem">📂</span>
                            <form method="POST" action="{{ route('categories.update',$parent) }}" class="inline-form" style="flex:1;margin:0;gap:6px">
                                @csrf @method('PUT')
                                <input type="text" name="name" value="{{ $parent->name }}" required maxlength="100" style="flex:1;font-weight:600">
                                <label class="check" title="{{ __('app.active') }}"><input type="checkbox" name="is_active" value="1" {{ $parent->is_active?'checked':'' }}></label>
                                <button type="submit" class="btn primary sm">{{ __('app.save') }}</button>
                            </form>
                            <form method="POST" action="{{ route('categories.destroy',$parent) }}" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn danger sm">✕</button>
                            </form>
                        </div>

                        {{-- Sub-categories --}}
                        <div style="padding:6px 12px 12px 34px">
                            @foreach($parent->children as $sub)
                                <div style="display:flex;gap:8px;align-items:center;margin-top:8px">
                                    <span class="muted">▸</span>
                                    <form method="POST" action="{{ route('categories.update',$sub) }}" class="inline-form" style="flex:1;margin:0;gap:6px">
                                        @csrf @method('PUT')
                                        <input type="text" name="name" value="{{ $sub->name }}" required maxlength="100" style="flex:1">
                                        <label class="check" title="{{ __('app.active') }}"><input type="checkbox" name="is_active" value="1" {{ $sub->is_active?'checked':'' }}></label>
                                        <button type="submit" class="btn ghost sm">{{ __('app.save') }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('categories.destroy',$sub) }}" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn danger sm">✕</button>
                                    </form>
                                </div>
                            @endforeach

                            {{-- Add sub-category --}}
                            <form method="POST" action="{{ route('categories.store') }}" class="inline-form" style="margin-top:10px;gap:6px">
                                @csrf
                                <input type="hidden" name="parent_id" value="{{ $parent->id }}">
                                <input type="text" name="name" placeholder="＋ {{ __('app.sub_category') }}" required maxlength="100" style="flex:1">
                                <button type="submit" class="btn ghost sm">＋ {{ __('app.add') }}</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="empty">{{ __('app.no_records') }}</p>
                @endforelse

                {{-- Add parent category --}}
                <form method="POST" action="{{ route('categories.store') }}" class="inline-form" style="border-top:1px solid var(--border);padding-top:14px;margin-top:4px">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">
                    <input type="text" name="name" placeholder="{{ __('app.parent_category') }}" required maxlength="100" style="flex:1">
                    <button type="submit" class="btn primary">＋ {{ __('app.create') }}</button>
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection
