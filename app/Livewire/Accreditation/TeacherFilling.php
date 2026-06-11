<?php

namespace App\Livewire\Accreditation;

use App\Models\AccreditationCycle;
use App\Models\AccreditationEvidence;
use App\Models\AccreditationEvidenceLink;
use App\Models\AccreditationIndicator;
use App\Models\AccreditationIndicatorScore;
use App\Models\AccreditationRubric;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
#[Title('Pengisian Akreditasi')]
class TeacherFilling extends Component
{
    use WithFileUploads;

    public AccreditationCycle $cycle;
    public ?int $selectedIndicatorId = null;
    public ?int $selectedRubricId = null;
    public string $checklistStatus = 'belum_diisi';
    public bool $isNa = false;
    public ?string $teacherNote = null;
    public ?string $evidenceTitle = null;
    public ?string $externalUrl = null;
    public $file;
    public ?int $evidenceTypeId = null;

    // Edit evidence state
    public ?int $editingEvidenceId = null;

    // Edit indicator state
    public bool $showEditIndicator = false;
    public string $editIndCode = '';
    public string $editIndTitle = '';
    public string $editIndDefinition = '';
    public bool $editIndNa = false;
    public string $editRubricKurang = '';
    public string $editRubricCukupBaik = '';
    public string $editRubricBaik = '';
    public string $editRubricSangatBaik = '';

    // Bulk delete state
    public array $selectedIndicators = [];
    public bool $selectAll = false;

    public function mount(AccreditationCycle $cycle): void
    {
        $this->cycle = $cycle->loadMissing(['school', 'scores']);
    }

    public function selectIndicator(int $indicatorId): void
    {
        $this->selectedIndicatorId = $indicatorId;

        $score = AccreditationIndicatorScore::where('cycle_id', $this->cycle->id)
            ->where('indicator_id', $indicatorId)
            ->first();

        $this->selectedRubricId = $score?->rubric_id;
        $this->checklistStatus = $score?->checklist_status ?? 'belum_diisi';
        $this->isNa = (bool) ($score?->is_na ?? false);
        $this->teacherNote = $score?->teacher_note;
        $this->resetValidation();
    }

    public function saveScore(): void
    {
        $indicator = AccreditationIndicator::findOrFail($this->selectedIndicatorId);
        $rubric = $this->selectedRubricId ? AccreditationRubric::with('level')->findOrFail($this->selectedRubricId) : null;

        if ($this->isNa && ! $indicator->is_na_allowed) {
            $this->addError('isNa', 'Indikator ini tidak memperbolehkan N/A.');
            return;
        }

        // Auto-set status to 'lengkap' if rubric is selected and status is still default
        $finalStatus = $this->checklistStatus;
        if (! $this->isNa && $rubric && $finalStatus === 'belum_diisi') {
            $finalStatus = 'lengkap';
        }

        AccreditationIndicatorScore::updateOrCreate(
            [
                'cycle_id' => $this->cycle->id,
                'indicator_id' => $indicator->id,
            ],
            [
                'rubric_id' => $this->isNa ? null : $rubric?->id,
                'assessed_by' => auth()->id(),
                'checklist_status' => $this->isNa ? 'na' : $finalStatus,
                'is_na' => $this->isNa,
                'rubric_context' => $rubric?->context,
                'score_value' => $this->isNa ? null : $rubric?->level?->score_value,
                'teacher_note' => $this->teacherNote,
            ]
        );

        // Update local state to reflect the saved status
        $this->checklistStatus = $this->isNa ? 'na' : $finalStatus;

        session()->flash('success', 'Nilai dan keterangan berhasil disimpan.');
    }

