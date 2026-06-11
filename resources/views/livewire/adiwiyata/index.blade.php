<div>
    @php
        $statusStyles = [
            'draft' => 'bg-ink-100 text-ink-500',
            'berjalan' => 'bg-amber-50 text-amber-700',
            'selesai' => 'bg-emerald-50 text-emerald-700',
        ];
        $awardStyles = [
            'calon' => 'bg-ink-50 text-ink-600 border-ink-200',
            'kabupaten' => 'bg-blue-50 text-blue-700 border-blue-200',
            'provinsi' => 'bg-violet-50 text-violet-700 border-violet-200',
            'nasional' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'mandiri' => 'bg-amber-50 text-amber-700 border-amber-200',
        ];
    @endphp

    {{-- Page header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h2 class="text-3xl font-bold text-ink-900 mb-1">Adiwiyata</h2>
            <p class="text-ink-500 text-sm">Kelola siklus Adiwiyata madrasah/sekolah.</p>
        </div>
        @if (!$showForm)
            <div class="flex items-center gap-2">
                <a href="{{ route('adiwiyata.components') }}" wire:navigate class="btn-ghost">
                    <x-admin.icon name="leaf" class="w-4 h-4" />
                    Kelola Komponen
                </a>
                <button wire:click="create" type="button" class="btn-primary">
                    <x-admin.icon name="plus" class="w-4 h-4" />
                    Tambah Siklus
                </button>
            </div>
        @endif
    </div>

    {{-- Form Create/Edit --}}
    @if ($showForm)
        <div class="bg-white rounded-2xl shadow-soft p-6 mb-6">
            <h3 class="text-lg font-bold text-ink-900 mb-4">
                {{ $editingCycleId ? 'Edit Siklus Adiwiyata' : 'Tambah Siklus Adiwiyata Baru' }}
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-ink-700 mb-1">Nama Sekolah/Madrasah</label>
                    <input type="text" wire:model="schoolName" class="chip-input"
                        placeholder="Contoh: MTs Negeri 1 Kota Malang">
                    @error('schoolName')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-700 mb-1">Tahun</label>
                    <input type="number" wire:model="year" class="chip-input" min="2020" max="2099"
                        placeholder="2025">
                    @error('year')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-700 mb-1">Status</label>
                    <select wire:model="status" class="chip-input">
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-ink-700 mb-1">Level Penghargaan (opsional)</label>
                    <select wire:model="awardLevel" class="chip-input">
                        <option value="">— Belum ada —</option>
                        @foreach ($awardLevels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('awardLevel')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button wire:click="save" type="button" class="btn-primary">
                    {{ $editingCycleId ? 'Simpan Perubahan' : 'Simpan' }}
                </button>
                <button wire:click="cancelForm" type="button" class="btn-ghost">
                    Batal
                </button>
            </div>
        </div>
    @endif

    {{-- Cycles List --}}
    @if ($cycles->isEmpty() && !$showForm)
        <div class="bg-ink-100 rounded-2xl p-12 text-center">
            <x-admin.icon name="leaf" class="w-16 h-16 text-ink-200 mx-auto mb-4" />
            <p class="text-ink-600 font-medium">Belum ada siklus Adiwiyata.</p>
            <p class="text-ink-400 text-sm mt-1">Klik "Tambah Siklus" untuk memulai.</p>
        </div>
    @elseif ($cycles->isNotEmpty())
        <div class="space-y-4">
            @foreach ($cycles as $cycle)
                <div class="bg-white rounded-2xl shadow-soft p-5">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 mb-1 flex-wrap">
                                <h4 class="text-base font-bold text-ink-900 truncate">{{ $cycle->school->name }}</h4>

                                <span
                                    class="inline-flex items-center text-xs font-medium px-2 py-0.5 rounded-full {{ $statusStyles[$cycle->status] ?? 'bg-ink-100 text-ink-500' }}">
                                    {{ $statuses[$cycle->status] ?? $cycle->status }}
                                </span>

                                @if ($cycle->award_level)
                                    <span
                                        class="inline-flex items-center text-xs font-bold px-2.5 py-0.5 rounded-full border {{ $awardStyles[$cycle->award_level] ?? 'bg-ink-50 text-ink-600 border-ink-200' }}">
                                        {{ $awardLevels[$cycle->award_level] ?? $cycle->award_level }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-sm text-ink-500">{{ $cycle->instrument->name }} &mdash; Tahun
                                {{ $cycle->year }}</p>

                            <div class="flex items-center gap-3 mt-3">
                                <div class="flex-1 h-2 bg-ink-100 rounded-full overflow-hidden max-w-[240px]">
                                    <div class="h-full rounded-full bg-brand-500 transition-all"
                                        style="width: {{ $cycle->progress_percent }}%"></div>
                                </div>
                                <span class="text-xs text-ink-500 font-medium">
                                    {{ $cycle->filled_count }}/{{ $cycle->total_indicators }} terisi
                                    ({{ $cycle->progress_percent }}%)
                                </span>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-2 shrink-0 flex-wrap">
                            <a href="{{ route('adiwiyata.filling', $cycle) }}" wire:navigate
                                class="btn-primary text-xs px-4 py-2">
                                <x-admin.icon name="file" class="w-3.5 h-3.5" />
                                Isi Indikator
                            </a>
                            <button wire:click="edit({{ $cycle->id }})" type="button"
                                class="btn-ghost text-xs px-3 py-2">
                                <x-admin.icon name="settings" class="w-3.5 h-3.5" />
                                Edit
                            </button>
                            <button
                                @click="$dispatch('open-delete-modal', { id: 'delete-adiwiyata-cycle', action: 'delete({{ $cycle->id }})', title: 'Hapus Siklus Adiwiyata', message: 'Yakin ingin menghapus siklus {{ $cycle->school->name }} ({{ $cycle->year }})?' })"
                                type="button"
                                class="inline-flex items-center justify-center gap-1.5 rounded-full border border-red-200 bg-white px-3 py-2 text-xs font-medium text-red-600 hover:bg-red-50 shadow-soft transition">
                                <x-admin.icon name="close" class="w-3.5 h-3.5" />
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <x-admin.delete-modal id="delete-adiwiyata-cycle" />
</div>
