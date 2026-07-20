@extends('layouts.app')
@section('title', __('app.suppliers').' — '.__('app.edit'))

@section('content')
<form method="POST" action="{{ route('suppliers.update', $supplier) }}">
    @csrf
    @method('PUT')
    @include('suppliers._form')
</form>
@endsection
