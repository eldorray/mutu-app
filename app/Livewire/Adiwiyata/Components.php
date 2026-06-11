<?php

namespace App\Livewire\Adiwiyata;

use App\Models\AdiwiyataComponent;
use App\Models\AdiwiyataIndicator;
use App\Models\AdiwiyataInstrument;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Kelola Komponen Adiwiyata')]
class Components extends Component
{
    // Component form
    public string $componentName = '';
    public ?int $editingComponentId = null;
    public string $editName = '';

    // Bulk assign
    public array $selectedIndicators = [];
    public bool $selectAll = false;
    public string $targetComponentId = '';

    public function addComponent(): void
    {
        $this->validate(['componentName' => ['required', 'string', 'max:255']]);

        $instrument = $this->instrument();
        if (! $instrument) {
            $this->addError('componentName', 'Instrumen Adiwiyata aktif belum tersedia.');

            return;
        }

        $next = (int) AdiwiyataComponent::where('instrument_id', $instrument->id)->max('number') + 1;

        AdiwiyataComponent::create([
            'instrument_id' => $instrument->id,
            'number' => $next,
            'name' => $this->componentName,
            'sort_order' => $next,
        ]);

        $this->componentName = '';
        session()->flash('success', 'Komponen berhasil ditambahkan.');
    }

    public function editComponent(int $id): void
    {
        $component = AdiwiyataComponent::findOrFail($id);
        $this->editingComponentId = $component->id;
        $this->editName = $component->name;
    }

    public function updateComponent(): void
    {
        $this->validate(['editName' => ['required', 'string', 'max:255']]);

        AdiwiyataComponent::findOrFail($this->editingComponentId)->update(['name' => $this->editName]);

        $this->cancelEdit();
        session()->flash('success', 'Komponen berhasil diperbarui.');
    }

    public function cancelEdit(): void
    {
        $this->editingComponentId = null;
        $this->editName = '';
        $this->resetValidation();
    }

    public function deleteComponent(int $id): void
    {
        // Indicators' component_id is set null automatically (nullOnDelete).
        AdiwiyataComponent::findOrFail($id)->delete();
        session()->flash('success', 'Komponen dihapus. Indikator terkait dikembalikan ke "Tanpa Komponen".');
    }

    public function updatedSelectAll(bool $value): void
    {
        $instrument = $this->instrument();
        $this->selectedIndicators = $value && $instrument
            ? AdiwiyataIndicator::where('instrument_id', $instrument->id)->orderBy('sort_order')
                ->pluck('id')->map(fn ($id) => (string) $id)->toArray()
            : [];
    }

    public function bulkAssign(): void
    {
        if (empty($this->selectedIndicators)) {
            $this->addError('selectedIndicators', 'Pilih minimal satu indikator.');

            return;
        }

        $componentId = $this->targetComponentId !== '' ? (int) $this->targetComponentId : null;

        AdiwiyataIndicator::whereIn('id', $this->selectedIndicators)
            ->update(['component_id' => $componentId]);

        $count = count($this->selectedIndicators);
        $this->selectedIndicators = [];
        $this->selectAll = false;

        $label = $componentId
            ? AdiwiyataComponent::find($componentId)?->name
            : 'Tanpa Komponen';

        session()->flash('success', "$count indikator dipindahkan ke \"$label\".");
    }

    private function instrument(): ?AdiwiyataInstrument
    {
        return AdiwiyataInstrument::where('is_active', true)->first();
    }

    public function render()
    {
        $instrument = $this->instrument();

        $components = $instrument
            ? AdiwiyataComponent::where('instrument_id', $instrument->id)
                ->withCount('indicators')->orderBy('sort_order')->orderBy('number')->get()
            : collect();

        $indicators = $instrument
            ? AdiwiyataIndicator::with('component')->where('instrument_id', $instrument->id)
                ->orderBy('sort_order')->get()
            : collect();

        return view('livewire.adiwiyata.components', [
            'instrument' => $instrument,
            'components' => $components,
            'indicators' => $indicators,
        ]);
    }
}
