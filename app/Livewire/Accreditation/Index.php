<?php

namespace App\Livewire\Accreditation;

use App\Models\AccreditationCycle;
use App\Models\AccreditationIndicator;
use App\Models\AccreditationIndicatorScore;
use App\Models\AccreditationInstrument;
use App\Models\School;
use App\Services\InstrumentImportService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
#[Title('Akreditasi')]
class Index extends Component
{
    use WithFileUploads;

    // Form fields
    public string $schoolName = '';
    public string $year = '';
    public string $instrumentName = '';

    // Edit state
    public bool $showForm = false;
    public ?int $editingCycleId = null;

    // Import state
    public bool $showImport = false;
    public $importFile = null;
    public string $importType = 'excel'; // excel, pdf, ai
    public ?string $pdfPreviewText = null;
    public ?string $importMessage = null;
    public bool $importSuccess = false;

    public function mount(): void
    {
        $this->year = (string) date('Y');
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $cycleId): void
    {
        $cycle = AccreditationCycle::with('school')->findOrFail($cycleId);
        $this->editingCycleId = $cycle->id;
        $this->schoolName = $cycle->school->name;
        $this->year = (string) $cycle->year;
        $this->showForm = true;
    }

    public function save(): void
    {
        $rules = [
            'schoolName' => ['required', 'string', 'max:255'],
            'year' => ['required', 'digits:4', 'integer', 'min:2020', 'max:2099'],
        ];

        if (! $this->editingCycleId) {
            $rules['instrumentName'] = ['required', 'string', 'max:255'];
        }

        $this->validate($rules);

        if ($this->editingCycleId) {
            $cycle = AccreditationCycle::findOrFail($this->editingCycleId);
            $cycle->school->update(['name' => $this->schoolName]);
            $cycle->update(['year' => $this->year]);
        } else {
            // Always create a new instrument per cycle
            $instrument = AccreditationInstrument::create([
                'code' => 'INSTR-' . strtoupper(\Illuminate\Support\Str::random(6)) . '-' . $this->year,
                'name' => $this->instrumentName,
                'version' => $this->year,
                'year' => $this->year,
                'is_active' => true,
            ]);

            $school = School::create([
                'name' => $this->schoolName,
            ]);

            AccreditationCycle::create([
                'school_id' => $school->id,
                'instrument_id' => $instrument->id,
                'year' => $this->year,
                'status' => 'draft',
            ]);
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function delete(int $cycleId): void
    {
        $cycle = AccreditationCycle::findOrFail($cycleId);
        $school = $cycle->school;
        $cycle->delete();

        // Delete school if no other cycles reference it
        if ($school->cycles()->count() === 0) {
            $school->delete();
        }
    }

    public function cancelForm(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    private function resetForm(): void
    {
        $this->schoolName = '';
        $this->year = (string) date('Y');
        $this->instrumentName = '';
        $this->editingCycleId = null;
        $this->resetValidation();
    }

    // --- Import Methods ---

    public function openImport(int $cycleId): void
    {
        $this->showImport = true;
        $this->editingCycleId = $cycleId;
        $this->importFile = null;
        $this->importType = 'excel';
        $this->pdfPreviewText = null;
        $this->importMessage = null;
        $this->importSuccess = false;
    }

    public function cancelImport(): void
    {
        $this->showImport = false;
        $this->editingCycleId = null;
        $this->importFile = null;
        $this->pdfPreviewText = null;
        $this->importMessage = null;
        $this->importSuccess = false;
    }

    public function processImport(): void
    {
        if (! $this->importFile) {
            $this->addError('importFile', 'Pilih file terlebih dahulu.');
            return;
        }

        // Extend timeout for AI processing
        set_time_limit(0);

        $cycle = AccreditationCycle::findOrFail($this->editingCycleId);
        $extension = strtolower($this->importFile->getClientOriginalExtension());
        $fullPath = $this->importFile->getRealPath();

        $this->importMessage = null;
        $this->importSuccess = false;

        try {
            if ($this->importType === 'ai') {
                if ($extension !== 'pdf') {
                    $this->importSuccess = false;
                    $this->importMessage = 'Import AI hanya mendukung file PDF.';
                    return;
                }
                $service = new \App\Services\DeepSeekImportService();
                $result = $service->importFromPdf($fullPath, $cycle->instrument_id);
            } elseif (in_array($extension, ['xlsx', 'xls'])) {
                $service = new InstrumentImportService();
                $result = $service->importFromExcel($fullPath, $cycle->instrument_id);
            } elseif ($extension === 'pdf') {
                $service = new InstrumentImportService();
                $text = $service->extractFromPdf($fullPath);
                $this->pdfPreviewText = $text;
                $result = $service->parseFromPdfText($text, $cycle->instrument_id);
            } else {
                $this->importSuccess = false;
                $this->importMessage = 'Format file tidak didukung. Gunakan .xlsx atau .pdf';
                return;
            }

            $this->importSuccess = $result['success'];
            $this->importMessage = $result['message'];
        } catch (\Exception $e) {
            $this->importSuccess = false;
            $this->importMessage = 'Error: ' . $e->getMessage();
            \Illuminate\Support\Facades\Log::error('Import error', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }

        $this->importFile = null;
    }

    public function downloadTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->streamDownload(function () {
            $writer = new \OpenSpout\Writer\XLSX\Writer();
            $writer->openToFile('php://output');

            // Header row
            $header = new \OpenSpout\Common\Entity\Row([
                \OpenSpout\Common\Entity\Cell::fromValue('Komponen'),
                \OpenSpout\Common\Entity\Cell::fromValue('Butir'),
                \OpenSpout\Common\Entity\Cell::fromValue('Kode Indikator'),
                \OpenSpout\Common\Entity\Cell::fromValue('Judul Indikator'),
                \OpenSpout\Common\Entity\Cell::fromValue('Definisi'),
                \OpenSpout\Common\Entity\Cell::fromValue('Rubrik Kurang (1)'),
                \OpenSpout\Common\Entity\Cell::fromValue('Rubrik Cukup Baik (2)'),
                \OpenSpout\Common\Entity\Cell::fromValue('Rubrik Baik (3)'),
                \OpenSpout\Common\Entity\Cell::fromValue('Rubrik Sangat Baik (4)'),
                \OpenSpout\Common\Entity\Cell::fromValue('Boleh N/A (ya/tidak)'),
                \OpenSpout\Common\Entity\Cell::fromValue('Saran Bukti (pisah dengan ;)'),
            ]);
            $writer->addRow($header);

            // Example row
            $example = new \OpenSpout\Common\Entity\Row([
                \OpenSpout\Common\Entity\Cell::fromValue('1'),
                \OpenSpout\Common\Entity\Cell::fromValue('1'),
                \OpenSpout\Common\Entity\Cell::fromValue('1.1.1'),
                \OpenSpout\Common\Entity\Cell::fromValue('Interaksi guru dengan murid yang setara dan menghargai'),
                \OpenSpout\Common\Entity\Cell::fromValue('Kinerja guru dalam berinteraksi dengan murid...'),
                \OpenSpout\Common\Entity\Cell::fromValue('Guru mengabaikan atau merendahkan murid'),
                \OpenSpout\Common\Entity\Cell::fromValue('Guru mendengar sepintas dan menanggapi seperlunya'),
                \OpenSpout\Common\Entity\Cell::fromValue('Guru mendengarkan dengan saksama dan menanggapi relevan'),
                \OpenSpout\Common\Entity\Cell::fromValue('Guru mendengarkan, menggali lebih lanjut, membangun semangat'),
                \OpenSpout\Common\Entity\Cell::fromValue('tidak'),
                \OpenSpout\Common\Entity\Cell::fromValue('Hasil observasi;Hasil wawancara murid;Bukti lain'),
            ]);
            $writer->addRow($example);

            $writer->close();
        }, 'template_instrumen_akreditasi.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Convert average score (1-4) to final score (0-100).
     * Scale: 1 = 25, 2 = 50, 3 = 75, 4 = 100
     */
    private function convertToHundredScale(float $avgScore): float
    {
        return round(($avgScore / 4) * 100, 2);
    }

    /**
     * Determine accreditation grade based on final score (0-100).
     */
    private function getPeringkat(float $finalScore): array
    {
        return match (true) {
            $finalScore >= 91 => ['label' => 'A (Unggul)', 'color' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
            $finalScore >= 81 => ['label' => 'B (Baik)', 'color' => 'bg-blue-50 text-blue-700 border-blue-200'],
            $finalScore >= 71 => ['label' => 'C (Cukup)', 'color' => 'bg-amber-50 text-amber-700 border-amber-200'],
            default => ['label' => 'Tidak Terakreditasi', 'color' => 'bg-red-50 text-red-700 border-red-200'],
        };
    }

    public function render()
    {
        $cycles = AccreditationCycle::with(['school', 'instrument'])->latest()->get();
        $instrument = AccreditationInstrument::where('is_active', true)->first();

        $cyclesWithProgress = $cycles->map(function ($cycle) {
            $totalIndicators = AccreditationIndicator::whereHas('item.component', fn ($q) => $q->where('instrument_id', $cycle->instrument_id))->count();

            $scores = AccreditationIndicatorScore::where('cycle_id', $cycle->id)->get();

            // Filled = has rubric_id or is_na
            $filled = $scores->filter(fn ($s) => $s->rubric_id !== null || $s->is_na)->count();

            // Progress percent (how many indicators have been scored)
            $cycle->progress_percent = $totalIndicators > 0
                ? round(($filled / $totalIndicators) * 100, 1)
                : 0;

            // Kelengkapan: lengkap if ALL indicators are filled
            $cycle->is_lengkap = $totalIndicators > 0 && $filled >= $totalIndicators;

            // Average score (1-4) from non-NA indicators that have a score
            $scoredEntries = $scores->filter(fn ($s) => ! $s->is_na && $s->score_value !== null);
            $avgScore = $scoredEntries->isNotEmpty() ? $scoredEntries->avg('score_value') : 0;

            // Convert to 100 scale
            $cycle->final_score = $this->convertToHundredScale($avgScore);
            $cycle->avg_score = round($avgScore, 2);
            $cycle->peringkat = $this->getPeringkat($cycle->final_score);
            $cycle->filled_count = $filled;
            $cycle->total_indicators = $totalIndicators;

            return $cycle;
        });

        return view('livewire.accreditation.index', [
            'cycles' => $cyclesWithProgress,
            'instrument' => $instrument,
        ]);
    }
}
