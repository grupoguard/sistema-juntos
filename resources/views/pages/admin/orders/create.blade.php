@extends('layouts.user_type.auth')

@section('content')
    @livewire('order-form', ['orderId' => $id ?? null])
@endsection