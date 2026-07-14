@props(['complaint'])

<article class="panel overflow-hidden">
    <a href="{{ route('complaints.show', $complaint) }}" wire:navigate class="block">
        <img src="{{ $complaint->image_url }}" alt="Foto {{ $complaint->title }}" class="h-48 w-full object-cover" loading="lazy">
    </a>
    <div class="space-y-3 p-5">
        <div class="flex flex-wrap items-center gap-2">
            <x-badge.category :category="$complaint->category" />
            <x-badge.status :status="$complaint->status" />
            @if ($complaint->exif_is_stale)
                <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-800">⚠️ Foto lama</span>
            @endif
        </div>
        <h3 class="line-clamp-2 text-lg font-bold text-slate-950 dark:text-white">
            <a href="{{ route('complaints.show', $complaint) }}" wire:navigate>{{ $complaint->title }}</a>
        </h3>
        <p class="line-clamp-2 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $complaint->description }}</p>
        <p class="line-clamp-1 text-xs text-slate-500">📍 {{ $complaint->address_text }}</p>
        <div class="flex items-center justify-between border-t border-slate-100 pt-3 text-xs text-slate-500 dark:border-slate-800">
            <span>{{ $complaint->reporter_name }} · {{ $complaint->created_at->diffForHumans() }}</span>
            <span>▲ {{ $complaint->upvotes_count }} · 💬 {{ $complaint->comments_count }}</span>
        </div>
    </div>
</article>
