@props(['label', 'value', 'hint' => null, 'icon' => '•'])

<article {{ $attributes->class('panel stat-card') }}>
    <div class="flex items-start justify-between gap-2">
        <div class="min-w-0">
            <p class="truncate text-[10px] font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400 sm:text-xs">{{ $label }}</p>
            <p class="mt-1.5 truncate text-2xl font-black tracking-tight text-slate-950 dark:text-white sm:mt-2 sm:text-3xl">{{ $value }}</p>
            @if($hint)
                <p class="mt-1 line-clamp-1 text-[10px] text-slate-400 sm:text-xs">{{ $hint }}</p>
            @endif
        </div>
        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-xl bg-blue-50 text-sm font-black text-blue-700 dark:bg-blue-950/50 dark:text-blue-300 sm:h-10 sm:w-10 sm:text-lg">{{ $icon }}</span>
    </div>
</article>
