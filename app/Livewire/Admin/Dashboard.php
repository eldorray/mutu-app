<?php

namespace App\Livewire\Admin;

use App\Models\AccreditationComponent;
use App\Models\AccreditationCycle;
use App\Models\AccreditationIndicator;
use App\Models\AccreditationIndicatorScore;
use App\Models\AccreditationInstrument;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    #[Computed]
    public function stats(): array
    {
        $activeCycle = AccreditationCycle::latest()->first();
        $totalIndicators = 0;
        $filled = 0;
        $avgScore = 0;
        $finalScore = 0;

        if ($activeCycle) {
            $totalIndicators = AccreditationIndicator::whereHas('item.component', fn ($q) => $q->where('instrument_id', $activeCycle->instrument_id))->count();
            $scores = AccreditationIndicatorScore::where('cycle_id', $activeCycle->id)->get();
            $filled = $scores->filter(fn ($s) => $s->rubric_id !== null || $s->is_na)->count();
            $scored = $scores->filter(fn ($s) => ! $s->is_na && $s->score_value !== null);
            $avgScore = $scored->isNotEmpty() ? round($scored->avg('score_value'), 2) : 0;
            $finalScore = round(($avgScore / 4) * 100, 2);
        }

        $totalCycles = AccreditationCycle::count();
        $progressPercent = $totalIndicators > 0 ? round(($filled / $totalIndicators) * 100, 1) : 0;

        return [
            'total_indicators' => $totalIndicators,
            'total_cycles' => $totalCycles,
            'filled' => $filled,
            'progress_percent' => $progressPercent,
            'avg_score' => $avgScore,
            'final_score' => $finalScore,
        ];
    }

    #[Computed]
    public function peringkat(): array
    {
        $score = $this->stats['final_score'];

        return match (true) {
            $score >= 91 => ['label' => 'A (Unggul)', 'color' => 'text-emerald-700', 'bg' => 'bg-emerald-50 border-emerald-200'],
            $score >= 81 => ['label' => 'B (Baik)', 'color' => 'text-blue-700', 'bg' => 'bg-blue-50 border-blue-200'],
            $score >= 71 => ['label' => 'C (Cukup)', 'color' => 'text-amber-700', 'bg' => 'bg-amber-50 border-amber-200'],
            default => ['label' => 'Tidak Terakreditasi', 'color' => 'text-red-700', 'bg' => 'bg-red-50 border-red-200'],
        };
    }

    #[Computed]
    public function componentProgress(): array
    {
        $activeCycle = AccreditationCycle::latest()->first();
        if (! $activeCycle) {
            return [];
        }

        $components = AccreditationComponent::with(['items.indicators'])->orderBy('sort_order')->get();
        $scores = AccreditationIndicatorScore::where('cycle_id', $activeCycle->id)->get()->keyBy('indicator_id');

        return $components->map(function ($component) use ($scores) {
            $indicatorIds = $component->items->flatMap(fn ($item) => $item->indicators->pluck('id'));
            $total = $indicatorIds->count();
            $filled = $indicatorIds->filter(fn ($id) => isset($scores[$id]) && ($scores[$id]->rubric_id || $scores[$id]->is_na))->count();
            $scored = $indicatorIds
                ->map(fn ($id) => $scores[$id] ?? null)
                ->filter(fn ($s) => $s && ! $s->is_na && $s->score_value !== null);
            $avg = $scored->isNotEmpty() ? round($scored->avg('score_value'), 2) : 0;

            return [
                'number' => $component->number,
                'name' => $component->name,
                'total' => $total,
                'filled' => $filled,
                'percent' => $total > 0 ? round(($filled / $total) * 100, 1) : 0,
                'avg_score' => $avg,
            ];
        })->toArray();
    }

    #[Computed]
    public function recentScores(): array
    {
        $activeCycle = AccreditationCycle::latest()->first();
        if (! $activeCycle) {
            return [];
        }

        return AccreditationIndicatorScore::where('cycle_id', $activeCycle->id)
            ->whereNotNull('rubric_id')
            ->with(['indicator', 'rubric.level'])
            ->latest('updated_at')
            ->take(8)
            ->get()
            ->map(fn ($s) => [
                'code' => $s->indicator->code,
                'title' => $s->indicator->title,
                'score' => $s->score_value,
                'label' => $s->rubric->level->label,
                'status' => $s->checklist_status,
                'date' => $s->updated_at->diffForHumans(),
            ])
            ->toArray();
    }

    #[Computed]
    public function activeCycle(): ?AccreditationCycle
    {
        return AccreditationCycle::with(['school', 'instrument'])->latest()->first();
    }

    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}
