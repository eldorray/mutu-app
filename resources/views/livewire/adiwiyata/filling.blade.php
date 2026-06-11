<div>
    @php
        $methodLabels = ['checklist' => 'Checklist Bukti', 'count' => 'Jumlah', 'percentage' => 'Persentase'];
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
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-ink-900">{{ $cycle->school->name }}</h2>
                <p class="text-ink-500 text-sm">Pengisian indikator Adiwiyata &mdash; Tahun {{ $cycle->year }}</p>
            </div>
            <div class="min-w-[220px]">
                <div class="flex items-center justify-between text-xs text-ink-500 mb-1">
                    <span>Kelengkapan</span>
                    <span class="font-medium">{{ $filledCount }}/{{ $totalCount }} ({{ $progressPercent }}%)</span>
                </div>
                <div class="h-2 bg-ink-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full bg-brand-500 transition-all" style="width: {{ $progressPercent }}%">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[320px_1fr] gap-6">
        {{-- Indicator list --}}
        <div class="bg-white rounded-2xl shadow-soft p-3 lg:max-h-[calc(100vh-180px)] lg:overflow-y-auto">
            @foreach ($groups as $group)
                @if ($group['name'])
                    <p class="text-[11px] font-bold uppercase tracking-wide text-ink-400 px-3 pt-3 pb-1">
                        {{ $group['name'] }}
                    </p>
                @endif
                <div class="space-y-1 {{ !$loop->last ? 'mb-2' : '' }}">
                    @foreach ($group['indicators'] as $indicator)
                        @php $ans = $answers->get($indicator->id); $isFilled = $ans && $ans->status === 'terisi'; @endphp
                        <button wire:click="selectIndicator({{ $indicator->id }})" type="button"
                            class="w-full text-left rounded-xl px-3 py-2.5 transition-colors flex items-start gap-2.5
                                   {{ $selectedIndicatorId === $indicator->id ? 'bg-brand-50 ring-1 ring-brand-200' : 'hover:bg-ink-50' }}">
                            <span
                                class="shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold
                                       {{ $isFilled ? 'bg-emerald-500 text-white' : 'bg-ink-100 text-ink-500' }}">
                                {{ $indicator->number }}
                            </span>
                            <span class="text-xs leading-snug text-ink-700 line-clamp-2">{{ $indicator->title }}</span>
                        </button>
                    @endforeach
                </div>
            @endforeach
        </div>

        {{-- Detail panel --}}
        <div>
            @if (!$currentIndicator)
                <div class="bg-ink-100 rounded-2xl p-12 text-center">
                    <x-admin.icon name="leaf" class="w-14 h-14 text-ink-200 mx-auto mb-3" />
                    <p class="text-ink-600 font-medium">Pilih indikator di sebelah kiri untuk mulai mengisi.</p>
                </div>
            @else
                @if (session('success'))
                    <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-100 px-4 py-3 text-sm text-emerald-700">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="bg-white rounded-2xl shadow-soft p-6 mb-6">
                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                        <span class="text-xs font-bold text-ink-400">Indikator {{ $currentIndicator->number }}</span>
                        <span class="inline-flex items-center text-xs font-medium px-2 py-0.5 rounded-full {{ $methodStyles[$currentIndicator->scoring_method] ?? 'bg-ink-100 text-ink-500' }}">
                            {{ $methodLabels[$currentIndicator->scoring_method] ?? $currentIndicator->scoring_method }}
                        </span>
                    </div>
                    <h3 class="text-lg font-bold text-ink-900 mb-2">{{ $currentIndicator->title }}</h3>
                    @if ($currentIndicator->description)
                        <p class="text-sm text-ink-500 mb-3">{{ $currentIndicator->description }}</p>
                    @endif

                    @if ($currentIndicator->scoring_guide)
                        <div class="rounded-xl bg-blue-50 border border-blue-100 p-3 mb-2">
                            <p class="text-xs font-bold text-blue-700 mb-1">Cara Menilai</p>
                            <p class="text-xs text-blue-700 leading-relaxed">{{ $currentIndicator->scoring_guide }}</p>
                        </div>
                    @endif
                </div>

                {{-- Answer form --}}
                <div class="bg-white rounded-2xl shadow-soft p-6 mb-6">
                    <h4 class="text-sm font-bold text-ink-900 mb-4">Pengisian</h4>

                    {{-- checklist --}}
                    @if ($currentIndicator->scoring_method === 'checklist')
                        <label class="block text-sm font-medium text-ink-700 mb-2">Centang bukti yang terpenuhi</label>
                        <div class="space-y-2 mb-4">
                            @forelse ($currentIndicator->evidences as $ev)
                                <label class="flex items-start gap-2.5 text-sm cursor-pointer rounded-lg p-2 hover:bg-ink-50">
                                    <input type="checkbox" wire:model="checkedEvidences" value="{{ $ev->id }}"
                                        class="mt-0.5 rounded text-brand-500 focus:ring-brand-500">
                                    <span class="text-ink-700 leading-snug">{{ $ev->name }}</span>
                                </label>
                            @empty
                                <p class="text-xs text-ink-400">Tidak ada daftar bukti untuk indikator ini.</p>
                            @endforelse
                        </div>

                    {{-- count --}}
                    @elseif ($currentIndicator->scoring_method === 'count')
                        <div class="mb-4 max-w-xs">
                            <label class="block text-sm font-medium text-ink-700 mb-1">Jumlah</label>
                            <input type="number" wire:model="valueNumber" min="0" class="chip-input" placeholder="0">
                            @error('valueNumber') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                    {{-- percentage --}}
                    @elseif ($currentIndicator->scoring_method === 'percentage')
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-2 max-w-md">
                            <div>
                                <label class="block text-sm font-medium text-ink-700 mb-1">Pembilang</label>
                                <input type="number" wire:model.live="valueNumerator" min="0" class="chip-input" placeholder="0">
                                @error('valueNumerator') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-ink-700 mb-1">Penyebut</label>
                                <input type="number" wire:model.live="valueDenominator" min="1" class="chip-input" placeholder="0">
                                @error('valueDenominator') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        @if (is_numeric($valueNumerator) && is_numeric($valueDenominator) && (int) $valueDenominator > 0)
                            <p class="text-sm text-ink-600 mb-4">Hasil:
                                <span class="font-bold text-ink-900">{{ round(((int) $valueNumerator / (int) $valueDenominator) * 100, 2) }}%</span>
                            </p>
                        @endif
                    @endif

                    {{-- note (wajib) --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-ink-700 mb-1">Catatan <span class="text-red-500">*</span></label>
                        <textarea wire:model="note" rows="3" class="chip-input"
                            placeholder="Wajib mengisi catatan sebelum menyimpan."></textarea>
                        @error('note') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <button wire:click="saveAnswer" type="button" class="btn-primary">
                        <x-admin.icon name="check-circle" class="w-4 h-4" />
                        Simpan
                    </button>
                </div>

                {{-- Evidence section --}}
                <div class="bg-white rounded-2xl shadow-soft p-6">
                    <h4 class="text-sm font-bold text-ink-900 mb-3">Bukti Dukung</h4>

                    @if (session('evidence-success'))
                        <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-100 px-4 py-3 text-sm text-emerald-700">
                            {{ session('evidence-success') }}
                        </div>
                    @endif

                    {{-- Upload form --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="block text-xs font-medium text-ink-600 mb-1">Jenis</label>
                            <select wire:model="evidenceType" class="chip-input">
                                @foreach ($evidenceTypes as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-ink-600 mb-1">Judul Bukti</label>
                            <input type="text" wire:model="evidenceTitle" class="chip-input" placeholder="Contoh: Dokumen KSP TA 2024/2025">
                            @error('evidenceTitle') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-ink-600 mb-1">File (opsional, maks 10MB)</label>
                            <input type="file" wire:model="file" class="chip-input text-sm">
                            @error('file') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-ink-600 mb-1">Tautan (opsional)</label>
                            <input type="url" wire:model="evidenceUrl" class="chip-input" placeholder="https://...">
                            @error('evidenceUrl') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <button wire:click="uploadEvidence" type="button" class="btn-ghost text-sm" wire:loading.attr="disabled" wire:target="uploadEvidence,file">
                        <x-admin.icon name="upload" class="w-4 h-4" />
                        <span wire:loading.remove wire:target="uploadEvidence">Unggah Bukti</span>
                        <span wire:loading wire:target="uploadEvidence">Mengunggah...</span>
                    </button>

                    {{-- Uploaded list --}}
                    @if ($uploadedEvidences->isNotEmpty())
                        <div class="mt-5 space-y-2">
                            @foreach ($uploadedEvidences as $ev)
                                <div class="flex items-center justify-between gap-3 rounded-xl border border-ink-100 px-3 py-2">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span class="shrink-0 inline-flex items-center text-[10px] font-bold uppercase px-1.5 py-0.5 rounded bg-ink-100 text-ink-500">{{ $evidenceTypes[$ev->type] ?? $ev->type }}</span>
                                        <span class="text-sm text-ink-700 truncate">{{ $ev->title }}</span>
                                        @if ($ev->file_path)
                                            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($ev->file_path) }}" target="_blank"
                                                class="text-xs text-brand-600 hover:underline shrink-0">file</a>
                                        @endif
                                        @if ($ev->external_url)
                                            <a href="{{ $ev->external_url }}" target="_blank"
                                                class="text-xs text-brand-600 hover:underline shrink-0">tautan</a>
                                        @endif
                                    </div>
                                    <button wire:click="deleteEvidence({{ $ev->id }})" type="button"
                                        class="shrink-0 text-red-500 hover:text-red-700" title="Hapus">
                                        <x-admin.icon name="close" class="w-4 h-4" />
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
