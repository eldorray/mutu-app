<?php

namespace Database\Seeders;

use App\Models\AdiwiyataComponent;
use App\Models\AdiwiyataIndicator;
use App\Models\AdiwiyataInstrument;
use Illuminate\Database\Seeder;

class AdiwiyataInstrumentSeeder extends Seeder
{
    public function run(): void
    {
        $instrument = AdiwiyataInstrument::updateOrCreate(
            ['code' => 'ADIWIYATA-24-2024'],
            [
                'name' => 'Instrumen Penilaian Adiwiyata (24 Indikator)',
                'version' => '2024',
                'year' => 2024,
                'is_active' => true,
            ]
        );

        // Components (per the source PDF) and the id lookup used to assign indicators.
        $componentIds = [];
        foreach ($this->components() as $component) {
            $componentIds[$component['number']] = AdiwiyataComponent::updateOrCreate(
                ['instrument_id' => $instrument->id, 'number' => $component['number']],
                ['name' => $component['name'], 'sort_order' => $component['number']]
            )->id;
        }

        foreach ($this->indicators() as $data) {
            $indicator = AdiwiyataIndicator::updateOrCreate(
                ['instrument_id' => $instrument->id, 'number' => $data['number']],
                [
                    'component_id' => $componentIds[$this->componentNumberFor($data['number'])] ?? null,
                    'title' => $data['title'],
                    'description' => $data['description'] ?? null,
                    'scoring_method' => $data['scoring_method'],
                    'scoring_guide' => $data['scoring_guide'] ?? null,
                    'sort_order' => $data['number'],
                ]
            );

            // Reset evidences so re-seeding stays idempotent.
            $indicator->evidences()->delete();

            foreach (($data['evidences'] ?? []) as $i => $name) {
                $indicator->evidences()->create([
                    'name' => $name,
                    'sort_order' => $i + 1,
                ]);
            }
        }
    }

    /**
     * The 4 Adiwiyata components (source: divider pages of the PDF).
     */
    private function components(): array
    {
        return [
            ['number' => 1, 'name' => 'Kebijakan'],
            ['number' => 2, 'name' => 'Proses Pembelajaran'],
            ['number' => 3, 'name' => 'Kegiatan Berbasis Partisipatif'],
            ['number' => 4, 'name' => 'Prasarana dan Sarana'],
        ];
    }

    /**
     * Map an indicator number to its component number (per the PDF grouping).
     * 1-3 => Kebijakan, 4-7 => Proses Pembelajaran,
     * 8-16 => Kegiatan Berbasis Partisipatif, 17-24 => Prasarana dan Sarana.
     */
    private function componentNumberFor(int $indicatorNumber): int
    {
        return match (true) {
            $indicatorNumber <= 3 => 1,
            $indicatorNumber <= 7 => 2,
            $indicatorNumber <= 16 => 3,
            default => 4,
        };
    }

