@extends('layouts.user_type.auth')

@section('content')
    @livewire('product-form', ['productId' => $id ?? null])
@endsection