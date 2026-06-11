<div>
    {{-- Page header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h2 class="text-3xl font-bold text-ink-900 mb-1">
                Selamat datang, {{ auth()->user()->name }}!
            </h2>
            <p class="text-ink-500 text-sm">Ringkasan progres akreditasi madrasah/sekolah Anda.</p>
        </div>
        @if ($this->activeCycle)
            <a href="{{ route('accreditation.index') }}" wire:navigate class="btn-primary">
                <x-admin.icon name="clipboard" class="w-4 h-4" />
                Kelola Akreditasi
            </a>
        @endif
    </div>

    @if (!$this->activeCycle)
        <div class="bg-ink-100 rounded-2xl p-12 text-center">
            <x-admin.icon name="clipboard" class="w-16 h-16 text-ink-200 mx-auto mb-4" />
            <p class="text-ink-600 font-medium">Belum ada siklus akreditasi.</p>
            <p class="text-ink-400 text-sm mt-1">Buat siklus akreditasi di menu Akreditasi untuk memulai.</p>
            <a href="{{ route('accreditation.index') }}" wire:navigate class="btn-primary mt-4 inline-flex">
                Buka Menu Akreditasi
            </a>
        </div>
    @else
        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 shadow-soft">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-9 h-9 rounded-xl bg-brand-50 flex items-center justify-center">
                        <x-admin.icon name="file" class="w-4 h-4 text-brand-600" />
                    </div>
                    <p class="text-xs text-ink-500 font-medium">Total Indikator</p>
                </div>
                <p class="text-2xl font-bold text-ink-900">{{ $this->stats['total_indicators'] }}</p>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-soft">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-9 h-9 rounded-xl bg-brand-50 flex items-center justify-center">
                        <x-admin.icon name="check-circle" class="w-4 h-4 text-brand-600" />
                    </div>
                    <p class="text-xs text-ink-500 font-medium">Sudah Terisi</p>
                </div>
                <p class="text-2xl font-bold text-ink-900">{{ $this->stats['filled'] }} <span
                        class="text-sm font-normal text-ink-400">/ {{ $this->stats['total_indicators'] }}</span></p>
                <div class="mt-2 h-1.5 bg-ink-100 rounded-full overflow-hidden">
                    <div class="h-full bg-brand-500 rounded-full transition-all"
                        style="width: {{ $this->stats['progress_percent'] }}%"></div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-soft">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-9 h-9 rounded-xl bg-brand-50 flex items-center justify-center">
                        <x-admin.icon name="star" class="w-4 h-4 text-brand-600" />
                    </div>
                    <p class="text-xs text-ink-500 font-medium">Rata-rata Skor</p>
                </div>
                <p class="text-2xl font-bold text-ink-900">{{ $this->stats['avg_score'] }} <span
                        class="text-sm font-normal text-ink-400">/ 4</span></p>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-soft">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-9 h-9 rounded-xl bg-brand-50 flex items-center justify-center">
                        <x-admin.icon name="trend-up" class="w-4 h-4 text-brand-600" />
                    </div>
                    <p class="text-xs text-ink-500 font-medium">Nilai Akhir</p>
                </div>
                <p class="text-2xl font-bold text-ink-900">{{ $this->stats['final_score'] }} <span
                        class="text-sm font-normal text-ink-400">/ 100</span></p>
                <span
                    class="inline-flex items-center text-xs font-bold px-2 py-0.5 rounded-full border mt-1 {{ $this->peringkat['bg'] }} {{ $this->peringkat['color'] }}">
                    {{ $this->peringkat['label'] }}
                </span>
            </div>
        </div>

        {{-- Active Cycle Info --}}
        <div class="bg-white rounded-2xl p-5 shadow-soft mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-ink-500 font-medium mb-1">Siklus Aktif</p>
                    <h3 class="text-lg font-bold text-ink-900">{{ $this->activeCycle->school->name }}</h3>
                    <p class="text-sm text-ink-500">{{ $this->activeCycle->instrument->name }} &mdash;
                        {{ $this->activeCycle->year }}</p>
                </div>
                <div class="text-right">
                    <p class="text-3xl font-bold text-brand-600">{{ $this->stats['progress_percent'] }}%</p>
                    <p class="text-xs text-ink-400">terisi</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- Component Progress --}}
            <div class="lg:col-span-5">
                <div class="bg-white rounded-2xl shadow-soft p-5">
                    <h3 class="text-sm font-bold text-ink-900 mb-4">Progres Per Komponen</h3>
                    <div class="space-y-4">
                        @foreach ($this->componentProgress as $comp)
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <p class="text-sm text-ink-700 font-medium">Komponen {{ $comp['number'] }}</p>
                                    <span class="text-xs text-ink-500">{{ $comp['filled'] }}/{{ $comp['total'] }}
                                        &bull; Skor {{ $comp['avg_score'] }}</span>
                                </div>
                                <div class="h-2 bg-ink-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-brand-500 rounded-full transition-all"
                                        style="width: {{ $comp['percent'] }}%"></div>
                                </div>
                                <p class="text-[11px] text-ink-400 mt-1 line-clamp-1">{{ $comp['name'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Recent Activity --}}
            <div class="lg:col-span-7">
                <div class="bg-white rounded-2xl shadow-soft p-5">
                    <h3 class="text-sm font-bold text-ink-900 mb-4">Pengisian Terbaru</h3>
                    @if (empty($this->recentScores))
                        <p class="text-sm text-ink-400 text-center py-6">Belum ada indikator yang diisi.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left min-w-[500px]">
                                <thead>
                                    <tr class="text-xs text-ink-400 border-b border-ink-100">
                                        <th class="font-medium py-2 px-3">Kode</th>
                                        <th class="font-medium py-2 px-3">Indikator</th>
                                        <th class="font-medium py-2 px-3">Nilai</th>
                                        <th class="font-medium py-2 px-3">Status</th>
                                        <th class="font-medium py-2 px-3">Waktu</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm">
                                    @foreach ($this->recentScores as $item)
                                        @php
                                            [$statusLabel, $statusBadge] = match ($item['status']) {
                                                'lengkap' => ['Lengkap', 'bg-emerald-50 text-emerald-700'],
                                                'tidak_lengkap' => ['Tidak Lengkap', 'bg-red-50 text-red-600'],
                                                'perlu_revisi' => ['Perlu Revisi', 'bg-amber-50 text-amber-700'],
                                                default => ['Belum Diisi', 'bg-ink-100 text-ink-400'],
                                            };
                                        @endphp
                                        <tr class="border-b border-ink-50 last:border-0">
                                            <td class="py-2.5 px-3">
                                                <span
                                                    class="text-xs font-bold text-brand-600">{{ $item['code'] }}</span>
                                            </td>
                                            <td class="py-2.5 px-3 max-w-[180px]">
                                                <p class="text-ink-700 truncate text-xs">{{ $item['title'] }}</p>
                                            </td>
                                            <td class="py-2.5 px-3">
                                                <span class="font-bold text-ink-900">{{ $item['score'] }}/4</span>
                                                <span
                                                    class="text-[10px] text-ink-400 block">{{ $item['label'] }}</span>
                                            </td>
                                            <td class="py-2.5 px-3">
                                                <span
                                                    class="inline-flex text-[10px] font-medium px-1.5 py-0.5 rounded-full {{ $statusBadge }}">{{ $statusLabel }}</span>
                                            </td>
                                            <td class="py-2.5 px-3 text-xs text-ink-400">{{ $item['date'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
