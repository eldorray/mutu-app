@php
    $items = [
        [
            'key' => 'light',
            'label' => 'Light',
            'desc' => 'Always use the light theme.',
            'icon' => '<circle cx="12" cy="12" r="4" /><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>',
        ],
        [
            'key' => 'dark',
            'label' => 'Dark',
            'desc' => 'Always use the dark theme.',
            'icon' => '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />',
        ],
        [
            'key' => 'system',
            'label' => 'System',
            'desc' => 'Match your device setting.',
            'icon' => '<rect x="2" y="3" width="20" height="14" rx="2" ry="2" /><line x1="8" x2="16" y1="21" y2="21" /><line x1="12" x2="12" y1="17" y2="21" />',
        ],
    ];
@endphp

<div class="space-y-6">
    <header>
        <h3 class="text-lg font-semibold text-ink-900">Appearance</h3>
        <p class="text-sm text-ink-500">Choose how the interface looks to you.</p>
    </header>

    @if (session('appearance_status'))
        <div class="rounded-2xl bg-emerald-50 border border-emerald-100 px-4 py-3 text-sm text-emerald-700">
            {{ session('appearance_status') }}
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @foreach ($items as $item)
            @php $active = $appearance === $item['key']; @endphp
            <button type="button" wire:click="setAppearance('{{ $item['key'] }}')"
                class="text-left rounded-2xl border p-5 transition-all
                       {{ $active ? 'border-brand-500 ring-2 ring-brand-500/20 bg-brand-50/40' : 'border-ink-200 hover:border-ink-300 bg-white' }}">
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="w-10 h-10 rounded-xl flex items-center justify-center
                               {{ $active ? 'bg-brand-500 text-white' : 'bg-ink-100 text-ink-700' }}">
                        <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="2" viewBox="0 0 24 24" class="w-5 h-5">
                            {!! $item['icon'] !!}
                        </svg>
                    </div>

                    @if ($active)
                        <span
                            class="text-xs font-medium text-brand-600 bg-brand-100 px-2 py-0.5 rounded-full">Active</span>
                    @endif
                </div>
                <p class="font-semibold text-ink-900">{{ $item['label'] }}</p>
                <p class="text-xs text-ink-500 mt-1">{{ $item['desc'] }}</p>
            </button>
        @endforeach
    </div>

    <p class="text-xs text-ink-500">
        Your selection is applied instantly and synced across devices when signed in.
    </p>
</div>