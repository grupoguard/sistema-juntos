@props(['paginator'])

{{ dd($paginator) }}

@if ($paginator->hasPages())
    {{ $paginator->links('vendor.pagination.bootstrap-5') }}
@endif
