@extends('layouts.app')
@section('title', __('app.products').' — '.__('app.edit'))

@section('content')
<form method="POST" action="{{ route('products.update', $product) }}">
    @csrf
    @method('PUT')
    @include('products._form')
</form>
@endsection
