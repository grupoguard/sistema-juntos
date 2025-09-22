@extends('layouts.user_type.auth')

@section('content')
    @livewire('group-form', ['groupId' => $id ?? null])
@endsection