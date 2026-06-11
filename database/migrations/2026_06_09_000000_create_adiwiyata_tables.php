<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adiwiyata_instruments', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('version')->nullable();
            $table->year('year')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('adiwiyata_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instrument_id')->constrained('adiwiyata_instruments')->cascadeOnDelete();
            $table->unsignedInteger('number');
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['instrument_id', 'number']);
        });

        Schema::create('adiwiyata_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instrument_id')->constrained('adiwiyata_instruments')->cascadeOnDelete();
            $table->foreignId('component_id')->nullable()->constrained('adiwiyata_components')->nullOnDelete();
            $table->unsignedInteger('number');
            $table->text('title');
            $table->longText('description')->nullable();
            $table->string('scoring_method')->default('checklist'); // checklist, count, percentage
            $table->longText('scoring_guide')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['instrument_id', 'number']);
        });

        Schema::create('adiwiyata_indicator_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indicator_id')->constrained('adiwiyata_indicators')->cascadeOnDelete();
            $table->text('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adiwiyata_indicator_evidences');
        Schema::dropIfExists('adiwiyata_indicators');
        Schema::dropIfExists('adiwiyata_components');
        Schema::dropIfExists('adiwiyata_instruments');
    }
};
