<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.settings')]
#[Title('Appearance')]
class Appearance extends Component
{
    public string $appearance = 'system';

    public array $options = ['light', 'dark', 'system'];

    public function mount(): void
    {
        $this->appearance = Auth::user()->appearance ?? 'system';
    }

    public function setAppearance(string $value): void
    {
        if (! in_array($value, $this->options, true)) {
            return;
        }

        $this->appearance = $value;

        $user = Auth::user();
        $user->appearance = $value;
        $user->save();

        $this->dispatch('appearance-changed', value: $value);

        session()->flash('appearance_status', 'Appearance updated.');
    }

    public function render()
    {
        return view('livewire.settings.appearance');
    }
}