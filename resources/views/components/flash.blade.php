<div x-data="{ visible: true }" x-show="visible" x-transition>
    @if (session('success'))
        <div class="mb-5 flex items-start justify-between rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-200">
            <span>{{ session('success') }}</span>
            <button type="button" @click="visible = false" aria-label="Tutup">×</button>
        </div>
    @elseif (session('error'))
        <div class="mb-5 flex items-start justify-between rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/50 dark:text-red-200">
            <span>{{ session('error') }}</span>
            <button type="button" @click="visible = false" aria-label="Tutup">×</button>
        </div>
    @endif
</div>
