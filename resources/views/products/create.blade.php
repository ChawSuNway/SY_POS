@extends('layouts.app')
@section('title', __('app.products').' — '.__('app.create'))

@section('content')
<form method="POST" action="{{ route('products.store') }}">
    @csrf
    @include('products._form', ['presetType' => 'rice'])
</form>
@endsection
