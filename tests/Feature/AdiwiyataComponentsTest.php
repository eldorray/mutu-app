<?php

use App\Livewire\Adiwiyata\Components;
use App\Models\AdiwiyataComponent;
use App\Models\AdiwiyataIndicator;
use App\Models\AdiwiyataInstrument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function componentsInstrument(): AdiwiyataInstrument
{
    $instrument = AdiwiyataInstrument::create([
        'code' => 'ADW-C', 'name' => 'Adiwiyata C', 'year' => 2025, 'is_active' => true,
    ]);

    foreach (range(1, 4) as $n) {
        AdiwiyataIndicator::create([
            'instrument_id' => $instrument->id, 'number' => $n, 'title' => "Indikator $n",
            'scoring_method' => 'checklist', 'sort_order' => $n,
        ]);
    }

    return $instrument;
}

function componentsUser(): User
{
    return User::create([
        'name' => 'Guru', 'email' => 'guru-c@example.com',
        'password' => bcrypt('password'), 'role' => 'guru',
    ]);
}

it('adds a component with auto-incrementing number', function () {
    componentsInstrument();
    $this->actingAs(componentsUser());

    Livewire::test(Components::class)
        ->set('componentName', 'Kebijakan')
        ->call('addComponent')
        ->assertHasNoErrors()
        ->set('componentName', 'Prasarana dan Sarana')
        ->call('addComponent');

    $components = AdiwiyataComponent::orderBy('number')->get();
    expect($components)->toHaveCount(2);
    expect($components[0]->number)->toBe(1);
    expect($components[1]->number)->toBe(2);
    expect($components[1]->name)->toBe('Prasarana dan Sarana');
});

it('requires a component name', function () {
    componentsInstrument();
    $this->actingAs(componentsUser());

    Livewire::test(Components::class)
        ->set('componentName', '')
        ->call('addComponent')
        ->assertHasErrors(['componentName']);
});

it('renames a component', function () {
    $instrument = componentsInstrument();
    $component = AdiwiyataComponent::create([
        'instrument_id' => $instrument->id, 'number' => 1, 'name' => 'Lama', 'sort_order' => 1,
    ]);
    $this->actingAs(componentsUser());

    Livewire::test(Components::class)
        ->call('editComponent', $component->id)
        ->set('editName', 'Baru')
        ->call('updateComponent')
        ->assertHasNoErrors();

    expect($component->fresh()->name)->toBe('Baru');
});

it('bulk-assigns indicators to a component', function () {
    $instrument = componentsInstrument();
    $component = AdiwiyataComponent::create([
        'instrument_id' => $instrument->id, 'number' => 1, 'name' => 'Kebijakan', 'sort_order' => 1,
    ]);
    $ids = AdiwiyataIndicator::whereIn('number', [1, 2])->pluck('id')->map(fn ($id) => (string) $id)->all();
    $this->actingAs(componentsUser());

    Livewire::test(Components::class)
        ->set('selectedIndicators', $ids)
        ->set('targetComponentId', (string) $component->id)
        ->call('bulkAssign')
        ->assertHasNoErrors();

    expect(AdiwiyataIndicator::where('component_id', $component->id)->count())->toBe(2);
    expect(AdiwiyataIndicator::whereNull('component_id')->count())->toBe(2);
});

it('bulk-removes indicators from a component when target is empty', function () {
    $instrument = componentsInstrument();
    $component = AdiwiyataComponent::create([
        'instrument_id' => $instrument->id, 'number' => 1, 'name' => 'Kebijakan', 'sort_order' => 1,
    ]);
    AdiwiyataIndicator::query()->update(['component_id' => $component->id]);
    $ids = AdiwiyataIndicator::pluck('id')->map(fn ($id) => (string) $id)->all();
    $this->actingAs(componentsUser());

    Livewire::test(Components::class)
        ->set('selectedIndicators', $ids)
        ->set('targetComponentId', '')
        ->call('bulkAssign')
        ->assertHasNoErrors();

    expect(AdiwiyataIndicator::whereNull('component_id')->count())->toBe(4);
});

it('errors on bulk-assign with no selection', function () {
    componentsInstrument();
    $this->actingAs(componentsUser());

    Livewire::test(Components::class)
        ->set('selectedIndicators', [])
        ->call('bulkAssign')
        ->assertHasErrors(['selectedIndicators']);
});

it('returns indicators to no-component when its component is deleted', function () {
    $instrument = componentsInstrument();
    $component = AdiwiyataComponent::create([
        'instrument_id' => $instrument->id, 'number' => 1, 'name' => 'Kebijakan', 'sort_order' => 1,
    ]);
    AdiwiyataIndicator::query()->update(['component_id' => $component->id]);
    $this->actingAs(componentsUser());

    Livewire::test(Components::class)->call('deleteComponent', $component->id);

    expect(AdiwiyataComponent::count())->toBe(0);
    expect(AdiwiyataIndicator::whereNull('component_id')->count())->toBe(4);
});
