@props(['href', 'active' => false, 'icon' => '•'])

<a
    href="{{ $href }}"
    wire:navigate
    {{ $attributes->class(['sidebar-link', 'sidebar-link-active' => $active]) }}
>
    <span class="sidebar-icon" aria-hidden="true">{{ $icon }}</span>
    <span class="min-w-0 truncate">{{ $slot }}</span>
</a>
