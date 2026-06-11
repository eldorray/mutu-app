<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Kepala Sekolah
        User::updateOrCreate(
            ['email' => 'kepsek@gmail.com'],
            [
                'name' => 'Kepala Sekolah',
                'password' => Hash::make('password'),
                'role' => 'kepsek',
                'email_verified_at' => now(),
            ],
        );

        // Guru
        User::updateOrCreate(
            ['email' => 'guru@gmail.com'],
            [
                'name' => 'Guru',
                'password' => Hash::make('password'),
                'role' => 'guru',
                'email_verified_at' => now(),
            ],
        );

        $this->call([
            AccreditationInstrumentSeeder::class,
            AdiwiyataInstrumentSeeder::class,
        ]);

        // Create a default school and cycle for demo
        $school = \App\Models\School::updateOrCreate(
            ['npsn' => '69755384'],
            [
                'name' => 'MI Daarul hikmah',
                'level' => 'SD/MI',
                'address' => 'Jl. Pembangunan 3, Rt. 05/05 Karangsari Kec. Neglasari',
                'principal_name' => 'Dra Nurjanah',
            ]
        );

        $instrument = \App\Models\AccreditationInstrument::where('is_active', true)->first();

        if ($instrument) {
            \App\Models\AccreditationCycle::updateOrCreate(
                ['school_id' => $school->id, 'instrument_id' => $instrument->id, 'year' => 2025],
                ['status' => 'draft']
            );
        }
    }
}
