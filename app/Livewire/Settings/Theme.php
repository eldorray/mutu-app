<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.settings')]
#[Title('Theme')]
class Theme extends Component
{
    public string $theme = 'orange';

    /**
     * Available color themes.
     * Each entry maps to CSS variables applied via data-theme on <html>.
     */
    public array $themes = [
        ['key' => 'orange', 'label' => 'Sunset', 'swatch' => '#ff5e1e'],
        ['key' => 'emerald', 'label' => 'Emerald', 'swatch' => '#10b981'],
        ['key' => 'blue', 'label' => 'Ocean', 'swatch' => '#3b82f6'],
        ['key' => 'violet', 'label' => 'Violet', 'swatch' => '#8b5cf6'],
        ['key' => 'rose', 'label' => 'Rose', 'swatch' => '#f43f5e'],
        ['key' => 'amber', 'label' => 'Amber', 'swatch' => '#f59e0b'],
        ['key' => 'slate', 'label' => 'Slate', 'swatch' => '#475569'],
    ];

    public function mount(): void
    {
        $this->theme = Auth::user()->color_theme ?? 'orange';
    }

    public function setTheme(string $value): void
    {
        $valid = collect($this->themes)->pluck('key')->all();

        if (! in_array($value, $valid, true)) {
            return;
        }

        $this->theme = $value;

        $user = Auth::user();
        $user->color_theme = $value;
        $user->save();

        // Also save as site-wide theme for guest pages (login/register)
        file_put_contents(storage_path('app/site_theme.txt'), $value);

        $this->dispatch('theme-changed', value: $value);

        session()->flash('theme_status', 'Theme updated.');
    }

    public function render()
    {
        return view('livewire.settings.theme');
    }
}
