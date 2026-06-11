<div class="space-y-6">
    <header>
        <h3 class="text-lg font-semibold text-ink-900">Color theme</h3>
        <p class="text-sm text-ink-500">Pick a brand color. It applies to the admin panel and the public landing page.</p>
    </header>

    @if (session('theme_status'))
        <div class="rounded-2xl bg-emerald-50 border border-emerald-100 px-4 py-3 text-sm text-emerald-700">
            {{ session('theme_status') }}
        </div>
    @endif

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach ($themes as $item)
            @php $active = $theme === $item['key']; @endphp
            <button type="button" wire:click="setTheme('{{ $item['key'] }}')"
                class="text-left rounded-2xl border p-4 transition-all
                       {{ $active ? 'border-ink-900 ring-2 ring-ink-900/10 bg-white' : 'border-ink-200 hover:border-ink-300 bg-white' }}">
                <div class="flex items-center justify-between mb-3">
                    <span class="w-10 h-10 rounded-xl shrink-0"
                        style="background-color: {{ $item['swatch'] }}"></span>

                    @if ($active)
                        <span class="w-6 h-6 rounded-full bg-ink-900 flex items-center justify-center text-white">
                            <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="3" viewBox="0 0 24 24" class="w-3.5 h-3.5">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                        </span>
                    @endif
                </div>
                <p class="font-semibold text-ink-900">{{ $item['label'] }}</p>
                <p class="text-xs text-ink-500 mt-0.5">{{ $item['key'] }}</p>
            </button>
        @endforeach
    </div>

    <div class="rounded-2xl bg-ink-50 p-5">
        <p class="text-xs font-medium text-ink-500 uppercase tracking-wide mb-3">Preview</p>
        <div class="flex flex-wrap items-center gap-3">
            <button type="button" class="btn-primary">Primary button</button>
            <button type="button" class="btn-ghost">Ghost button</button>
            <span class="px-3 py-1.5 rounded-full bg-brand-100 text-brand-700 text-sm font-medium">Badge</span>
            <span class="text-brand-500 text-sm font-medium">Brand link</span>
        </div>
    </div>
</div>