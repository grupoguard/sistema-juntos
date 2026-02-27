@extends('layouts.user_type.auth')

@section('content')
    <livewire:order-show :order="$order" />
@endsection