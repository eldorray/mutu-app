<?php

namespace App\Livewire\Adiwiyata;

use App\Models\AdiwiyataComponent;
use App\Models\AdiwiyataCycle;
use App\Models\AdiwiyataEvidence;
use App\Models\AdiwiyataIndicator;
use App\Models\AdiwiyataIndicatorAnswer;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
#[Title('Pengisian Adiwiyata')]
class Filling extends Component
{
    use WithFileUploads;

    public AdiwiyataCycle $cycle;
    public ?int $selectedIndicatorId = null;

    // Answer form
    public string $note = '';
    public array $checkedEvidences = [];
    public $valueNumber = null;
    public $valueNumerator = null;
    public $valueDenominator = null;

    // Evidence upload form
    public string $evidenceType = 'dokumen';
    public string $evidenceTitle = '';
    public ?string $evidenceUrl = null;
    public $file = null;

    public const EVIDENCE_TYPES = [
        'dokumen' => 'Dokumen',
        'foto' => 'Foto',
        'video' => 'Video',
        'tautan' => 'Tautan',
    ];

    public function mount(AdiwiyataCycle $cycle): void
    {
        $this->cycle = $cycle->loadMissing('school');
    }

    public function selectIndicator(int $indicatorId): void
    {
        $this->selectedIndicatorId = $indicatorId;

        $answer = AdiwiyataIndicatorAnswer::where('cycle_id', $this->cycle->id)
            ->where('indicator_id', $indicatorId)
            ->first();

        $this->note = $answer?->note ?? '';
        $this->checkedEvidences = array_map('strval', $answer?->checked_evidences ?? []);
        $this->valueNumber = $answer?->value_number;
        $this->valueNumerator = $answer?->value_numerator;
        $this->valueDenominator = $answer?->value_denominator;

        $this->resetEvidenceForm();
        $this->resetValidation();
    }

    public function saveAnswer(): void
    {
        $indicator = AdiwiyataIndicator::findOrFail($this->selectedIndicatorId);

        $rules = ['note' => ['required', 'string']];
        if ($indicator->scoring_method === 'count') {
            $rules['valueNumber'] = ['required', 'integer', 'min:0'];
        } elseif ($indicator->scoring_method === 'percentage') {
            $rules['valueNumerator'] = ['required', 'integer', 'min:0'];
            $rules['valueDenominator'] = ['required', 'integer', 'min:1'];
        }

        $this->validate($rules, [
            'note.required' => 'Catatan wajib diisi.',
            'valueNumber.required' => 'Jumlah wajib diisi.',
            'valueNumerator.required' => 'Pembilang wajib diisi.',
            'valueDenominator.required' => 'Penyebut wajib diisi.',
            'valueDenominator.min' => 'Penyebut harus lebih dari 0.',
        ]);

        $percentage = null;
        if ($indicator->scoring_method === 'percentage') {
            $percentage = round(((int) $this->valueNumerator / (int) $this->valueDenominator) * 100, 2);
        }

        AdiwiyataIndicatorAnswer::updateOrCreate(
            ['cycle_id' => $this->cycle->id, 'indicator_id' => $indicator->id],
            [
                'filled_by' => auth()->id(),
                'note' => $this->note,
                'checked_evidences' => $indicator->scoring_method === 'checklist'
                    ? array_values(array_map('intval', $this->checkedEvidences))
                    : null,
                'value_number' => $indicator->scoring_method === 'count' ? (int) $this->valueNumber : null,
                'value_numerator' => $indicator->scoring_method === 'percentage' ? (int) $this->valueNumerator : null,
                'value_denominator' => $indicator->scoring_method === 'percentage' ? (int) $this->valueDenominator : null,
                'value_percentage' => $percentage,
                'status' => 'terisi',
            ]
        );

        session()->flash('success', 'Jawaban dan catatan berhasil disimpan.');
    }

    public function uploadEvidence(): void
    {
        $this->validate([
            'selectedIndicatorId' => ['required'],
            'evidenceType' => ['required', 'in:' . implode(',', array_keys(self::EVIDENCE_TYPES))],
            'evidenceTitle' => ['required', 'string', 'max:255'],
            'file' => ['nullable', 'file', 'max:10240'],
            'evidenceUrl' => ['nullable', 'url'],
        ]);

        $path = $this->file ? $this->file->store('adiwiyata/evidences', 'public') : null;

        AdiwiyataEvidence::create([
            'cycle_id' => $this->cycle->id,
            'indicator_id' => $this->selectedIndicatorId,
            'uploaded_by' => auth()->id(),
            'type' => $this->evidenceType,
            'title' => $this->evidenceTitle,
            'file_path' => $path,
            'external_url' => $this->evidenceUrl ?: null,
        ]);

        $this->resetEvidenceForm();
        session()->flash('evidence-success', 'Bukti berhasil diunggah.');
    }

    public function deleteEvidence(int $evidenceId): void
    {
        $evidence = AdiwiyataEvidence::where('cycle_id', $this->cycle->id)->findOrFail($evidenceId);

        if ($evidence->file_path) {
            Storage::disk('public')->delete($evidence->file_path);
        }
        $evidence->delete();

        session()->flash('evidence-success', 'Bukti berhasil dihapus.');
    }

    private function resetEvidenceForm(): void
    {
        $this->evidenceType = 'dokumen';
        $this->evidenceTitle = '';
        $this->evidenceUrl = null;
        $this->file = null;
    }

    public function render()
    {
        $indicators = AdiwiyataIndicator::with('evidences')
            ->where('instrument_id', $this->cycle->instrument_id)
            ->orderBy('sort_order')
            ->get();

        // Group indicators by component (with a "Tanpa Komponen" bucket for unassigned).
        $components = AdiwiyataComponent::where('instrument_id', $this->cycle->instrument_id)
            ->orderBy('sort_order')->orderBy('number')->get();

        $groups = collect();
        if ($components->isEmpty()) {
            $groups->push(['name' => null, 'indicators' => $indicators]);
        } else {
            foreach ($components as $component) {
                $items = $indicators->where('component_id', $component->id)->values();
                if ($items->isNotEmpty()) {
                    $groups->push(['name' => $component->name, 'indicators' => $items]);
                }
            }
            $uncategorized = $indicators->whereNull('component_id')->values();
            if ($uncategorized->isNotEmpty()) {
                $groups->push(['name' => 'Tanpa Komponen', 'indicators' => $uncategorized]);
            }
        }

        $answers = AdiwiyataIndicatorAnswer::where('cycle_id', $this->cycle->id)
            ->get()
            ->keyBy('indicator_id');

        $currentIndicator = $this->selectedIndicatorId
            ? $indicators->firstWhere('id', $this->selectedIndicatorId)
            : null;

        $uploadedEvidences = $this->selectedIndicatorId
            ? AdiwiyataEvidence::where('cycle_id', $this->cycle->id)
                ->where('indicator_id', $this->selectedIndicatorId)
                ->latest()
                ->get()
            : collect();

        $filledCount = $answers->where('status', 'terisi')->count();
        $totalCount = $indicators->count();

        return view('livewire.adiwiyata.filling', [
            'indicators' => $indicators,
            'groups' => $groups,
            'answers' => $answers,
            'currentIndicator' => $currentIndicator,
            'uploadedEvidences' => $uploadedEvidences,
            'evidenceTypes' => self::EVIDENCE_TYPES,
            'filledCount' => $filledCount,
            'totalCount' => $totalCount,
            'progressPercent' => $totalCount > 0 ? round(($filledCount / $totalCount) * 100, 1) : 0,
        ]);
    }
}
