<?php

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function moduleUser(): User
{
    return User::create([
        'name' => 'Guru', 'email' => 'guru-m@example.com',
        'password' => bcrypt('password'), 'role' => 'guru',
    ]);
}

it('logs in with akreditasi and lands on accreditation index', function () {
    moduleUser();

    Livewire::test(Login::class)
        ->set('module', 'akreditasi')
        ->set('email', 'guru-m@example.com')
        ->set('password', 'password')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('accreditation.index', absolute: false));

    expect(session('active_module'))->toBe('akreditasi');
});

it('logs in with adiwiyata and lands on adiwiyata index', function () {
    moduleUser();

    Livewire::test(Login::class)
        ->set('module', 'adiwiyata')
        ->set('email', 'guru-m@example.com')
        ->set('password', 'password')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('adiwiyata.index', absolute: false));

    expect(session('active_module'))->toBe('adiwiyata');
});

it('rejects an invalid module', function () {
    moduleUser();

    Livewire::test(Login::class)
        ->set('module', 'keuangan')
        ->set('email', 'guru-m@example.com')
        ->set('password', 'password')
        ->call('login')
        ->assertHasErrors(['module']);
});

it('switches the active module via the switch route', function () {
    $this->actingAs(moduleUser());

    $this->get(route('module.switch', 'adiwiyata'))
        ->assertRedirect(route('adiwiyata.index'));
    expect(session('active_module'))->toBe('adiwiyata');

    $this->get(route('module.switch', 'akreditasi'))
        ->assertRedirect(route('accreditation.index'));
    expect(session('active_module'))->toBe('akreditasi');
});

it('returns 404 for an unknown module in the switch route', function () {
    $this->actingAs(moduleUser());

    $this->get(route('module.switch', 'keuangan'))->assertNotFound();
});
