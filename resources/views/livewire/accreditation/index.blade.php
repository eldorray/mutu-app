<div>
    @php $user = auth()->user(); @endphp

    {{-- Page header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h2 class="text-3xl font-bold text-ink-900 mb-1">Akreditasi</h2>
            <p class="text-ink-500 text-sm">
                @if ($user->isKepsek())
                    Pantau progres pengisian akreditasi oleh guru.
                @else
                    Kelola siklus akreditasi madrasah/sekolah.
                @endif
            </p>
        </div>
        @if (!$showForm && !$showImport && $user->isGuru())
            <button wire:click="create" type="button" class="btn-primary">
                <x-admin.icon name="plus" class="w-4 h-4" />
                Tambah Siklus
            </button>
        @endif
    </div>

    {{-- Form Create/Edit --}}
    @if ($showForm && $user->isGuru())
        <div class="bg-white rounded-2xl shadow-soft p-6 mb-6">
            <h3 class="text-lg font-bold text-ink-900 mb-4">
                {{ $editingCycleId ? 'Edit Siklus Akreditasi' : 'Tambah Siklus Akreditasi Baru' }}
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-ink-700 mb-1">Nama Sekolah/Madrasah</label>
                    <input type="text" wire:model="schoolName" class="chip-input"
                        placeholder="Contoh: MTs Negeri 1 Kota Malang">
                    @error('schoolName')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-700 mb-1">Tahun Ajaran</label>
                    <input type="number" wire:model="year" class="chip-input" min="2020" max="2099"
                        placeholder="2025">
                    @error('year')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                @if (!$editingCycleId)
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1">Nama Instrumen</label>
                        <input type="text" wire:model="instrumentName" class="chip-input"
                            placeholder="Contoh: IA2024 Dasmen 2025">
                        @error('instrumentName')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
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

    {{-- Import Panel --}}
    @if ($showImport)
        <div class="bg-white rounded-2xl shadow-soft p-6 mb-6 border-2 border-brand-200">
            <h3 class="text-lg font-bold text-ink-900 mb-2">Import Instrumen Akreditasi</h3>
            <p class="text-sm text-ink-500 mb-4">Upload file Excel (.xlsx) atau PDF untuk mengimpor indikator dan rubrik
                penilaian.</p>

            @if ($importMessage)
                <div
                    class="mb-4 rounded-xl px-4 py-3 text-sm {{ $importSuccess ? 'bg-emerald-50 border border-emerald-100 text-emerald-700' : 'bg-red-50 border border-red-100 text-red-700' }}">
                    {{ $importMessage }}
                </div>
            @endif

            <div class="space-y-4">
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="radio" wire:model.live="importType" value="excel"
                            class="text-brand-500 focus:ring-brand-500">
                        <span class="font-medium text-ink-700">Excel (.xlsx)</span>
                    </label>
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="radio" wire:model.live="importType" value="pdf"
                            class="text-brand-500 focus:ring-brand-500">
                        <span class="font-medium text-ink-700">PDF (regex)</span>
                    </label>
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="radio" wire:model.live="importType" value="ai"
                            class="text-brand-500 focus:ring-brand-500">
                        <span class="font-medium text-ink-700">PDF + AI (DeepSeek)</span>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-ink-700 mb-1">
                        Pilih File {{ $importType === 'excel' ? '(.xlsx)' : '(.pdf)' }}
                    </label>
                    <input type="file" wire:model="importFile" class="chip-input text-sm"
                        accept="{{ $importType === 'excel' ? '.xlsx,.xls' : '.pdf' }}">
                    @error('importFile')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                @if ($importType === 'excel')
                    <div class="p-3 rounded-xl bg-blue-50 border border-blue-100">
                        <p class="text-xs font-medium text-blue-700 mb-1">Format kolom Excel:</p>
                        <p class="text-xs text-blue-600">A: Komponen | B: Butir | C: Kode | D: Judul | E: Definisi | F:
                            Rubrik Kurang | G: Rubrik Cukup Baik | H: Rubrik Baik | I: Rubrik Sangat Baik | J: Boleh N/A
                            | K: Saran Bukti</p>
                        <button wire:click="downloadTemplate" type="button"
                            class="mt-2 text-xs font-medium text-blue-700 underline hover:text-blue-900">
                            Download Template Excel
                        </button>
                    </div>
                @elseif ($importType === 'pdf')
                    <div class="p-3 rounded-xl bg-amber-50 border border-amber-100">
                        <p class="text-xs text-amber-700">PDF akan di-extract teksnya dan sistem akan mencoba mendeteksi
                            indikator secara otomatis menggunakan regex. Untuk hasil terbaik, gunakan format Excel.</p>
                    </div>
                @elseif ($importType === 'ai')
                    <div class="p-3 rounded-xl bg-violet-50 border border-violet-100">
                        <p class="text-xs font-medium text-violet-700 mb-1">AI-Powered Import (DeepSeek)</p>
                        <p class="text-xs text-violet-600">PDF akan di-extract teksnya lalu dikirim ke DeepSeek AI untuk
                            distrukturkan menjadi indikator dan rubrik secara otomatis. Proses ini membutuhkan waktu
                            30-60 detik.</p>
                    </div>
                @endif

                @if ($pdfPreviewText)
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1">Teks yang di-extract dari
                            PDF:</label>
                        <textarea class="chip-input text-xs font-mono min-h-[200px]" readonly>{{ $pdfPreviewText }}</textarea>
                    </div>
                @endif

                <div class="flex items-center gap-3">
                    <button wire:click="processImport" type="button" class="btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="processImport">Import</span>
                        <span wire:loading wire:target="processImport">Memproses...</span>
                    </button>
                    <button wire:click="cancelImport" type="button" class="btn-ghost" wire:loading.attr="disabled"
                        wire:target="processImport">
                        Batal
                    </button>
                </div>

                {{-- Loading overlay --}}
                <div wire:loading wire:target="processImport"
                    class="mt-4 p-4 rounded-xl bg-ink-50 border border-ink-200">
                    <div class="flex items-center gap-4">
                        <div class="relative w-10 h-10 shrink-0">
                            <div class="absolute inset-0 rounded-full border-4 border-ink-200"></div>
                            <div
                                class="absolute inset-0 rounded-full border-4 border-brand-500 border-t-transparent animate-spin">
                            </div>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-ink-900">Sedang memproses...</p>
                            <p class="text-xs text-ink-500 mt-0.5">
                                @if ($importType === 'ai')
                                    AI sedang menganalisis dokumen PDF. Proses ini membutuhkan waktu 30-90 detik.
                                @else
                                    Mengimpor data dari file. Mohon tunggu sebentar.
                                @endif
                            </p>
                        </div>
                    </div>
                    @if ($importType === 'ai')
                        <div class="mt-3 space-y-1.5">
                            <div class="flex items-center gap-2 text-xs text-ink-500">
                                <div class="w-1.5 h-1.5 rounded-full bg-brand-500 animate-pulse"></div>
                                Mengekstrak teks dari PDF...
                            </div>
                            <div class="flex items-center gap-2 text-xs text-ink-500">
                                <div class="w-1.5 h-1.5 rounded-full bg-brand-500 animate-pulse"
                                    style="animation-delay: 0.5s"></div>
                                Mengirim ke DeepSeek AI untuk distrukturkan...
                            </div>
                            <div class="flex items-center gap-2 text-xs text-ink-500">
                                <div class="w-1.5 h-1.5 rounded-full bg-brand-500 animate-pulse"
                                    style="animation-delay: 1s"></div>
                                Menyimpan indikator dan rubrik ke database...
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Success/Error notification after import --}}
    @if ($importMessage && !$showImport)
        <div
            class="mb-6 rounded-2xl px-5 py-4 {{ $importSuccess ? 'bg-emerald-50 border border-emerald-200' : 'bg-red-50 border border-red-200' }}">
            <div class="flex items-start gap-3">
                @if ($importSuccess)
                    <x-admin.icon name="check-circle" class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" />
                @else
                    <x-admin.icon name="close" class="w-5 h-5 text-red-600 shrink-0 mt-0.5" />
                @endif
                <div>
                    <p class="text-sm font-medium {{ $importSuccess ? 'text-emerald-800' : 'text-red-800' }}">
                        {{ $importSuccess ? 'Import Berhasil!' : 'Import Gagal' }}
                    </p>
                    <p class="text-sm {{ $importSuccess ? 'text-emerald-600' : 'text-red-600' }} mt-0.5">
                        {{ $importMessage }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Cycles List --}}
    @if ($cycles->isEmpty() && !$showForm && !$showImport)
        <div class="bg-ink-100 rounded-2xl p-12 text-center">
            <x-admin.icon name="clipboard" class="w-16 h-16 text-ink-200 mx-auto mb-4" />
            <p class="text-ink-600 font-medium">Belum ada siklus akreditasi.</p>
            @if ($user->isGuru())
                <p class="text-ink-400 text-sm mt-1">Klik "Tambah Siklus" untuk memulai.</p>
            @else
                <p class="text-ink-400 text-sm mt-1">Guru belum membuat siklus akreditasi.</p>
            @endif
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

                                @if ($cycle->filled_count === 0)
                                    <span
                                        class="inline-flex items-center text-xs font-medium px-2 py-0.5 rounded-full bg-ink-100 text-ink-500">
                                        Belum Diisi
                                    </span>
                                @elseif ($cycle->is_lengkap)
                                    <span
                                        class="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">
                                        <x-admin.icon name="check-circle" class="w-3 h-3" />
                                        Lengkap
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center text-xs font-medium px-2 py-0.5 rounded-full bg-amber-50 text-amber-700">
                                        Belum Lengkap ({{ $cycle->filled_count }}/{{ $cycle->total_indicators }})
                                    </span>
                                @endif
                            </div>
                            <p class="text-sm text-ink-500">{{ $cycle->instrument->name }} &mdash; Tahun
                                {{ $cycle->year }}</p>

                            <div class="flex items-center gap-3 mt-3">
                                <div class="flex-1 h-2 bg-ink-100 rounded-full overflow-hidden max-w-[240px]">
                                    <div class="h-full rounded-full transition-all {{ $cycle->is_lengkap ? 'bg-emerald-500' : 'bg-brand-500' }}"
                                        style="width: {{ $cycle->progress_percent }}%"></div>
                                </div>
                                <span class="text-xs text-ink-500 font-medium">{{ $cycle->progress_percent }}%
                                    terisi</span>
                            </div>

                            @if ($cycle->filled_count > 0)
                                <div class="flex items-center gap-4 mt-3 flex-wrap">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-ink-500">Rata-rata:</span>
                                        <span class="text-sm font-bold text-ink-900">{{ $cycle->avg_score }}</span>
                                        <span class="text-xs text-ink-400">/ 4</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-ink-500">Nilai Akhir:</span>
                                        <span class="text-sm font-bold text-ink-900">{{ $cycle->final_score }}</span>
                                        <span class="text-xs text-ink-400">/ 100</span>
                                    </div>
                                    <span
                                        class="inline-flex items-center text-xs font-bold px-2.5 py-1 rounded-full border {{ $cycle->peringkat['color'] }}">
                                        {{ $cycle->peringkat['label'] }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-2 shrink-0 flex-wrap">
                            @if ($user->isGuru())
                                <a href="{{ route('accreditation.filling', $cycle) }}" wire:navigate
                                    class="btn-primary text-xs px-4 py-2">
                                    <x-admin.icon name="file" class="w-3.5 h-3.5" />
                                    Isi Indikator
                                </a>
                                <a href="{{ route('accreditation.indicators', $cycle) }}" wire:navigate
                                    class="btn-ghost text-xs px-3 py-2">
                                    <x-admin.icon name="clipboard" class="w-3.5 h-3.5" />
                                    Daftar Indikator
                                </a>
                                <button wire:click="openImport({{ $cycle->id }})" type="button"
                                    class="btn-ghost text-xs px-3 py-2">
                                    <x-admin.icon name="upload" class="w-3.5 h-3.5" />
                                    Import
                                </button>
                                <button wire:click="edit({{ $cycle->id }})" type="button"
                                    class="btn-ghost text-xs px-3 py-2">
                                    <x-admin.icon name="settings" class="w-3.5 h-3.5" />
                                    Edit
                                </button>
                                <button
                                    @click="$dispatch('open-delete-modal', { id: 'delete-cycle', action: 'delete({{ $cycle->id }})', title: 'Hapus Siklus Akreditasi', message: 'Yakin ingin menghapus siklus {{ $cycle->school->name }} ({{ $cycle->year }})? Semua data pengisian akan ikut terhapus.' })"
                                    type="button"
                                    class="inline-flex items-center justify-center gap-1.5 rounded-full border border-red-200 bg-white px-3 py-2 text-xs font-medium text-red-600 hover:bg-red-50 shadow-soft transition">
                                    <x-admin.icon name="close" class="w-3.5 h-3.5" />
                                    Hapus
                                </button>
                            @else
                                <a href="{{ route('accreditation.monitoring', $cycle) }}" wire:navigate
                                    class="btn-ghost text-xs px-4 py-2">
                                    <x-admin.icon name="eye" class="w-3.5 h-3.5" />
                                    Lihat Detail
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Legend --}}
        <div class="mt-6 bg-ink-50 rounded-2xl p-4">
            <h4 class="text-xs font-bold text-ink-700 mb-2">Keterangan Peringkat Akreditasi</h4>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                    <span class="text-ink-600"><strong>A (Unggul)</strong>: 91 - 100</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                    <span class="text-ink-600"><strong>B (Baik)</strong>: 81 - 90</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                    <span class="text-ink-600"><strong>C (Cukup)</strong>: 71 - 80</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-red-500"></span>
                    <span class="text-ink-600"><strong>Tidak Terakreditasi</strong>: &lt; 71</span>
                </div>
            </div>
            <p class="text-xs text-ink-400 mt-2">Nilai akhir = (rata-rata skor 1-4 / 4) × 100. Skor per indikator:
                Kurang = 1, Cukup Baik = 2, Baik = 3, Sangat Baik = 4.</p>
        </div>
    @endif

    <x-admin.delete-modal id="delete-cycle" />
</div>
