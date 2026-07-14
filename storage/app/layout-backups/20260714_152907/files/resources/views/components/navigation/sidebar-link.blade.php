@props(['href', 'active' => false, 'icon' => '•'])
<a href="{{ $href }}" wire:navigate {{ $attributes->class(['sidebar-link', 'sidebar-link-active' => $active]) }}><span class="grid h-8 w-8 place-items-center rounded-xl bg-white/5 text-base">{{ $icon }}</span><span>{{ $slot }}</span></a>
