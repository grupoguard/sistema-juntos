@extends('layouts.user_type.auth')

@section('content')
    @livewire('partner-form', ['partnerId' => $id ?? null])
@endsection