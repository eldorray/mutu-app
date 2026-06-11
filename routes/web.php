<?php

use App\Livewire\Accreditation\Index as AccreditationIndex;
use App\Livewire\Accreditation\IndicatorList;
use App\Livewire\Accreditation\Monitoring as AccreditationMonitoring;
use App\Livewire\Accreditation\TeacherFilling;
use App\Livewire\Adiwiyata\Components as AdiwiyataComponents;
use App\Livewire\Adiwiyata\Filling as AdiwiyataFilling;
use App\Livewire\Adiwiyata\Index as AdiwiyataIndex;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Settings\Appearance as SettingsAppearance;
use App\Livewire\Settings\Profile as SettingsProfile;
use App\Livewire\Settings\Theme as SettingsTheme;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(Auth::check() ? 'dashboard' : 'login');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    // Akreditasi
    Route::prefix('akreditasi')->name('accreditation.')->group(function () {
        Route::get('/', AccreditationIndex::class)->name('index');
        Route::get('/{cycle}/pengisian', TeacherFilling::class)->name('filling')->middleware('role:guru');
        Route::get('/{cycle}/indikator', IndicatorList::class)->name('indicators')->middleware('role:guru');
        Route::get('/{cycle}/monitoring', AccreditationMonitoring::class)->name('monitoring')->middleware('role:kepsek');
    });

    // Adiwiyata
    Route::prefix('adiwiyata')->name('adiwiyata.')->group(function () {
        Route::get('/', AdiwiyataIndex::class)->name('index');
        Route::get('/komponen', AdiwiyataComponents::class)->name('components')->middleware('role:guru,kepsek');
        Route::get('/{cycle}/pengisian', AdiwiyataFilling::class)->name('filling')->middleware('role:guru,kepsek');
    });

    // Ganti modul aktif (Akreditasi / Adiwiyata)
    Route::get('/modul/{module}', function (string $module) {
        abort_unless(in_array($module, ['akreditasi', 'adiwiyata'], true), 404);
        session(['active_module' => $module]);

        return redirect()->route($module === 'adiwiyata' ? 'adiwiyata.index' : 'accreditation.index');
    })->name('module.switch');

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::redirect('/', '/settings/profile');
        Route::get('/profile', SettingsProfile::class)->name('profile');
        Route::get('/appearance', SettingsAppearance::class)->name('appearance');
        Route::get('/theme', SettingsTheme::class)->name('theme');
    });

    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');
});