    public function uploadEvidence(): void
    {
        $this->validate([
            'selectedIndicatorId' => ['required', 'exists:accreditation_indicators,id'],
            'evidenceTitle' => ['required', 'string', 'max:255'],
            'file' => ['nullable', 'file', 'max:10240'],
            'externalUrl' => ['nullable', 'url'],
            'evidenceTypeId' => ['required', 'exists:accreditation_evidence_types,id'],
        ]);

        $path = $this->file ? $this->file->store('akreditasi/evidences', 'public') : null;

        $evidence = AccreditationEvidence::create([
            'cycle_id' => $this->cycle->id,
            'evidence_type_id' => $this->evidenceTypeId,
            'uploaded_by' => auth()->id(),
            'title' => $this->evidenceTitle,
            'file_path' => $path,
            'external_url' => $this->externalUrl,
            'verification_status' => 'pending',
        ]);

        AccreditationEvidenceLink::create([
            'evidence_id' => $evidence->id,
            'indicator_id' => $this->selectedIndicatorId,
        ]);

        $this->reset(['evidenceTitle', 'externalUrl', 'file', 'evidenceTypeId']);
        session()->flash('evidence-success', 'Bukti berhasil diunggah.');
    }

    public function editEvidence(int $evidenceId): void
    {
        $evidence = AccreditationEvidence::findOrFail($evidenceId);
        $this->editingEvidenceId = $evidence->id;
        $this->evidenceTitle = $evidence->title;
        $this->evidenceTypeId = $evidence->evidence_type_id;
        $this->externalUrl = $evidence->external_url;
    }

    public function updateEvidence(): void
    {
        $this->validate([
            'evidenceTitle' => ['required', 'string', 'max:255'],
            'externalUrl' => ['nullable', 'url'],
            'evidenceTypeId' => ['required', 'exists:accreditation_evidence_types,id'],
            'file' => ['nullable', 'file', 'max:10240'],
        ]);

        $evidence = AccreditationEvidence::findOrFail($this->editingEvidenceId);

        $data = [
            'title' => $this->evidenceTitle,
            'evidence_type_id' => $this->evidenceTypeId,
            'external_url' => $this->externalUrl,
        ];

        // Replace file if new one uploaded
        if ($this->file) {
            // Delete old file
            if ($evidence->file_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($evidence->file_path);
            }
            $data['file_path'] = $this->file->store('akreditasi/evidences', 'public');
        }

        $evidence->update($data);

        $this->cancelEditEvidence();
        session()->flash('evidence-success', 'Bukti berhasil diperbarui.');
    }

    public function cancelEditEvidence(): void
    {
        $this->editingEvidenceId = null;
        $this->reset(['evidenceTitle', 'externalUrl', 'file', 'evidenceTypeId']);
    }

    public function deleteEvidence(int $evidenceId): void
    {
        $evidence = AccreditationEvidence::findOrFail($evidenceId);

        // Delete file from storage
        if ($evidence->file_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($evidence->file_path);
        }

        // Delete links and evidence
        $evidence->links()->delete();
        $evidence->delete();

        session()->flash('evidence-success', 'Bukti berhasil dihapus.');
    }

    // --- Indicator Edit/Delete ---

    public function editIndicator(int $id): void
    {
        $indicator = AccreditationIndicator::with('rubrics.level')->findOrFail($id);

        $this->selectedIndicatorId = $id;
        $this->showEditIndicator = true;
        $this->editIndCode = $indicator->code;
        $this->editIndTitle = $indicator->title;
        $this->editIndDefinition = $indicator->definition ?? '';
        $this->editIndNa = $indicator->is_na_allowed;

        $this->editRubricKurang = '';
        $this->editRubricCukupBaik = '';
        $this->editRubricBaik = '';
        $this->editRubricSangatBaik = '';

        foreach ($indicator->rubrics as $rubric) {
            match ($rubric->level->code) {
                'kurang' => $this->editRubricKurang = $rubric->description,
                'cukup_baik' => $this->editRubricCukupBaik = $rubric->description,
                'baik' => $this->editRubricBaik = $rubric->description,
                'sangat_baik' => $this->editRubricSangatBaik = $rubric->description,
                default => null,
            };
        }
    }

