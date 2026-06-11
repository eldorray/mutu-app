# Adiwiyata — Fondasi Data & Seeder 24 Indikator

Tanggal: 2026-06-09
Status: Disetujui untuk implementasi

## Tujuan

Menambahkan fondasi data modul Adiwiyata: struktur tabel + seeder berisi 24
indikator Adiwiyata (sumber: `database/seeders/adiwiyata/24 indikator adiwiyata.pdf`),
lengkap dengan "Bukti yang Dilihat" dan "Cara Menilai". Menu/UI "Kelola siklus
Adiwiyata" menyusul di iterasi berikutnya.

## Keputusan desain

- Tabel **terpisah** dari modul Akreditasi. Adiwiyata tidak memakai rubrik 4-level;
  penilaiannya berbasis checklist bukti, hitungan jumlah, atau persentase.
- 24 indikator diseed **flat** (`component_id = null`). Tabel & kolom komponen
  tetap disiapkan agar indikator bisa dikelompokkan manual nanti tanpa migrasi ulang.
- Mengikuti pola model `App\Models\Accreditation*` (`$guarded = []`, relasi Eloquent).

## Skema

Satu file migration membuat 4 tabel:

- `adiwiyata_instruments`: `code` (unique), `name`, `version` (nullable),
  `year` (nullable), `is_active` (default false).
- `adiwiyata_components`: `instrument_id` (FK cascade), `number`, `name`,
  `sort_order`. Unik `[instrument_id, number]`. *(disiapkan, belum diisi)*
- `adiwiyata_indicators`: `instrument_id` (FK cascade), `component_id`
  (nullable FK nullOnDelete), `number` (1–24), `title`, `description` (nullable
  longText — penjelasan/definisi indikator), `scoring_method`
  (`checklist` | `count` | `percentage`), `scoring_guide` (nullable longText —
  teks "Cara Menilai"), `sort_order`. Unik `[instrument_id, number]`.
- `adiwiyata_indicator_evidences`: `indicator_id` (FK cascade), `name` (text —
  1 baris = 1 butir "Bukti yang Dilihat"), `sort_order`.

## Models

`AdiwiyataInstrument` (hasMany components, indicators), `AdiwiyataComponent`
(belongsTo instrument, hasMany indicators), `AdiwiyataIndicator` (belongsTo
instrument & component, hasMany evidences), `AdiwiyataIndicatorEvidence`
(belongsTo indicator).

## Seeder

`AdiwiyataInstrumentSeeder`:
- 1 instrumen aktif (`is_active = true`).
- 24 indikator (`component_id = null`), masing-masing dengan `description`,
  `scoring_method`, `scoring_guide`, dan daftar `evidences`.
- `scoring_method` ditetapkan dari judul: "Persentase…" → `percentage`
  (indikator 5, 13, 20); "Jumlah…" → `count`; sisanya → `checklist`.
- Idempotent via `updateOrCreate` pada kode instrumen & `[instrument_id, number]`
  indikator; evidences di-reset per indikator saat seeding.
- Didaftarkan di `DatabaseSeeder` setelah `AccreditationInstrumentSeeder`.

## Verifikasi

`php artisan migrate` lalu `php artisan db:seed --class=AdiwiyataInstrumentSeeder`
berjalan tanpa error; query memastikan 24 indikator + jumlah bukti per indikator.
