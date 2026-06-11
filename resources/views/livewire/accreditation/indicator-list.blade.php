<div>
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 mb-1">
        <a href="{{ route('accreditation.index') }}" wire:navigate
            class="text-ink-400 hover:text-ink-600 transition-colors">
            <x-admin.icon name="clipboard" class="w-4 h-4" />
        </a>
        <span class="text-ink-300">/</span>
        <span class="text-sm text-ink-500">Daftar Indikator</span>
    </div>

    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-ink-900">Daftar Indikator</h2>
            <p class="text-ink-500 text-sm">{{ $cycle->school->name }} &mdash; {{ $cycle->instrument->name }}</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-sm text-ink-500 font-medium">{{ $indicators->count() }} indikator</span>
            <button wire:click="createIndicator" type="button" class="btn-primary">
                + Tambah Indikator
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-2xl bg-emerald-50 border border-emerald-100 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-6">
        <div class="flex-1">
            <input type="text" wire:model.live.debounce.300ms="search" class="chip-input"
                placeholder="Cari kode atau judul indikator...">
        </div>
        <select wire:model.live="filterComponent" class="chip-input sm:w-48">
            <option value="">Semua Komponen</option>
            @foreach ($components as $comp)
                <option value="{{ $comp->number }}">Komponen {{ $comp->number }}</option>
            @endforeach
        </select>
    </div>

    {{-- Edit Form --}}
    @if ($showForm)
        <div class="bg-white rounded-2xl shadow-soft p-6 mb-6 border-2 border-brand-200">
            <h3 class="text-lg font-bold text-ink-900 mb-4">
                {{ $editingId ? 'Edit Indikator: ' . $code : 'Tambah Indikator' }}
            </h3>

            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1">Kode</label>
                        <input type="text" wire:model="code" class="chip-input" placeholder="1.1.1">
                        @error('code')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="flex items-center gap-2 mt-6">
                            <input type="checkbox" wire:model="isNaAllowed"
                                class="rounded border-ink-300 text-brand-500 focus:ring-brand-500">
                            <span class="text-sm text-ink-700">Boleh N/A</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-ink-700 mb-1">Judul Indikator</label>
                    <input type="text" wire:model="title" class="chip-input">
                    @error('title')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-ink-700 mb-1">Definisi</label>
                    <textarea wire:model="definition" class="chip-input min-h-[80px]" rows="3"></textarea>
                </div>

                <div class="border-t border-ink-100 pt-4">
                    <h4 class="text-sm font-bold text-ink-900 mb-3">Rubrik Penilaian</h4>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-red-600 mb-1">Kurang (Skor 1)</label>
                            <textarea wire:model="rubricKurang" class="chip-input text-sm min-h-[60px]" rows="2"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-amber-600 mb-1">Cukup Baik (Skor 2)</label>
                            <textarea wire:model="rubricCukupBaik" class="chip-input text-sm min-h-[60px]" rows="2"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-blue-600 mb-1">Baik (Skor 3)</label>
                            <textarea wire:model="rubricBaik" class="chip-input text-sm min-h-[60px]" rows="2"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-emerald-600 mb-1">Sangat Baik (Skor 4)</label>
                            <textarea wire:model="rubricSangatBaik" class="chip-input text-sm min-h-[60px]" rows="2"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button wire:click="{{ $editingId ? 'updateIndicator' : 'storeIndicator' }}" type="button" class="btn-primary">
                        {{ $editingId ? 'Simpan Perubahan' : 'Tambah Indikator' }}
                    </button>
                    <button wire:click="cancelForm" type="button" class="btn-ghost">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Indicator Table --}}
    @if ($indicators->isEmpty())
        <div class="bg-ink-100 rounded-2xl p-12 text-center">
            <x-admin.icon name="file" class="w-12 h-12 text-ink-200 mx-auto mb-4" />
            <p class="text-ink-600 font-medium">Belum ada indikator.</p>
            <p class="text-ink-400 text-sm mt-1">Import instrumen terlebih dahulu dari menu Akreditasi.</p>
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-soft overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left min-w-[800px]">
                    <thead>
                        <tr class="text-xs text-ink-400 border-b border-ink-100 bg-ink-50/50">
                            <th class="font-medium py-3 px-4 w-20">Kode</th>
                            <th class="font-medium py-3 px-4">Indikator</th>
                            <th class="font-medium py-3 px-4 w-24">Komponen</th>
                            <th class="font-medium py-3 px-4 w-20">Rubrik</th>
                            <th class="font-medium py-3 px-4 w-16">N/A</th>
                            <th class="font-medium py-3 px-4 w-28 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @foreach ($indicators as $indicator)
                            <tr class="border-b border-ink-50 last:border-0 hover:bg-ink-50/40 transition-colors">
                                <td class="py-3 px-4">
                                    <span class="text-xs font-bold text-brand-600">{{ $indicator->code }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <p class="text-ink-800 font-medium line-clamp-2">{{ $indicator->title }}</p>
                                    @if ($indicator->definition)
                                        <p class="text-xs text-ink-400 line-clamp-1 mt-0.5">
                                            {{ $indicator->definition }}</p>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-xs text-ink-500">K{{ $indicator->item->component->number }} /
                                        B{{ $indicator->item->number }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-xs text-ink-500">{{ $indicator->rubrics->count() }}/4</span>
                                </td>
                                <td class="py-3 px-4">
                                    @if ($indicator->is_na_allowed)
                                        <span
                                            class="text-xs bg-amber-50 text-amber-700 px-1.5 py-0.5 rounded">Ya</span>
                                    @else
                                        <span class="text-xs text-ink-300">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button wire:click="editIndicator({{ $indicator->id }})" type="button"
                                            class="w-7 h-7 rounded-lg flex items-center justify-center text-ink-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                            title="Edit">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                            </svg>
                                        </button>
                                        <button
                                            @click="$dispatch('open-delete-modal', { id: 'delete-indicator-list', action: 'deleteIndicator({{ $indicator->id }})', title: 'Hapus Indikator', message: 'Hapus indikator {{ $indicator->code }}? Rubrik dan data terkait juga akan dihapus.' })"
                                            type="button"
                                            class="w-7 h-7 rounded-lg flex items-center justify-center text-ink-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                            title="Hapus">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <polyline points="3 6 5 6 21 6" />
                                                <path
                                                    d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <x-admin.delete-modal id="delete-indicator-list" />
</div>