    public function updateIndicator(): void
    {
        $this->validate([
            'editIndCode' => ['required', 'string', 'max:20'],
            'editIndTitle' => ['required', 'string', 'max:500'],
        ]);

        $indicator = AccreditationIndicator::findOrFail($this->selectedIndicatorId);
        $indicator->update([
            'code' => $this->editIndCode,
            'title' => $this->editIndTitle,
            'definition' => $this->editIndDefinition ?: null,
            'is_na_allowed' => $this->editIndNa,
        ]);

        // Update rubrics
        $levels = \App\Models\AccreditationRubricLevel::pluck('id', 'code')->toArray();
        $rubricMap = [
            'kurang' => $this->editRubricKurang,
            'cukup_baik' => $this->editRubricCukupBaik,
            'baik' => $this->editRubricBaik,
            'sangat_baik' => $this->editRubricSangatBaik,
        ];

        foreach ($rubricMap as $levelCode => $description) {
            if (! empty($description) && isset($levels[$levelCode])) {
                AccreditationRubric::updateOrCreate(
                    ['indicator_id' => $indicator->id, 'rubric_level_id' => $levels[$levelCode], 'context' => null],
                    ['description' => $description]
                );
            }
        }

        $this->showEditIndicator = false;
        session()->flash('success', 'Indikator berhasil diperbarui.');
    }

    public function cancelEditIndicator(): void
    {
        $this->showEditIndicator = false;
    }

    public function deleteIndicator(int $id): void
    {
        $indicator = AccreditationIndicator::findOrFail($id);
        $indicator->rubrics()->delete();
        $indicator->evidenceSuggestions()->delete();
        $indicator->scores()->delete();
        $indicator->delete();

        if ($this->selectedIndicatorId === $id) {
            $this->selectedIndicatorId = null;
        }

        $this->selectedIndicators = array_values(array_diff($this->selectedIndicators, [$id]));

        session()->flash('success', 'Indikator berhasil dihapus.');
    }

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $this->selectedIndicators = AccreditationIndicator::whereHas('item.component', fn ($q) => $q->where('instrument_id', $this->cycle->instrument_id))
                ->orderBy('sort_order')->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        } else {
            $this->selectedIndicators = [];
        }
    }

    public function bulkDelete(): void
    {
        if (empty($this->selectedIndicators)) {
            return;
        }

        $indicators = AccreditationIndicator::whereIn('id', $this->selectedIndicators)->get();

        foreach ($indicators as $indicator) {
            $indicator->rubrics()->delete();
            $indicator->evidenceSuggestions()->delete();
            $indicator->scores()->delete();
            $indicator->delete();
        }

        if (in_array($this->selectedIndicatorId, $this->selectedIndicators)) {
            $this->selectedIndicatorId = null;
        }

        $count = count($this->selectedIndicators);
        $this->selectedIndicators = [];
        $this->selectAll = false;

        session()->flash('success', "$count indikator berhasil dihapus.");
    }

    public function render()
    {
        // Reload scores so the sidebar badges reflect latest state
        $this->cycle->load('scores');

        $indicators = AccreditationIndicator::with(['item.component', 'rubrics.level', 'evidenceSuggestions'])
            ->whereHas('item.component', fn ($q) => $q->where('instrument_id', $this->cycle->instrument_id))
            ->orderBy('sort_order')
            ->get();

        $evidenceTypes = \App\Models\AccreditationEvidenceType::all();

        $currentIndicator = $this->selectedIndicatorId
            ? $indicators->firstWhere('id', $this->selectedIndicatorId)
            : null;

        $existingEvidences = $this->selectedIndicatorId
            ? AccreditationEvidenceLink::where('indicator_id', $this->selectedIndicatorId)
                ->whereHas('evidence', fn ($q) => $q->where('cycle_id', $this->cycle->id))
                ->with('evidence.evidenceType')
                ->get()
            : collect();

        return view('livewire.accreditation.teacher-filling', [
            'indicators' => $indicators,
            'evidenceTypes' => $evidenceTypes,
            'currentIndicator' => $currentIndicator,
            'existingEvidences' => $existingEvidences,
        ]);
    }
}
