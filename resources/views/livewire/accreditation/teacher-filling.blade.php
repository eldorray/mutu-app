<div>
    {{-- Page header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('accreditation.index') }}" wire:navigate
                    class="text-ink-400 hover:text-ink-600 transition-colors">
                    <x-admin.icon name="home" class="w-4 h-4" />
                </a>
                <span class="text-ink-300">/</span>
                <span class="text-sm text-ink-500">Pengisian</span>
            </div>
            <h2 class="text-2xl font-bold text-ink-900">Pengisian Indikator</h2>
            <p class="text-ink-500 text-sm">{{ $cycle->school->name }} &mdash; {{ $cycle->year }}</p>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-2xl bg-emerald-50 border border-emerald-100 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- LEFT: Indicator List --}}
        <aside class="lg:col-span-4 xl:col-span-3">
            <div class="bg-white rounded-2xl shadow-soft p-4 max-h-[calc(100vh-220px)] overflow-y-auto no-scrollbar">
                <div class="flex items-center justify-between mb-3 px-1">
                    <h3 class="text-sm font-bold text-ink-900">Daftar Indikator</h3>
                    <div class="flex items-center gap-2">
                        @if (count($selectedIndicators) > 0)
                            <button
                                @click="$dispatch('open-delete-modal', { id: 'delete-indicator', action: 'bulkDelete', title: 'Hapus Indikator', message: 'Hapus {{ count($selectedIndicators) }} indikator yang dipilih? Rubrik dan data terkait juga akan dihapus.' })"
                                type="button"
                                class="text-[10px] font-medium text-red-600 bg-red-50 hover:bg-red-100 px-2 py-1 rounded-lg transition-colors">
                                Hapus ({{ count($selectedIndicators) }})
                            </button>
                        @endif
                    </div>
                </div>
                {{-- Select All --}}
                <label class="flex items-center gap-2 px-3 py-2 mb-1 rounded-lg hover:bg-ink-50 cursor-pointer">
                    <input type="checkbox" wire:model.live="selectAll"
                        class="w-3.5 h-3.5 rounded border-ink-300 text-brand-500 focus:ring-brand-500">
                    <span class="text-xs text-ink-500 font-medium">Pilih Semua</span>
                </label>
                <div class="space-y-1">
                    @foreach ($indicators as $indicator)
                        @php
                            $isSelected = $indicator->id === $selectedIndicatorId;
                            $score = $cycle->scores->firstWhere('indicator_id', $indicator->id);
                            $status = $score?->checklist_status ?? 'belum_diisi';

                            [$statusLabel, $statusBadge] = match ($status) {
                                'lengkap' => ['Lengkap', 'bg-emerald-50 text-emerald-700'],
                                'tidak_lengkap' => ['Tidak Lengkap', 'bg-red-50 text-red-600'],
                                'perlu_revisi' => ['Perlu Revisi', 'bg-amber-50 text-amber-700'],
                                'na' => ['N/A', 'bg-ink-100 text-ink-500'],
                                default => ['Belum Diisi', 'bg-ink-100 text-ink-400'],
                            };

                            $scoreValue = $score?->score_value;
                        @endphp
                        <div class="flex items-start gap-2">
                            <input type="checkbox" wire:model.live="selectedIndicators" value="{{ $indicator->id }}"
                                class="w-3.5 h-3.5 rounded border-ink-300 text-brand-500 focus:ring-brand-500 mt-1 shrink-0"
                                @click.stop>
                            <div wire:click="selectIndicator({{ $indicator->id }})"
                                class="flex-1 min-w-0 text-left rounded-lg p-2 transition-colors cursor-pointer group relative {{ $isSelected ? 'bg-brand-50' : 'hover:bg-ink-50' }}">
                                <div class="flex items-center gap-2 mb-0.5">
                                    <span class="text-xs font-bold text-brand-600">{{ $indicator->code }}</span>
                                    @if ($scoreValue)
                                        <span
                                            class="text-xs font-bold text-ink-900 bg-ink-100 px-1.5 py-0.5 rounded">{{ $scoreValue }}/4</span>
                                    @endif
                                    <span
                                        class="text-[10px] font-medium px-1.5 py-0.5 rounded {{ $statusBadge }}">{{ $statusLabel }}</span>
                                </div>
                                <p class="text-sm text-ink-700 line-clamp-2">{{ $indicator->title }}</p>
                                {{-- Edit/Delete on hover --}}
                                <div
                                    class="absolute top-2 right-2 hidden group-hover:flex items-center gap-0.5 bg-white rounded-lg shadow-soft p-0.5">
                                    <button wire:click.stop="editIndicator({{ $indicator->id }})" type="button"
                                        class="w-6 h-6 rounded flex items-center justify-center text-ink-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                        title="Edit">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                        </svg>
                                    </button>
                                    <button
                                        @click.stop="$dispatch('open-delete-modal', { id: 'delete-indicator', action: 'deleteIndicator({{ $indicator->id }})', title: 'Hapus Indikator', message: 'Hapus indikator {{ $indicator->code }}? Rubrik dan data terkait juga akan dihapus.' })"
                                        type="button"
                                        class="w-6 h-6 rounded flex items-center justify-center text-ink-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                        title="Hapus">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <polyline points="3 6 5 6 21 6" />
                                            <path
                                                d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </aside>

        {{-- RIGHT: Form --}}
        <section class="lg:col-span-8 xl:col-span-9">
            @if ($selectedIndicatorId && $currentIndicator)
                <div class="space-y-6">
                    {{-- Edit Indicator Form --}}
                    @if ($showEditIndicator)
                        <div class="bg-white rounded-2xl shadow-soft p-6 border-2 border-brand-200">
                            <h4 class="text-sm font-bold text-ink-900 mb-4">Edit Indikator: {{ $editIndCode }}</h4>
                            <div class="space-y-3">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-ink-700 mb-1">Kode</label>
                                        <input type="text" wire:model="editIndCode" class="chip-input text-sm">
                                        @error('editIndCode')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <label class="flex items-center gap-2 mt-5">
                                        <input type="checkbox" wire:model="editIndNa"
                                            class="rounded border-ink-300 text-brand-500 focus:ring-brand-500">
                                        <span class="text-sm text-ink-700">Boleh N/A</span>
                                    </label>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-ink-700 mb-1">Judul</label>
                                    <input type="text" wire:model="editIndTitle" class="chip-input text-sm">
                                    @error('editIndTitle')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-ink-700 mb-1">Definisi</label>
                                    <textarea wire:model="editIndDefinition" class="chip-input text-sm min-h-[60px]" rows="2"></textarea>
                                </div>
                                <div class="border-t border-ink-100 pt-3">
                                    <p class="text-xs font-bold text-ink-700 mb-2">Rubrik</p>
                                    <div class="space-y-2">
                                        <div>
                                            <label class="block text-[10px] font-medium text-red-600 mb-0.5">Kurang
                                                (1)</label>
                                            <textarea wire:model="editRubricKurang" class="chip-input text-xs min-h-[50px]" rows="2"></textarea>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-medium text-amber-600 mb-0.5">Cukup
                                                Baik (2)</label>
                                            <textarea wire:model="editRubricCukupBaik" class="chip-input text-xs min-h-[50px]" rows="2"></textarea>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-medium text-blue-600 mb-0.5">Baik
                                                (3)</label>
                                            <textarea wire:model="editRubricBaik" class="chip-input text-xs min-h-[50px]" rows="2"></textarea>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-medium text-emerald-600 mb-0.5">Sangat
                                                Baik (4)</label>
                                            <textarea wire:model="editRubricSangatBaik" class="chip-input text-xs min-h-[50px]" rows="2"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 pt-2">
                                    <button wire:click="updateIndicator" type="button"
                                        class="btn-primary text-xs px-4 py-2">Simpan</button>
                                    <button wire:click="cancelEditIndicator" type="button"
                                        class="btn-ghost text-xs px-4 py-2">Batal</button>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Indicator Info --}}
                    <div class="bg-white rounded-2xl shadow-soft p-6">
                        <div class="flex items-start justify-between gap-4 mb-4">
                            <div>
                                <span
                                    class="inline-flex items-center gap-1 text-xs font-bold text-brand-600 bg-brand-50 px-2 py-1 rounded-lg">
                                    {{ $currentIndicator->code }}
                                </span>
                                <span class="text-xs text-ink-400 ml-2">Komponen
                                    {{ $currentIndicator->item->component->number }} &bull; Butir
                                    {{ $currentIndicator->item->number }}</span>
                            </div>
                            @if ($currentIndicator->is_na_allowed)
                                <span class="text-xs bg-amber-50 text-amber-700 px-2 py-1 rounded-lg font-medium">N/A
                                    diperbolehkan</span>
                            @endif
                        </div>
                        <h3 class="text-lg font-bold text-ink-900 mb-2">{{ $currentIndicator->title }}</h3>
                        @if ($currentIndicator->definition)
                            <p class="text-sm text-ink-600 leading-relaxed">{{ $currentIndicator->definition }}</p>
                        @endif
                    </div>

                    {{-- Score Form --}}
                    <div class="bg-white rounded-2xl shadow-soft p-6">
                        <h4 class="text-sm font-bold text-ink-900 mb-4">Penilaian</h4>

                        @if ($currentIndicator->is_na_allowed)
                            <label class="flex items-center gap-3 mb-4 p-3 rounded-xl bg-ink-50 cursor-pointer">
                                <input type="checkbox" wire:model.live="isNa"
                                    class="w-4 h-4 rounded border-ink-300 text-brand-500 focus:ring-brand-500">
                                <span class="text-sm text-ink-700">Tandai sebagai N/A (Tidak Berlaku)</span>
                            </label>
                            @error('isNa')
                                <p class="text-red-500 text-xs mb-3">{{ $message }}</p>
                            @enderror
                        @endif

                        @if (!$isNa)
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-ink-700 mb-2">Status Kelengkapan</label>
                                <select wire:model="checklistStatus" class="chip-input">
                                    <option value="belum_diisi">Belum Diisi</option>
                                    <option value="lengkap">Lengkap</option>
                                    <option value="tidak_lengkap">Tidak Lengkap</option>
                                    <option value="perlu_revisi">Perlu Revisi</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-ink-700 mb-2">Pilih Rubrik / Nilai</label>
                                <div class="space-y-2">
                                    @foreach ($currentIndicator->rubrics as $rubric)
                                        <label
                                            class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition-colors {{ $selectedRubricId == $rubric->id ? 'border-brand-300 bg-brand-50/50' : 'border-ink-200 hover:border-ink-300 hover:bg-ink-50' }}">
                                            <input type="radio" wire:model="selectedRubricId"
                                                value="{{ $rubric->id }}"
                                                class="mt-0.5 w-4 h-4 border-ink-300 text-brand-500 focus:ring-brand-500">
                                            <div class="min-w-0">
                                                <span class="text-sm font-medium text-ink-900">
                                                    {{ $rubric->level->label }}
                                                    @if ($rubric->context)
                                                        <span
                                                            class="text-xs text-ink-400">({{ $rubric->context }})</span>
                                                    @endif
                                                </span>
                                                <p class="text-xs text-ink-600 mt-1 whitespace-pre-line">
                                                    {{ $rubric->description }}</p>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-ink-700 mb-2">Keterangan Guru</label>
                            <textarea wire:model="teacherNote" class="chip-input min-h-[100px]" rows="4"
                                placeholder="Tuliskan keterangan atau catatan..."></textarea>
                        </div>

                        <button wire:click="saveScore" type="button" class="btn-primary">
                            Simpan Nilai
                        </button>
                    </div>

                    {{-- Evidence Upload --}}
                    <div class="bg-white rounded-2xl shadow-soft p-6">
                        <h4 class="text-sm font-bold text-ink-900 mb-4">Upload Bukti</h4>

                        @if (session('evidence-success'))
                            <div
                                class="mb-4 rounded-xl bg-emerald-50 border border-emerald-100 px-4 py-3 text-sm text-emerald-700">
                                {{ session('evidence-success') }}
                            </div>
                        @endif

                        {{-- Evidence Suggestions --}}
                        @if ($currentIndicator->evidenceSuggestions->isNotEmpty())
                            <div class="mb-4 p-3 rounded-xl bg-blue-50 border border-blue-100">
                                <p class="text-xs font-medium text-blue-700 mb-1">Saran bukti:</p>
                                <ul class="text-xs text-blue-600 space-y-0.5">
                                    @foreach ($currentIndicator->evidenceSuggestions as $suggestion)
                                        <li>&bull; {{ $suggestion->name }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Existing Evidences --}}
                        @if ($existingEvidences->isNotEmpty())
                            <div class="mb-4">
                                <p class="text-xs font-medium text-ink-500 mb-2">Bukti yang sudah diunggah:</p>
                                <div class="space-y-2">
                                    @foreach ($existingEvidences as $link)
                                        @php
                                            $evidence = $link->evidence;
                                            $hasFile = $evidence->file_path;
                                            $hasUrl = $evidence->external_url;
                                            $fileUrl = $hasFile
                                                ? \Illuminate\Support\Facades\Storage::disk('public')->url(
                                                    $evidence->file_path,
                                                )
                                                : null;
                                            $ext = $hasFile
                                                ? strtolower(pathinfo($evidence->file_path, PATHINFO_EXTENSION))
                                                : '';
                                            $isOffice = in_array($ext, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']);
                                            // Use Google Docs viewer for office files
                                            $viewUrl = $isOffice
                                                ? 'https://docs.google.com/gview?url=' .
                                                    urlencode(url($fileUrl)) .
                                                    '&embedded=false'
                                                : $fileUrl;
                                            $href = $hasUrl ? $evidence->external_url : $viewUrl;
                                        @endphp
                                        <div class="flex items-center gap-2 p-3 rounded-xl bg-ink-50">
                                            <a href="{{ $href }}" target="_blank" rel="noopener noreferrer"
                                                class="flex items-center gap-3 flex-1 min-w-0 hover:opacity-80 transition-opacity">
                                                @if ($hasUrl)
                                                    <x-admin.icon name="link"
                                                        class="w-4 h-4 text-blue-500 shrink-0" />
                                                @else
                                                    <x-admin.icon name="file"
                                                        class="w-4 h-4 text-brand-500 shrink-0" />
                                                @endif
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-sm text-ink-700 truncate">{{ $evidence->title }}
                                                    </p>
                                                    <p class="text-xs text-ink-400">
                                                        {{ $evidence->evidenceType->name }}
                                                        @if ($hasFile)
                                                            &bull; <span class="uppercase">{{ $ext }}</span>
                                                        @endif
                                                        @if ($hasUrl)
                                                            &bull; Tautan eksternal
                                                        @endif
                                                    </p>
                                                </div>
                                                <svg class="w-4 h-4 text-ink-300 shrink-0" fill="none"
                                                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14L21 3" />
                                                </svg>
                                            </a>
                                            {{-- Edit & Delete buttons --}}
                                            <div class="flex items-center gap-1 shrink-0">
                                                <button wire:click="editEvidence({{ $evidence->id }})" type="button"
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
                                                    @click="$dispatch('open-delete-modal', { id: 'delete-evidence', action: 'deleteEvidence({{ $evidence->id }})', title: 'Hapus Bukti', message: 'Yakin ingin menghapus bukti ini? File juga akan dihapus.' })"
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
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="space-y-3">
                            @if ($editingEvidenceId)
                                <div class="p-3 rounded-xl bg-blue-50 border border-blue-100 mb-2">
                                    <p class="text-xs font-medium text-blue-700">Mengedit bukti — ubah data lalu klik
                                        "Simpan Perubahan"</p>
                                </div>
                            @endif
                            <div>
                                <label class="block text-sm font-medium text-ink-700 mb-1">Judul Bukti</label>
                                <input type="text" wire:model="evidenceTitle" class="chip-input"
                                    placeholder="Contoh: RPP Semester 1">
                                @error('evidenceTitle')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-ink-700 mb-1">Jenis Bukti</label>
                                <select wire:model="evidenceTypeId" class="chip-input">
                                    <option value="">Pilih jenis...</option>
                                    @foreach ($evidenceTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                @error('evidenceTypeId')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-ink-700 mb-1">File (opsional, maks
                                    10MB)</label>
                                <input type="file" wire:model="file" class="chip-input text-sm">
                                @error('file')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-ink-700 mb-1">Atau Tautan
                                    Eksternal</label>
                                <input type="url" wire:model="externalUrl" class="chip-input"
                                    placeholder="https://drive.google.com/...">
                                @error('externalUrl')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            @if ($editingEvidenceId)
                                <div class="flex items-center gap-2">
                                    <button wire:click="updateEvidence" type="button"
                                        class="btn-primary text-xs px-4 py-2">
                                        Simpan Perubahan
                                    </button>
                                    <button wire:click="cancelEditEvidence" type="button"
                                        class="btn-ghost text-xs px-4 py-2">
                                        Batal
                                    </button>
                                </div>
                            @else
                                <button wire:click="uploadEvidence" type="button" class="btn-ghost">
                                    <x-admin.icon name="plus" class="w-4 h-4" />
                                    Upload Bukti
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-2xl shadow-soft p-12 text-center">
                    <x-admin.icon name="file" class="w-16 h-16 text-ink-200 mx-auto mb-4" />
                    <p class="text-ink-600 font-medium">Pilih indikator terlebih dahulu</p>
                    <p class="text-ink-400 text-sm mt-1">Klik salah satu indikator di panel kiri untuk mulai mengisi.
                    </p>
                </div>
            @endif
        </section>
    </div>

    <x-admin.delete-modal id="delete-indicator" />
    <x-admin.delete-modal id="delete-evidence" />
</div>
