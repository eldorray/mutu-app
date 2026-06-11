<?php

namespace App\Livewire\Accreditation;

use App\Models\AccreditationComponent;
use App\Models\AccreditationCycle;
use App\Models\AccreditationIndicator;
use App\Models\AccreditationItem;
use App\Models\AccreditationRubric;
use App\Models\AccreditationRubricLevel;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Daftar Indikator')]
class IndicatorList extends Component
{
    public AccreditationCycle $cycle;

    // Edit form state
    public bool $showForm = false;

    public ?int $editingId = null;

    public string $code = '';

    public string $title = '';

    public string $definition = '';

    public bool $isNaAllowed = false;

    public string $rubricKurang = '';

    public string $rubricCukupBaik = '';

    public string $rubricBaik = '';

    public string $rubricSangatBaik = '';

    // Filter
    public string $search = '';

    public ?int $filterComponent = null;

    public function mount(AccreditationCycle $cycle): void
    {
        $this->cycle = $cycle->loadMissing(['school', 'instrument']);
    }

    public function createIndicator(): void
    {
        $this->cancelForm();
        $this->showForm = true;
    }

    public function storeIndicator(): void
    {
        $this->validate([
            'code' => ['required', 'string', 'max:20'],
            'title' => ['required', 'string', 'max:500'],
        ]);

        // Derive component & item number from the code (e.g. "1.2.3" => komponen 1, butir 2).
        $parts = explode('.', trim($this->code));
        $compNum = (int) ($parts[0] ?? 1) ?: 1;
        $itemNum = (int) ($parts[1] ?? 1) ?: 1;

        $component = AccreditationComponent::firstOrCreate(
            ['instrument_id' => $this->cycle->instrument_id, 'number' => $compNum],
            ['name' => "Komponen $compNum", 'sort_order' => $compNum]
        );

        $item = AccreditationItem::firstOrCreate(
            ['component_id' => $component->id, 'number' => $itemNum],
            ['title' => "Butir $itemNum", 'sort_order' => $itemNum]
        );

        // Prevent duplicate code within the same item (matches the DB unique constraint).
        $exists = AccreditationIndicator::where('item_id', $item->id)
            ->where('code', $this->code)
            ->exists();

        if ($exists) {
            $this->addError('code', 'Kode indikator sudah digunakan pada butir ini.');

            return;
        }

        $indicator = AccreditationIndicator::create([
            'item_id' => $item->id,
            'code' => $this->code,
            'title' => $this->title,
            'definition' => $this->definition ?: null,
            'is_na_allowed' => $this->isNaAllowed,
            'is_contextual' => false,
            'sort_order' => (AccreditationIndicator::max('sort_order') ?? 0) + 1,
        ]);

        $this->saveRubrics($indicator);

        $this->cancelForm();
        session()->flash('success', 'Indikator berhasil ditambahkan.');
    }

    public function editIndicator(int $id): void
    {
        $indicator = AccreditationIndicator::with('rubrics.level')->findOrFail($id);

        $this->editingId = $indicator->id;
        $this->code = $indicator->code;
        $this->title = $indicator->title;
        $this->definition = $indicator->definition ?? '';
        $this->isNaAllowed = $indicator->is_na_allowed;

        // Load rubrics
        foreach ($indicator->rubrics as $rubric) {
            match ($rubric->level->code) {
                'kurang' => $this->rubricKurang = $rubric->description,
                'cukup_baik' => $this->rubricCukupBaik = $rubric->description,
                'baik' => $this->rubricBaik = $rubric->description,
                'sangat_baik' => $this->rubricSangatBaik = $rubric->description,
                default => null,
            };
        }

        $this->showForm = true;
    }

    public function updateIndicator(): void
    {
        $this->validate([
            'code' => ['required', 'string', 'max:20'],
            'title' => ['required', 'string', 'max:500'],
        ]);

        $indicator = AccreditationIndicator::findOrFail($this->editingId);

        $indicator->update([
            'code' => $this->code,
            'title' => $this->title,
            'definition' => $this->definition ?: null,
            'is_na_allowed' => $this->isNaAllowed,
        ]);

        $this->saveRubrics($indicator);

        $this->cancelForm();
        session()->flash('success', 'Indikator berhasil diperbarui.');
    }

    /**
     * Sync the four rubric levels for an indicator from the form fields.
     * Empty descriptions remove the corresponding rubric.
     */
    private function saveRubrics(AccreditationIndicator $indicator): void
    {
        $levels = AccreditationRubricLevel::pluck('id', 'code')->toArray();
        $rubricMap = [
            'kurang' => $this->rubricKurang,
            'cukup_baik' => $this->rubricCukupBaik,
            'baik' => $this->rubricBaik,
            'sangat_baik' => $this->rubricSangatBaik,
        ];

        foreach ($rubricMap as $levelCode => $description) {
            if (! isset($levels[$levelCode])) {
                continue;
            }

            if (! empty($description)) {
                AccreditationRubric::updateOrCreate(
                    ['indicator_id' => $indicator->id, 'rubric_level_id' => $levels[$levelCode], 'context' => null],
                    ['description' => $description]
                );
            } else {
                AccreditationRubric::where('indicator_id', $indicator->id)
                    ->where('rubric_level_id', $levels[$levelCode])
                    ->whereNull('context')
                    ->delete();
            }
        }
    }

    public function deleteIndicator(int $id): void
    {
        $indicator = AccreditationIndicator::findOrFail($id);
        $indicator->rubrics()->delete();
        $indicator->evidenceSuggestions()->delete();
        $indicator->scores()->delete();
        $indicator->delete();

        session()->flash('success', 'Indikator berhasil dihapus.');
    }

    public function cancelForm(): void
    {
        $this->showForm = false;
        $this->editingId = null;
        $this->reset(['code', 'title', 'definition', 'isNaAllowed', 'rubricKurang', 'rubricCukupBaik', 'rubricBaik', 'rubricSangatBaik']);
        $this->resetValidation();
    }

    public function render()
    {
        $query = AccreditationIndicator::with(['item.component', 'rubrics.level'])
            ->whereHas('item.component', fn ($q) => $q->where('instrument_id', $this->cycle->instrument_id));

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('code', 'like', "%{$this->search}%")
                    ->orWhere('title', 'like', "%{$this->search}%");
            });
        }

        if ($this->filterComponent) {
            $query->whereHas('item.component', fn ($q) => $q->where('number', $this->filterComponent));
        }

        $indicators = $query->orderBy('sort_order')->get();

        $components = \App\Models\AccreditationComponent::where('instrument_id', $this->cycle->instrument_id)
            ->orderBy('number')
            ->get();

        return view('livewire.accreditation.indicator-list', [
            'indicators' => $indicators,
            'components' => $components,
        ]);
    }
}
