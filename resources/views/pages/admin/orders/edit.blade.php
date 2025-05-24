@extends('layouts.user_type.auth')

@section('content')
    @livewire('order-edit', ['orderId' => $id ?? null])
@endsection