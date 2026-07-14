@props(['href', 'active' => false])

<a
    href="{{ $href }}"
    wire:navigate
    {{ $attributes->class(['nav-link', 'nav-link-active' => $active]) }}
>
    {{ $slot }}
</a>
