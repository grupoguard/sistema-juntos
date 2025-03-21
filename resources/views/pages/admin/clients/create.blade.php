@extends('layouts.user_type.auth')

@section('content')
    @livewire('client-form', ['clientId' => $id ?? null])
@endsection