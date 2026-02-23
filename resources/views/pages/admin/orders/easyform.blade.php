@extends('layouts.user_type.auth')

@section('content')
    @livewire('order-easy-form', ['draftId' => request('draft')])
@endsection