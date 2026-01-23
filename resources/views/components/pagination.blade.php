@props(['paginator'])

{{ dd($paginator->links) }}

@if ($paginator->hasPages())
    {{ $paginator->links('vendor.pagination.bootstrap-5') }}
@endif
