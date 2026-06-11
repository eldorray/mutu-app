<?php

namespace App\Livewire\Accreditation;

use App\Models\AccreditationCycle;
use App\Models\AccreditationIndicator;
use App\Models\AccreditationIndicatorScore;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Monitoring Akreditasi')]
class Monitoring extends Component
{
    public AccreditationCycle $cycle;

    public function mount(AccreditationCycle $cycle): void
    {
        $this->cycle = $cycle->loadMissing(['school', 'instrument']);
    }

    public function render()
    {
        $indicators = AccreditationIndicator::with(['item.component', 'rubrics.level'])
            ->whereHas('item.component', fn ($q) => $q->where('instrument_id', $this->cycle->instrument_id))
            ->orderBy('sort_order')
            ->get();

        $scores = AccreditationIndicatorScore::where('cycle_id', $this->cycle->id)
            ->with('rubric.level')
            ->get()
            ->keyBy('indicator_id');

        $totalIndicators = $indicators->count();
        $filled = $scores->filter(fn ($s) => $s->rubric_id !== null || $s->is_na)->count();
        $scoredEntries = $scores->filter(fn ($s) => ! $s->is_na && $s->score_value !== null);
        $avgScore = $scoredEntries->isNotEmpty() ? $scoredEntries->avg('score_value') : 0;
        $finalScore = round(($avgScore / 4) * 100, 2);

        $peringkat = match (true) {
            $finalScore >= 91 => ['label' => 'A (Unggul)', 'color' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
            $finalScore >= 81 => ['label' => 'B (Baik)', 'color' => 'bg-blue-50 text-blue-700 border-blue-200'],
            $finalScore >= 71 => ['label' => 'C (Cukup)', 'color' => 'bg-amber-50 text-amber-700 border-amber-200'],
            default => ['label' => 'Tidak Terakreditasi', 'color' => 'bg-red-50 text-red-700 border-red-200'],
        };

        return view('livewire.accreditation.monitoring', [
            'indicators' => $indicators,
            'scores' => $scores,
            'totalIndicators' => $totalIndicators,
            'filled' => $filled,
            'avgScore' => round($avgScore, 2),
            'finalScore' => $finalScore,
            'peringkat' => $peringkat,
            'progressPercent' => $totalIndicators > 0 ? round(($filled / $totalIndicators) * 100, 1) : 0,
        ]);
    }
}
