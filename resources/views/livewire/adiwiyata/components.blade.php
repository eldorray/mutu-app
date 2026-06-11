<div>
    @php
        $methodLabels = ['checklist' => 'Checklist', 'count' => 'Jumlah', 'percentage' => 'Persentase'];
        $methodStyles = [
            'checklist' => 'bg-blue-50 text-blue-700',
            'count' => 'bg-violet-50 text-violet-700',
            'percentage' => 'bg-amber-50 text-amber-700',
        ];
    @endphp

    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('adiwiyata.index') }}" wire:navigate
            class="inline-flex items-center gap-1.5 text-sm text-ink-500 hover:text-ink-800 mb-3">
            <x-admin.icon name="arrow-down" class="w-4 h-4 rotate-90" />
            Kembali ke daftar siklus
        </a>
        <h2 class="text-2xl font-bold text-ink-900">Kelola Komponen Adiwiyata</h2>
        <p class="text-ink-500 text-sm">
            Buat komponen lalu kelompokkan indikator ke dalamnya.
            @if ($instrument)
                Instrumen: <span class="font-medium text-ink-700">{{ $instrument->name }}</span>
            @endif
        </p>
    </div>

    @if (session('success'))
        <div class="mb-5 rounded-xl bg-emerald-50 border border-emerald-100 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if (!$instrument)
        <div class="bg-amber-50 border border-amber-100 rounded-2xl p-6 text-sm text-amber-700">
            Instrumen Adiwiyata aktif belum tersedia. Jalankan seeder terlebih dahulu.
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-[360px_1fr] gap-6">
            {{-- Components panel --}}
            <div class="bg-white rounded-2xl shadow-soft p-5 h-fit">
                <h3 class="text-sm font-bold text-ink-900 mb-3">Komponen</h3>

                {{-- Add form --}}
                <div class="flex items-start gap-2 mb-4">
                    <div class="flex-1">
                        <input type="text" wire:model="componentName" wire:keydown.enter="addComponent"
                            class="chip-input" placeholder="Nama komponen, mis. Kebijakan">
                        @error('componentName')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <button wire:click="addComponent" type="button" class="btn-primary shrink-0">
                        <x-admin.icon name="plus" class="w-4 h-4" />
                    </button>
                </div>

                {{-- Component list --}}
                @if ($components->isEmpty())
                    <p class="text-xs text-ink-400">Belum ada komponen. Tambahkan di atas.</p>
                @else
                    <div class="space-y-2">
                        @foreach ($components as $component)
                            <div class="rounded-xl border border-ink-100 px-3 py-2.5">
                                @if ($editingComponentId === $component->id)
                                    <div class="flex items-center gap-2">
                                        <input type="text" wire:model="editName"
                                            wire:keydown.enter="updateComponent" class="chip-input text-sm">
                                        <button wire:click="updateComponent" type="button"
                                            class="text-emerald-600 hover:text-emerald-800" title="Simpan">
                                            <x-admin.icon name="check-circle" class="w-5 h-5" />
                                        </button>
                                        <button wire:click="cancelEdit" type="button"
                                            class="text-ink-400 hover:text-ink-700" title="Batal">
                                            <x-admin.icon name="close" class="w-5 h-5" />
                                        </button>
                                    </div>
                                    @error('editName')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                @else
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-ink-800 truncate">
                                                {{ $component->number }}. {{ $component->name }}
                                            </p>
                                            <p class="text-xs text-ink-400">{{ $component->indicators_count }}
                                                indikator</p>
                                        </div>
                                        <div class="flex items-center gap-1 shrink-0">
                                            <button wire:click="editComponent({{ $component->id }})" type="button"
                                                class="text-ink-400 hover:text-ink-700" title="Edit">
                                                <x-admin.icon name="settings" class="w-4 h-4" />
                                            </button>
                                            <button
                                                @click="$dispatch('open-delete-modal', { id: 'delete-adiwiyata-component', action: 'deleteComponent({{ $component->id }})', title: 'Hapus Komponen', message: 'Hapus komponen {{ $component->name }}? Indikator di dalamnya akan dikembalikan ke Tanpa Komponen.' })"
                                                type="button" class="text-red-500 hover:text-red-700" title="Hapus">
                                                <x-admin.icon name="close" class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Indicators + bulk assign --}}
            <div class="bg-white rounded-2xl shadow-soft p-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                    <h3 class="text-sm font-bold text-ink-900">Indikator ({{ $indicators->count() }})</h3>

                    {{-- Bulk toolbar --}}
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs text-ink-500">{{ count($selectedIndicators) }} dipilih</span>
                        <select wire:model="targetComponentId" class="chip-input text-sm py-1.5 w-auto">
                            <option value="">— Tanpa Komponen —</option>
                            @foreach ($components as $component)
                                <option value="{{ $component->id }}">{{ $component->name }}</option>
                            @endforeach
                        </select>
                        <button wire:click="bulkAssign" type="button" class="btn-primary text-xs px-3 py-2"
                            @disabled(count($selectedIndicators) === 0)>
                            <x-admin.icon name="check-circle" class="w-3.5 h-3.5" />
                            Masukkan
                        </button>
                    </div>
                </div>
                @error('selectedIndicators')
                    <p class="text-red-500 text-xs mb-3">{{ $message }}</p>
                @enderror

                {{-- Select all --}}
                <label class="flex items-center gap-2 text-xs text-ink-600 mb-2 px-2">
                    <input type="checkbox" wire:model.live="selectAll"
                        class="rounded text-brand-500 focus:ring-brand-500">
                    Pilih semua
                </label>

                <div class="divide-y divide-ink-50">
                    @foreach ($indicators as $indicator)
                        <label class="flex items-start gap-3 py-2.5 px-2 rounded-lg hover:bg-ink-50 cursor-pointer">
                            <input type="checkbox" wire:model.live="selectedIndicators"
                                value="{{ $indicator->id }}" class="mt-1 rounded text-brand-500 focus:ring-brand-500">
                            <span
                                class="shrink-0 w-6 h-6 rounded-full bg-ink-100 text-ink-500 flex items-center justify-center text-xs font-bold mt-0.5">
                                {{ $indicator->number }}
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm text-ink-700 leading-snug">{{ $indicator->title }}</span>
                                <span class="flex items-center gap-2 mt-1">
                                    <span
                                        class="inline-flex items-center text-[10px] font-medium px-1.5 py-0.5 rounded {{ $methodStyles[$indicator->scoring_method] ?? 'bg-ink-100 text-ink-500' }}">
                                        {{ $methodLabels[$indicator->scoring_method] ?? $indicator->scoring_method }}
                                    </span>
                                    @if ($indicator->component)
                                        <span class="inline-flex items-center gap-1 text-[10px] text-ink-500">
                                            <x-admin.icon name="leaf" class="w-3 h-3" />
                                            {{ $indicator->component->name }}
                                        </span>
                                    @else
                                        <span class="text-[10px] text-ink-300">Tanpa Komponen</span>
                                    @endif
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <x-admin.delete-modal id="delete-adiwiyata-component" />
</div>
