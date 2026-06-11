<?php

use App\Livewire\Adiwiyata\Filling;
use App\Models\AdiwiyataCycle;
use App\Models\AdiwiyataEvidence;
use App\Models\AdiwiyataIndicator;
use App\Models\AdiwiyataIndicatorAnswer;
use App\Models\AdiwiyataInstrument;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function fillingCycle(): AdiwiyataCycle
{
    $instrument = AdiwiyataInstrument::create([
        'code' => 'ADW-TEST', 'name' => 'Adiwiyata Test', 'year' => 2025, 'is_active' => true,
    ]);

    // One indicator of each scoring method.
    $checklist = AdiwiyataIndicator::create([
        'instrument_id' => $instrument->id, 'number' => 1, 'title' => 'Checklist indicator',
        'scoring_method' => 'checklist', 'sort_order' => 1,
    ]);
    $checklist->evidences()->create(['name' => 'Bukti A', 'sort_order' => 1]);
    $checklist->evidences()->create(['name' => 'Bukti B', 'sort_order' => 2]);

    AdiwiyataIndicator::create([
        'instrument_id' => $instrument->id, 'number' => 2, 'title' => 'Count indicator',
        'scoring_method' => 'count', 'sort_order' => 2,
    ]);
    AdiwiyataIndicator::create([
        'instrument_id' => $instrument->id, 'number' => 3, 'title' => 'Percentage indicator',
        'scoring_method' => 'percentage', 'sort_order' => 3,
    ]);

    $school = School::create(['name' => 'SDN Test']);

    return AdiwiyataCycle::create([
        'school_id' => $school->id, 'instrument_id' => $instrument->id, 'year' => 2025, 'status' => 'berjalan',
    ]);
}

function fillingUser(string $role = 'guru'): User
{
    return User::create([
        'name' => ucfirst($role), 'email' => "$role@example.com",
        'password' => bcrypt('password'), 'role' => $role,
    ]);
}

it('requires a note for any indicator', function () {
    $cycle = fillingCycle();
    $indicator = $cycle->instrument->indicators()->where('number', 1)->first();
    $this->actingAs(fillingUser());

    Livewire::test(Filling::class, ['cycle' => $cycle])
        ->call('selectIndicator', $indicator->id)
        ->set('note', '')
        ->call('saveAnswer')
        ->assertHasErrors(['note' => 'required']);
});

it('saves a checklist answer with checked evidences', function () {
    $cycle = fillingCycle();
    $indicator = $cycle->instrument->indicators()->where('number', 1)->first();
    $evidenceIds = $indicator->evidences->pluck('id')->all();
    $this->actingAs(fillingUser());

    Livewire::test(Filling::class, ['cycle' => $cycle])
        ->call('selectIndicator', $indicator->id)
        ->set('checkedEvidences', [(string) $evidenceIds[0]])
        ->set('note', 'Sudah terpenuhi sebagian.')
        ->call('saveAnswer')
        ->assertHasNoErrors();

    $answer = AdiwiyataIndicatorAnswer::first();
    expect($answer->status)->toBe('terisi');
    expect($answer->checked_evidences)->toBe([$evidenceIds[0]]);
});

it('validates count value is required for count method', function () {
    $cycle = fillingCycle();
    $indicator = $cycle->instrument->indicators()->where('number', 2)->first();
    $this->actingAs(fillingUser());

    Livewire::test(Filling::class, ['cycle' => $cycle])
        ->call('selectIndicator', $indicator->id)
        ->set('note', 'catatan')
        ->set('valueNumber', null)
        ->call('saveAnswer')
        ->assertHasErrors(['valueNumber']);
});

it('computes percentage from numerator and denominator', function () {
    $cycle = fillingCycle();
    $indicator = $cycle->instrument->indicators()->where('number', 3)->first();
    $this->actingAs(fillingUser());

    Livewire::test(Filling::class, ['cycle' => $cycle])
        ->call('selectIndicator', $indicator->id)
        ->set('note', 'catatan')
        ->set('valueNumerator', 3)
        ->set('valueDenominator', 4)
        ->call('saveAnswer')
        ->assertHasNoErrors();

    $answer = AdiwiyataIndicatorAnswer::first();
    expect((float) $answer->value_percentage)->toBe(75.0);
});

it('rejects zero denominator for percentage', function () {
    $cycle = fillingCycle();
    $indicator = $cycle->instrument->indicators()->where('number', 3)->first();
    $this->actingAs(fillingUser());

    Livewire::test(Filling::class, ['cycle' => $cycle])
        ->call('selectIndicator', $indicator->id)
        ->set('note', 'catatan')
        ->set('valueNumerator', 3)
        ->set('valueDenominator', 0)
        ->call('saveAnswer')
        ->assertHasErrors(['valueDenominator']);
});

it('uploads and deletes evidence', function () {
    Storage::fake('public');
    $cycle = fillingCycle();
    $indicator = $cycle->instrument->indicators()->where('number', 1)->first();
    $this->actingAs(fillingUser());

    $component = Livewire::test(Filling::class, ['cycle' => $cycle])
        ->call('selectIndicator', $indicator->id)
        ->set('evidenceType', 'dokumen')
        ->set('evidenceTitle', 'Dokumen KSP')
        ->set('file', UploadedFile::fake()->create('ksp.pdf', 100))
        ->call('uploadEvidence')
        ->assertHasNoErrors();

    $evidence = AdiwiyataEvidence::first();
    expect($evidence)->not->toBeNull();
    Storage::disk('public')->assertExists($evidence->file_path);

    $component->call('deleteEvidence', $evidence->id);
    expect(AdiwiyataEvidence::count())->toBe(0);
    Storage::disk('public')->assertMissing($evidence->file_path);
});

it('allows kepsek to access the filling route', function () {
    $cycle = fillingCycle();

    $this->actingAs(fillingUser('kepsek'))
        ->get(route('adiwiyata.filling', $cycle))
        ->assertOk();

    $this->actingAs(fillingUser('guru'))
        ->get(route('adiwiyata.filling', $cycle))
        ->assertOk();
});
