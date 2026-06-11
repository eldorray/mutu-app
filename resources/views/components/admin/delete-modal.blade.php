@props([
    'id' => 'delete-modal',
    'title' => 'Hapus Data',
    'message' => 'Yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.',
    'action' => '',
])

<div x-data="{ open: false, action: '', title: '', message: '' }"
    x-on:open-delete-modal.window="if ($event.detail.id === '{{ $id }}') { open = true; action = $event.detail.action; title = $event.detail.title || '{{ $title }}'; message = $event.detail.message || '{{ $message }}'; }"
    x-on:keydown.escape.window="open = false" x-cloak>

    {{-- Backdrop --}}
    <div x-show="open" x-transition.opacity class="fixed inset-0 bg-ink-900/50 backdrop-blur-sm z-50"
        @click="open = false"></div>

    {{-- Modal --}}
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 z-50 flex items-center justify-center p-4">

        <div class="bg-white rounded-2xl shadow-soft w-full max-w-md p-6" @click.stop>
            {{-- Icon --}}
            <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <polyline points="3 6 5 6 21 6" />
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                    <line x1="10" x2="10" y1="11" y2="17" />
                    <line x1="14" x2="14" y1="11" y2="17" />
                </svg>
            </div>

            {{-- Content --}}
            <h3 class="text-lg font-bold text-ink-900 text-center mb-2" x-text="title"></h3>
            <p class="text-sm text-ink-500 text-center mb-6" x-text="message"></p>

            {{-- Actions --}}
            <div class="flex items-center gap-3 justify-center">
                <button @click="open = false" type="button" class="btn-ghost px-5">
                    Batal
                </button>
                <button
                    @click="$wire.$call(...action.split('(').length > 1 ? [action.split('(')[0], ...action.split('(')[1].replace(')','').split(',').map(v => isNaN(v.trim()) ? v.trim() : Number(v.trim()))] : [action]); open = false;"
                    type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-full bg-red-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-red-600 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <polyline points="3 6 5 6 21 6" />
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                    </svg>
                    Hapus
                </button>
            </div>
        </div>
    </div>
</div>
