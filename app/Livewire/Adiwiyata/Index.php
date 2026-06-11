<?php

namespace App\Livewire\Adiwiyata;

use App\Models\AdiwiyataCycle;
use App\Models\AdiwiyataIndicator;
use App\Models\AdiwiyataIndicatorAnswer;
use App\Models\AdiwiyataInstrument;
use App\Models\School;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Adiwiyata')]
class Index extends Component
{
    // Form fields
    public string $schoolName = '';
    public string $year = '';
    public string $status = 'draft';
    public string $awardLevel = '';

    // Edit state
    public bool $showForm = false;
    public ?int $editingCycleId = null;

    public const STATUSES = [
        'draft' => 'Draft',
        'berjalan' => 'Sedang Berjalan',
        'selesai' => 'Selesai',
    ];

    public const AWARD_LEVELS = [
        'calon' => 'Calon Sekolah Adiwiyata',
        'kabupaten' => 'Adiwiyata Kabupaten/Kota',
        'provinsi' => 'Adiwiyata Provinsi',
        'nasional' => 'Adiwiyata Nasional',
        'mandiri' => 'Adiwiyata Mandiri',
    ];

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
        $cycle = AdiwiyataCycle::with('school')->findOrFail($cycleId);
        $this->editingCycleId = $cycle->id;
        $this->schoolName = $cycle->school->name;
        $this->year = (string) $cycle->year;
        $this->status = $cycle->status;
        $this->awardLevel = (string) $cycle->award_level;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'schoolName' => ['required', 'string', 'max:255'],
            'year' => ['required', 'digits:4', 'integer', 'min:2020', 'max:2099'],
            'status' => ['required', 'string', 'in:' . implode(',', array_keys(self::STATUSES))],
            'awardLevel' => ['nullable', 'string', 'in:' . implode(',', array_keys(self::AWARD_LEVELS))],
        ]);

        $instrument = AdiwiyataInstrument::where('is_active', true)->first();

        if (! $instrument) {
            $this->addError('schoolName', 'Instrumen Adiwiyata aktif belum tersedia. Jalankan seeder terlebih dahulu.');

            return;
        }

        if ($this->editingCycleId) {
            $cycle = AdiwiyataCycle::findOrFail($this->editingCycleId);
            $cycle->school->update(['name' => $this->schoolName]);
            $cycle->update([
                'year' => $this->year,
                'status' => $this->status,
                'award_level' => $this->awardLevel ?: null,
            ]);
        } else {
            $school = School::create(['name' => $this->schoolName]);

            AdiwiyataCycle::create([
                'school_id' => $school->id,
                'instrument_id' => $instrument->id,
                'year' => $this->year,
                'status' => $this->status,
                'award_level' => $this->awardLevel ?: null,
            ]);
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function delete(int $cycleId): void
    {
        $cycle = AdiwiyataCycle::findOrFail($cycleId);
        $school = $cycle->school;
        $cycle->delete();

        // Delete school only if no other cycles (adiwiyata or accreditation) reference it.
        if ($school->adiwiyataCycles()->count() === 0 && $school->cycles()->count() === 0) {
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
        $this->status = 'draft';
        $this->awardLevel = '';
        $this->editingCycleId = null;
        $this->resetValidation();
    }

    public function render()
    {
        $cycles = AdiwiyataCycle::with(['school', 'instrument'])->latest()->get();

        $totalIndicators = AdiwiyataIndicator::query()
            ->when($cycles->isNotEmpty(), fn ($q) => $q->where('instrument_id', $cycles->first()->instrument_id))
            ->count();

        $filledByCycle = AdiwiyataIndicatorAnswer::query()
            ->whereIn('cycle_id', $cycles->pluck('id'))
            ->where('status', 'terisi')
            ->selectRaw('cycle_id, count(*) as total')
            ->groupBy('cycle_id')
            ->pluck('total', 'cycle_id');

        $cycles->each(function ($cycle) use ($filledByCycle, $totalIndicators) {
            $filled = (int) ($filledByCycle[$cycle->id] ?? 0);
            $cycle->filled_count = $filled;
            $cycle->total_indicators = $totalIndicators;
            $cycle->progress_percent = $totalIndicators > 0 ? round(($filled / $totalIndicators) * 100, 1) : 0;
        });

        return view('livewire.adiwiyata.index', [
            'cycles' => $cycles,
            'totalIndicators' => $totalIndicators,
            'statuses' => self::STATUSES,
            'awardLevels' => self::AWARD_LEVELS,
        ]);
    }
}
