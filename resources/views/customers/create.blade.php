@extends('layouts.app')
@section('title', __('app.customers').' — '.__('app.create'))

@section('content')
<form method="POST" action="{{ route('customers.store') }}">
    @csrf
    @include('customers._form')
</form>
@endsection
