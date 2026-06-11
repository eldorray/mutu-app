<div>
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 mb-1">
        <a href="{{ route('accreditation.index') }}" wire:navigate
            class="text-ink-400 hover:text-ink-600 transition-colors">
            <x-admin.icon name="clipboard" class="w-4 h-4" />
        </a>
        <span class="text-ink-300">/</span>
        <span class="text-sm text-ink-500">Monitoring</span>
    </div>

    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-ink-900">{{ $cycle->school->name }}</h2>
            <p class="text-ink-500 text-sm">{{ $cycle->instrument->name }} &mdash; Tahun {{ $cycle->year }}</p>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-5 shadow-soft">
            <p class="text-xs text-ink-500 font-medium mb-1">Progres Pengisian</p>
            <p class="text-2xl font-bold text-ink-900">{{ $filled }}<span
                    class="text-sm font-normal text-ink-400"> / {{ $totalIndicators }}</span></p>
            <div class="mt-2 h-1.5 bg-ink-100 rounded-full overflow-hidden">
                <div class="h-full bg-brand-500 rounded-full" style="width: {{ $progressPercent }}%"></div>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-soft">
            <p class="text-xs text-ink-500 font-medium mb-1">Persentase</p>
            <p class="text-2xl font-bold text-brand-600">{{ $progressPercent }}%</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-soft">
            <p class="text-xs text-ink-500 font-medium mb-1">Rata-rata Skor</p>
            <p class="text-2xl font-bold text-ink-900">{{ $avgScore }} <span
                    class="text-sm font-normal text-ink-400">/ 4</span></p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-soft">
            <p class="text-xs text-ink-500 font-medium mb-1">Nilai Akhir & Peringkat</p>
            <p class="text-2xl font-bold text-ink-900">{{ $finalScore }}</p>
            <span
                class="inline-flex items-center text-xs font-bold px-2 py-0.5 rounded-full border mt-1 {{ $peringkat['color'] }}">
                {{ $peringkat['label'] }}
            </span>
        </div>
    </div>

    {{-- Indicator Table --}}
    <div class="bg-white rounded-2xl shadow-soft overflow-hidden">
        <div class="p-5 border-b border-ink-100">
            <h3 class="text-sm font-bold text-ink-900">Detail Per Indikator</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left min-w-[700px]">
                <thead>
                    <tr class="text-xs text-ink-400 border-b border-ink-100 bg-ink-50/50">
                        <th class="font-medium py-3 px-4">Kode</th>
                        <th class="font-medium py-3 px-4">Indikator</th>
                        <th class="font-medium py-3 px-4">Status</th>
                        <th class="font-medium py-3 px-4">Nilai</th>
                        <th class="font-medium py-3 px-4">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @foreach ($indicators as $indicator)
                        @php
                            $score = $scores->get($indicator->id);
                            $status = $score?->checklist_status ?? 'belum_diisi';
                            [$statusLabel, $statusBadge] = match ($status) {
                                'lengkap' => ['Lengkap', 'bg-emerald-50 text-emerald-700'],
                                'tidak_lengkap' => ['Tidak Lengkap', 'bg-red-50 text-red-600'],
                                'perlu_revisi' => ['Perlu Revisi', 'bg-amber-50 text-amber-700'],
                                'na' => ['N/A', 'bg-ink-100 text-ink-500'],
                                default => ['Belum Diisi', 'bg-ink-100 text-ink-400'],
                            };
                            $scoreValue = $score?->score_value;
                            $rubricLabel = $score?->rubric?->level?->label;
                        @endphp
                        <tr class="border-b border-ink-50 last:border-0 hover:bg-ink-50/40 transition-colors">
                            <td class="py-3 px-4">
                                <span class="text-xs font-bold text-brand-600">{{ $indicator->code }}</span>
                            </td>
                            <td class="py-3 px-4 max-w-[300px]">
                                <p class="text-ink-700 line-clamp-2">{{ $indicator->title }}</p>
                            </td>
                            <td class="py-3 px-4">
                                <span
                                    class="inline-flex text-[10px] font-medium px-2 py-0.5 rounded-full {{ $statusBadge }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                @if ($scoreValue)
                                    <span class="font-bold text-ink-900">{{ $scoreValue }}/4</span>
                                    <span class="text-xs text-ink-400 block">{{ $rubricLabel }}</span>
                                @elseif ($score?->is_na)
                                    <span class="text-ink-400">N/A</span>
                                @else
                                    <span class="text-ink-300">—</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 max-w-[200px]">
                                @if ($score?->teacher_note)
                                    <p class="text-xs text-ink-600 line-clamp-2">{{ $score->teacher_note }}</p>
                                @else
                                    <span class="text-ink-300">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
