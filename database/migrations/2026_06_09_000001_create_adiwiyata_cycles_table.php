<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adiwiyata_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('instrument_id')->constrained('adiwiyata_instruments')->restrictOnDelete();
            $table->year('year');
            $table->string('status')->default('draft'); // draft, berjalan, selesai
            $table->string('award_level')->nullable(); // calon, kabupaten, provinsi, nasional, mandiri
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'instrument_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adiwiyata_cycles');
    }
};
