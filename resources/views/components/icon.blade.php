@props(['name'])
{{-- Game icon from the cached sprite file (public/icons.svg). Sized by font-size; coloured by text colour. --}}
<svg {{ $attributes->class(['ic']) }} aria-hidden="true"><use href="{{ asset('icons.svg') }}#i-{{ $name }}"/></svg>
