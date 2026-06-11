<?php

use App\Livewire\Adiwiyata\Index;
use App\Models\AdiwiyataCycle;
use App\Models\AdiwiyataInstrument;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function activeAdiwiyataInstrument(): AdiwiyataInstrument
{
    return AdiwiyataInstrument::create([
        'code' => 'ADIWIYATA-TEST',
        'name' => 'Instrumen Adiwiyata Test',
        'year' => 2025,
        'is_active' => true,
    ]);
}

function actingUser(): User
{
    return User::create([
        'name' => 'Guru',
        'email' => 'guru-test@example.com',
        'password' => bcrypt('password'),
        'role' => 'guru',
    ]);
}

it('renders the adiwiyata index', function () {
    $this->actingAs(actingUser());

    Livewire::test(Index::class)
        ->assertOk()
        ->assertSee('Kelola siklus Adiwiyata');
});

it('creates a cycle with an active instrument', function () {
    $instrument = activeAdiwiyataInstrument();
    $this->actingAs(actingUser());

    Livewire::test(Index::class)
        ->call('create')
        ->set('schoolName', 'MI Daarul Hikmah')
        ->set('year', '2025')
        ->set('status', 'berjalan')
        ->set('awardLevel', 'kabupaten')
        ->call('save')
        ->assertHasNoErrors();

    $cycle = AdiwiyataCycle::first();
    expect($cycle)->not->toBeNull();
    expect($cycle->instrument_id)->toBe($instrument->id);
    expect($cycle->status)->toBe('berjalan');
    expect($cycle->award_level)->toBe('kabupaten');
    expect($cycle->school->name)->toBe('MI Daarul Hikmah');
});

it('validates required fields and award level', function () {
    activeAdiwiyataInstrument();
    $this->actingAs(actingUser());

    Livewire::test(Index::class)
        ->set('schoolName', '')
        ->set('year', '1999')
        ->set('awardLevel', 'galaksi')
        ->call('save')
        ->assertHasErrors(['schoolName', 'year', 'awardLevel']);
});

it('edits an existing cycle', function () {
    $instrument = activeAdiwiyataInstrument();
    $school = School::create(['name' => 'SDN 1']);
    $cycle = AdiwiyataCycle::create([
        'school_id' => $school->id,
        'instrument_id' => $instrument->id,
        'year' => 2024,
        'status' => 'draft',
    ]);
    $this->actingAs(actingUser());

    Livewire::test(Index::class)
        ->call('edit', $cycle->id)
        ->set('schoolName', 'SDN 1 Updated')
        ->set('status', 'selesai')
        ->set('awardLevel', 'nasional')
        ->call('save')
        ->assertHasNoErrors();

    $cycle->refresh();
    expect($cycle->status)->toBe('selesai');
    expect($cycle->award_level)->toBe('nasional');
    expect($cycle->school->name)->toBe('SDN 1 Updated');
});

it('deletes a cycle and its orphan school', function () {
    $instrument = activeAdiwiyataInstrument();
    $school = School::create(['name' => 'SDN Orphan']);
    $cycle = AdiwiyataCycle::create([
        'school_id' => $school->id,
        'instrument_id' => $instrument->id,
        'year' => 2024,
        'status' => 'draft',
    ]);
    $this->actingAs(actingUser());

    Livewire::test(Index::class)->call('delete', $cycle->id);

    expect(AdiwiyataCycle::count())->toBe(0);
    expect(School::find($school->id))->toBeNull();
});
