@extends('layouts.user_type.auth')

@section('content')
    @livewire('partner-categories-form', ['partnerCategoriesId' => $id ?? null])
@endsection