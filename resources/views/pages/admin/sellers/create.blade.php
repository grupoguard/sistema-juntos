@extends('layouts.user_type.auth')

@section('content')
    @livewire('seller-form', ['sellerId' => $id ?? null])
@endsection