@extends('layouts.app')
@section('title', __('app.customers').' — '.__('app.edit'))

@section('content')
<form method="POST" action="{{ route('customers.update', $customer) }}">
    @csrf
    @method('PUT')
    @include('customers._form')
</form>
@endsection
