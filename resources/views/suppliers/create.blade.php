@extends('layouts.app')
@section('title', __('app.suppliers').' — '.__('app.create'))

@section('content')
<form method="POST" action="{{ route('suppliers.store') }}">
    @csrf
    @include('suppliers._form')
</form>
@endsection
