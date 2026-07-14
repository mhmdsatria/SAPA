<button
    type="button"
    class="icon-button"
    @click="dark = window.applyTheme(!dark)"
    :aria-label="dark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap'"
    title="Ubah tema"
>
    <svg x-show="!dark" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
    </svg>
    <svg x-show="dark" x-cloak class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v1.5M12 19.5V21M4.22 4.22l1.06 1.06M18.72 18.72l1.06 1.06M3 12h1.5M19.5 12H21M4.22 19.78l1.06-1.06M18.72 5.28l1.06-1.06M16.5 12a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
    </svg>
</button>