    /**
     * The 24 Adiwiyata indicators.
     *
     * scoring_method derived from the title: "Persentase…" => percentage,
     * "Jumlah…" => count, otherwise => checklist.
     */
    private function indicators(): array
    {
        return [
            [
                'number' => 1,
                'title' => 'Kurikulum Satuan Pendidikan (visi, misi dan tujuan) memuat aspek lingkungan',
                'scoring_method' => 'checklist',
                'description' => 'Visi, misi dan tujuan yang memuat aspek lingkungan; keterkaitan antara visi, misi dan tujuan; KSP ditandatangani oleh kepala sekolah dan komite sekolah; serta KSP disahkan oleh pejabat berwenang.',
                'scoring_guide' => 'Cermati KSP bagian visi, misi dan tujuan apakah memuat aspek lingkungan.',
                'evidences' => [
                    'Dokumen KSP 1 TA dari 2 TA terakhir.',
                ],
            ],
            [
                'number' => 2,
                'title' => 'Keputusan Kepala Sekolah/Tata Tertib yang memuat aspek lingkungan',
                'scoring_method' => 'checklist',
                'description' => 'SK Kepala Sekolah/Tata Tertib yang memuat aspek lingkungan dan ditetapkan pada tahun berjalan.',
                'scoring_guide' => 'Cek SK/tata tertib kepala sekolah yang memuat aspek lingkungan. 1 SK/tata tertib dapat memuat 1 (satu) aspek atau beberapa aspek lingkungan.',
                'evidences' => [
                    'SK Kepala Sekolah/Tata Tertib yang memuat aspek lingkungan dan ditetapkan pada tahun berjalan.',
                ],
            ],
            [
                'number' => 3,
                'title' => 'Kebijakan lingkungan yang telah diedukasikan ke sekolah melalui bahan informasi',
                'scoring_method' => 'checklist',
                'description' => null,
                'scoring_guide' => 'Cek bahan informasi yang memuat kebijakan lingkungan dan melihat aspek lingkungan apa yang termuat dalam bahan informasi tersebut.',
                'evidences' => [
                    'Dokumentasi penggunaan bahan informasi yang memuat kebijakan lingkungan di sekolah (spanduk/flyer/stiker/tulisan/poster/dll).',
                ],
            ],
            [
                'number' => 4,
                'title' => 'Aspek lingkungan yang diintegrasikan dalam RPP',
                'scoring_method' => 'count',
                'description' => null,
                'scoring_guide' => 'Cek keterkaitan KD/CP, indikator/tujuan pembelajaran, materi, langkah-langkah pembelajaran, sumber belajar dan penilaian. Hitung jumlah aspek lingkungan yang terintegrasi dalam RPP.',
                'evidences' => [
                    'RPP yang mengintegrasikan aspek lingkungan (1 TA dari 2 TA terakhir).',
                    'RPP tersebut sudah disahkan Kepala Sekolah dan ditandatangani guru pengampu.',
                ],
            ],
            [
                'number' => 5,
                'title' => 'Persentase RPP yang mengintegrasikan aspek lingkungan',
                'scoring_method' => 'percentage',
                'description' => null,
                'scoring_guide' => 'Hitung jumlah RPP yang mengintegrasikan aspek lingkungan dibandingkan dengan total RPP.',
                'evidences' => [
                    'RPP yang mengintegrasikan aspek lingkungan (1 TA dari 2 TA terakhir).',
                    'RPP tersebut sudah disahkan Kepala Sekolah dan ditandatangani guru pengampu.',
                ],
            ],
            [
                'number' => 6,
                'title' => 'Aspek lingkungan diintegrasikan dalam laporan ekstrakurikuler',
                'scoring_method' => 'checklist',
                'description' => null,
                'scoring_guide' => 'Cek laporan kegiatan ekstrakurikuler yang terintegrasi aspek lingkungan.',
                'evidences' => [
                    'Laporan kegiatan ekstrakurikuler (1 TA dari 2 TA terakhir).',
                    'Dokumentasi pelaksanaan ekstrakurikuler (foto dan/atau video) disertai keterangan kegiatan, waktu dan tempat.',
                ],
            ],
            [
                'number' => 7,
                'title' => 'Aspek lingkungan termuat dalam hasil karya siswa',
                'scoring_method' => 'checklist',
                'description' => null,
                'scoring_guide' => 'Cek hasil karya siswa yang terintegrasi dengan aspek lingkungan.',
                'evidences' => [
                    'Hasil karya siswa terkait aspek lingkungan, misalnya poster, tulisan, karya seni, dll (1 TA dari 2 TA terakhir).',
                ],
            ],
            [
                'number' => 8,
                'title' => 'Aspek lingkungan yang terintegrasi dalam program rutin sekolah',
                'scoring_method' => 'checklist',
                'description' => null,
                'scoring_guide' => 'a) Cek tabel perencanaan 1 tahun dan/atau 4 tahun untuk melihat program rutin lingkungan. b) Cek dokumentasi pelaksanaan, laporan pelaksanaan dan buku monitoring untuk melihat kegiatan dilaksanakan sebulan sekali dan terkait aspek lingkungan. c) Cek MoU dengan pihak lain terkait aspek lingkungan.',
                'evidences' => [
                    'Tabel perencanaan 1 tahun dan/atau 4 tahun.',
                    'Laporan pelaksanaan program rutin.',
                    'Perjanjian kerjasama dengan pihak lain (jika dilaksanakan dengan pihak lain).',
                    'Buku monitoring/ceklis.',
                ],
            ],
            [
                'number' => 9,
                'title' => 'Jumlah program rutin terkait aspek lingkungan',
                'scoring_method' => 'count',
                'description' => 'Program dinilai sebagai program rutin sekolah apabila dilaksanakan minimal 1 bulan sekali dan tercantum di perencanaan tahunan.',
                'scoring_guide' => 'Penilai menghitung berapa program terkait lingkungan yang dilaksanakan secara rutin.',
                'evidences' => [
                    'Tabel perencanaan 1 tahun dan/atau 4 tahun.',
                    'Laporan pelaksanaan program rutin sekolah.',
                    'Dokumentasi pelaksanaan program rutin sekolah yang terkait aspek lingkungan berupa foto dan/atau video disertai keterangan kegiatan, waktu dan tempat.',
                    'Perjanjian kerjasama dengan pihak lain (jika program rutin dilakukan bersama pihak lain).',
                    'Buku monitoring/ceklis program rutin.',
                ],
            ],
            [
                'number' => 10,
                'title' => 'Aspek lingkungan yang terintegrasi dalam program non rutin',
                'scoring_method' => 'checklist',
                'description' => 'Diartikan sebagai program yang dilakukan di sekolah secara insidentil/non rutin terkait lingkungan.',
                'scoring_guide' => 'Cek dokumentasi pelaksanaan dan laporan pelaksanaan, apakah kegiatan insidentil tersebut merupakan kegiatan terkait lingkungan. Semakin banyak aspek lingkungan yang terintegrasi dalam program non rutin, maka semakin tinggi perolehan nilai dalam indikator ini.',
                'evidences' => [
                    'Dokumentasi pelaksanaan kegiatan insidentil sekolah yang terkait lingkungan berupa foto dan/atau video disertai keterangan kegiatan, waktu dan tempat.',
                    'Laporan pelaksanaan kegiatan insidentil sekolah.',
                    'Perjanjian kerjasama dengan pihak lain (jika kegiatan insidentil dilakukan bersama pihak lain).',
                ],
            ],
            [
                'number' => 11,
                'title' => 'Jumlah kegiatan kampanye dan publikasi program adiwiyata',
                'scoring_method' => 'count',
                'description' => null,
                'scoring_guide' => 'Cek dokumen dan dokumentasi pelaksanaan kampanye dan publikasi program adiwiyata. Apabila kampanye dan publikasi dilaksanakan dengan pihak lain, perlu dilihat perjanjian kerjasama/bukti kerjasama lainnya. Penilai menghitung jumlah kampanye dan publikasi program adiwiyata yang dilakukan. Semakin banyak, semakin tinggi perolehan nilai.',
                'evidences' => [
                    'Dokumentasi kegiatan kampanye dan publikasi berupa foto dan/atau video disertai keterangan kegiatan, waktu dan tempat (bisa dalam bentuk tautan).',
                    'Dokumen (laporan kegiatan kampanye dan publikasi, undangan, brosur, leaflet, dll).',
                ],
            ],
            [
                'number' => 12,
                'title' => 'Jumlah media publikasi program adiwiyata',
                'scoring_method' => 'count',
                'description' => null,
                'scoring_guide' => 'Cek dokumentasi pelaksanaan publikasi program adiwiyata. Penilai melihat jenis media publikasi program adiwiyata yang digunakan. Semakin banyak media publikasi, semakin tinggi perolehan nilai.',
                'evidences' => [
                    'Dokumentasi publikasi program adiwiyata berupa foto dan/atau video disertai keterangan kegiatan, waktu dan tempat (bisa dalam bentuk tautan).',
                    'Dokumen (laporan kegiatan kampanye dan publikasi, undangan, brosur, leaflet, dll).',
                ],
            ],
            [
                'number' => 13,
                'title' => 'Persentase kader adiwiyata',
                'scoring_method' => 'percentage',
                'description' => 'Kader Adiwiyata adalah peserta didik sekolah yang ditetapkan oleh kepala sekolah dan dibina untuk berperan aktif dan menggerakkan warga sekolah dan warga sekitarnya dalam menerapkan perilaku ramah lingkungan hidup.',
                'scoring_guide' => 'Penilai menghitung jumlah kader adiwiyata sesuai keputusan kepala sekolah dibandingkan dengan jumlah peserta didik.',
                'evidences' => [
                    'Surat Keputusan pembentukan kader adiwiyata yang ditandatangani oleh Kepala Sekolah memuat struktur pengurus.',
                    'Dokumen tertulis (surat undangan/notulen/laporan kegiatan/dll) terkait kegiatan pembentukan kader adiwiyata.',
                    'Rencana kerja kader adiwiyata.',
                    'Profil Sekolah untuk mengetahui jumlah peserta didik.',
                    'Dokumentasi kegiatan pembentukan kader adiwiyata (foto dan/atau video disertai keterangan kegiatan, waktu dan tempat).',
                ],
            ],
            [
                'number' => 14,
                'title' => 'Jumlah kegiatan pemberdayaan kader adiwiyata',
                'scoring_method' => 'count',
                'description' => 'Kegiatan pemberdayaan kader adiwiyata adalah peningkatan kapasitas dan/atau aksi terkait lingkungan yang dilakukan kader adiwiyata sesuai dengan rencana kerja kader adiwiyata.',
                'scoring_guide' => 'Cek dokumen dan dokumentasi kegiatan pemberdayaan kader adiwiyata. Penilai menghitung jumlah kegiatan peningkatan kapasitas dan/atau aksi yang dilakukan oleh kader adiwiyata.',
                'evidences' => [
                    'Dokumen tertulis (surat undangan/notulen/laporan kegiatan/dll) terkait kegiatan pemberdayaan Kader Adiwiyata.',
                    'Dokumentasi kegiatan pemberdayaan kader adiwiyata (foto dan/atau video disertai keterangan kegiatan, waktu dan tempat).',
                    'Rencana kerja kader adiwiyata.',
                ],
            ],
            [
                'number' => 15,
                'title' => 'Jumlah kegiatan lingkungan di luar sekolah yang diinisiasi oleh sekolah',
                'scoring_method' => 'count',
                'description' => null,
                'scoring_guide' => 'Cek dokumen dan dokumentasi kegiatan lingkungan di luar sekolah yang diinisiasi oleh sekolah. Penilai menghitung berapa banyak kegiatan yang diinisiasi oleh sekolah terkait lingkungan.',
                'evidences' => [
                    'Dokumen (surat undangan, surat tugas, surat permohonan, absensi, notulensi, dll) kegiatan lingkungan di luar sekolah yang diinisiasi oleh sekolah.',
                    'Dokumentasi kegiatan lingkungan di luar sekolah yang diinisiasi oleh sekolah dari berbagai sudut pengambilan gambar (foto dan/atau video disertai keterangan kegiatan, waktu dan tempat).',
                ],
            ],
            [
                'number' => 16,
                'title' => 'Jumlah kerjasama atau komitmen terkait lingkungan',
                'scoring_method' => 'count',
                'description' => null,
                'scoring_guide' => 'Penilai menghitung kerjasama sekolah dengan mitra terkait lingkungan. Semakin banyak kerjasama terkait lingkungan yang dilakukan bersama mitra, maka semakin tinggi perolehan nilai dalam indikator ini.',
                'evidences' => [
                    'Dokumen kerjasama antara sekolah dengan para pihak (perjanjian kerja sama/undangan/notulensi pertemuan/serah terima bantuan/dokumen terkait).',
                    'Tautan dari group jejaring kerja dan komunikasi di media sosial.',
                    'Dokumentasi kegiatan yang dilakukan dengan para pihak (foto dan/atau video disertai keterangan kegiatan, waktu dan tempat).',
                ],
            ],
            [
                'number' => 17,
                'title' => 'Aspek lingkungan yang menggunakan prasarana dan sarana sekolah sebagai lingkungan pembelajaran',
                'scoring_method' => 'checklist',
                'description' => 'Penilai melihat aspek lingkungan apa yang diintegrasikan dalam pembelajaran dengan menggunakan prasarana dan sarana yang ada di sekolah.',
                'scoring_guide' => 'Cek apakah ada prasarana dan sarana terkait lingkungan yang dimanfaatkan sebagai lingkungan pembelajaran. Contoh: kebun TOGA → aspek keanekaragaman hayati; tempat sampah terpilah → aspek pengelolaan sampah, maka dihitung 2 aspek.',
                'evidences' => [
                    'Dokumentasi penggunaan prasarana dan sarana lingkungan di sekolah sebagai lingkungan pembelajaran berupa foto dan/atau video disertai keterangan kegiatan, waktu dan tempat.',
                    'RPP terintegrasi lingkungan.',
                ],
            ],
            [
                'number' => 18,
                'title' => 'Jumlah prasarana dan sarana sanitasi yang ada di sekolah',
                'scoring_method' => 'count',
                'description' => 'Prasarana dan sarana sanitasi di sekolah yang bersih dan terawat dengan baik.',
                'scoring_guide' => 'Penilai melihat prasarana dan sarana sanitasi di sekolah yang bersih dan terawat dengan baik. Contoh: ketersediaan air bersih, toilet terpisah/sarana cuci tangan yang bersih, saluran pembuangan air limbah/drainase yang berfungsi, dan terpeliharanya septic tank.',
                'evidences' => [
                    'Dokumen dan dokumentasi prasarana dan sarana sanitasi berupa foto dan/atau video disertai keterangan kegiatan, waktu dan tempat.',
                ],
            ],
            [
                'number' => 19,
                'title' => 'Jumlah prasarana dan sarana pengelolaan sampah yang ada di sekolah',
                'scoring_method' => 'count',
                'description' => 'Penilai perlu melihat prasarana dan sarana pengelolaan sampah yang ada di sekolah yang digunakan dan terawat dengan baik.',
                'scoring_guide' => 'Penilai melihat prasarana dan sarana pengelolaan sampah yang ada di sekolah yang digunakan dan terawat dengan baik. Contoh: tempat sampah terpilah, tempat pengomposan, bank sampah, dan lainnya.',
                'evidences' => [
                    'Dokumentasi prasarana dan sarana pengelolaan sampah berupa foto dan/atau video disertai keterangan kegiatan, waktu dan tempat.',
                ],
            ],
            [
                'number' => 20,
                'title' => 'Persentase jumlah pengurangan timbulan sampah',
                'scoring_method' => 'percentage',
                'description' => null,
                'scoring_guide' => 'Isi data volume sampah yang tidak dapat diolah lebih lanjut melalui proses pengurangan atau penanganan sampah di sekolah yang dibuang ke TPA/TPS/3R pada 1 TA dari 2 TA terakhir dan pada tahun ajaran sebelum menjadi sekolah adiwiyata. Isi jumlah warga sekolah pada masing-masing periode, lalu bandingkan untuk menghitung persentase pengurangan timbulan sampah.',
                'evidences' => [
                    'Data/catatan volume sampah yang tidak dapat diolah lebih lanjut melalui proses pengurangan maupun penanganan sampah di sekolah yang dibuang ke TPA/TPS3R, 1 TA dari 2 TA terakhir.',
                    'Data/catatan volume sampah yang dibuang ke TPA/TPS3R pada tahun ajaran sebelum menjadi Sekolah Adiwiyata.',
                    'Profil sekolah yang menunjukkan jumlah warga sekolah pada satu tahun ajaran dari dua tahun ajaran terakhir.',
                    'Profil sekolah yang menunjukkan jumlah warga sekolah pada tahun ajaran sebelum mengikuti adiwiyata.',
                ],
            ],
            [
                'number' => 21,
                'title' => 'Jumlah prasarana dan sarana keanekaragaman hayati yang ada di sekolah',
                'scoring_method' => 'count',
                'description' => 'Prasarana dan sarana keanekaragaman hayati yang ada di sekolah yang digunakan dan terawat dengan baik.',
                'scoring_guide' => 'Penilai melihat prasarana dan sarana keanekaragaman hayati yang ada di sekolah yang digunakan dan terawat dengan baik. Contoh: TOGA, tipe ekosistem buatan/alami, kebun pangan/sayur, tanaman, pohon, terumbu karang, mangrove, dan ekosistem lainnya.',
                'evidences' => [
                    'Dokumen dan dokumentasi prasarana dan sarana keanekaragaman hayati berupa foto dan/atau video disertai keterangan kegiatan, waktu dan tempat.',
                ],
            ],
            [
                'number' => 22,
                'title' => 'Jumlah tanaman/pohon yang ditanam dan dipelihara di sekolah',
                'scoring_method' => 'count',
                'description' => 'Tanaman adalah tumbuhan non kayu seperti tanaman hias atau tanaman yang digunakan dalam vertical garden atau tanaman dalam pot yang bukan tanaman semusim. Pohon adalah tumbuhan yang memiliki batang berkayu.',
                'scoring_guide' => 'Hitung jumlah tanaman dan pohon yang ada dan dirawat di sekolah, kemudian dibandingkan dengan jumlah warga sekolah.',
                'evidences' => [
                    'Buku monitoring/ceklis kegiatan penanaman dan pemeliharaan untuk melihat tanaman/pohon benar-benar dipelihara dan dirawat oleh warga sekolah.',
                    'Dokumentasi terkait kondisi dan keberadaan tanaman dan pohon berupa foto dan/atau video disertai keterangan waktu dan tempat.',
                    'Daftar jenis dan jumlah pohon/tanaman yang ditanam dan tumbuh.',
                ],
            ],
            [
                'number' => 23,
                'title' => 'Jumlah prasarana dan sarana penghematan dan konservasi energi yang ada di sekolah',
                'scoring_method' => 'count',
                'description' => 'Prasarana dan sarana penghematan dan konservasi energi yang ada di sekolah yang digunakan dan terawat dengan baik.',
                'scoring_guide' => 'Penilai melihat prasarana dan sarana penghematan dan konservasi energi yang ada di sekolah yang digunakan dan terawat dengan baik. Contoh: lampu hemat energi; peralatan hemat energi di sekolah; pencahayaan dan ventilasi alami sekolah.',
                'evidences' => [
                    'Dokumen dan dokumentasi prasarana dan sarana penghematan dan konservasi energi berupa foto dan/atau video disertai keterangan kegiatan, waktu dan tempat.',
                ],
            ],
            [
                'number' => 24,
                'title' => 'Jumlah prasarana dan sarana penghematan dan konservasi air yang ada di sekolah',
                'scoring_method' => 'count',
                'description' => 'Prasarana dan sarana penghematan dan konservasi air yang ada di sekolah yang digunakan dan terawat dengan baik.',
                'scoring_guide' => 'Penilai melihat prasarana dan sarana penghematan dan konservasi air yang ada di sekolah yang digunakan dan terawat dengan baik. Contoh: lubang biopori/embung buatan/sumur resapan, penampungan/pemanfaatan air hujan, daur ulang air (air bekas wudhu, air cuci tangan, sisa air), sarana penghematan air (tidak ada kran/pipa bocor, alat siram tanaman sederhana, alat pengatur kran sederhana).',
                'evidences' => [
                    'Dokumen dan dokumentasi prasarana dan sarana penghematan dan konservasi air berupa foto dan/atau video disertai keterangan kegiatan, waktu dan tempat.',
                ],
            ],
        ];
    }
}
