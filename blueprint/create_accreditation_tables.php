<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accreditation_instruments', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('version')->nullable();
            $table->year('year')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('accreditation_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instrument_id')->constrained('accreditation_instruments')->cascadeOnDelete();
            $table->unsignedInteger('number');
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['instrument_id', 'number']);
        });

        Schema::create('accreditation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('component_id')->constrained('accreditation_components')->cascadeOnDelete();
            $table->unsignedInteger('number');
            $table->text('title');
            $table->text('dka_prompt')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['component_id', 'number']);
        });

        Schema::create('accreditation_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('accreditation_items')->cascadeOnDelete();
            $table->string('code');
            $table->text('title');
            $table->longText('definition')->nullable();
            $table->boolean('is_na_allowed')->default(false);
            $table->text('na_condition')->nullable();
            $table->boolean('is_contextual')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['item_id', 'code']);
        });

        Schema::create('accreditation_rubric_levels', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('label');
            $table->unsignedTinyInteger('score_value');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('accreditation_rubrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indicator_id')->constrained('accreditation_indicators')->cascadeOnDelete();
            $table->foreignId('rubric_level_id')->constrained('accreditation_rubric_levels')->cascadeOnDelete();
            $table->string('context')->nullable(); // contoh: heterogen/homogen pada indikator 3.10.3
            $table->longText('description');
            $table->timestamps();
            $table->unique(['indicator_id', 'rubric_level_id', 'context'], 'rubric_unique_context');
        });

        Schema::create('accreditation_evidence_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('accreditation_indicator_evidence_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indicator_id')->constrained('accreditation_indicators')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['indicator_id', 'name'], 'indicator_evidence_suggestion_unique');
        });

        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('npsn')->nullable()->index();
            $table->string('name');
            $table->string('level')->nullable();
            $table->text('address')->nullable();
            $table->string('principal_name')->nullable();
            $table->timestamps();
        });

        Schema::create('accreditation_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('instrument_id')->constrained('accreditation_instruments')->restrictOnDelete();
            $table->year('year');
            $table->string('status')->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'instrument_id', 'year']);
        });

        Schema::create('accreditation_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cycle_id')->constrained('accreditation_cycles')->cascadeOnDelete();
            $table->foreignId('evidence_type_id')->constrained('accreditation_evidence_types')->restrictOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();
            $table->text('external_url')->nullable();
            $table->string('verification_status')->default('pending');
            $table->text('verification_note')->nullable();
            $table->timestamps();
        });

        Schema::create('accreditation_evidence_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evidence_id')->constrained('accreditation_evidences')->cascadeOnDelete();
            $table->foreignId('indicator_id')->nullable()->constrained('accreditation_indicators')->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('accreditation_items')->cascadeOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['indicator_id', 'item_id']);
        });

        Schema::create('accreditation_indicator_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cycle_id')->constrained('accreditation_cycles')->cascadeOnDelete();
            $table->foreignId('indicator_id')->constrained('accreditation_indicators')->cascadeOnDelete();
            $table->foreignId('rubric_id')->nullable()->constrained('accreditation_rubrics')->nullOnDelete();
            $table->foreignId('assessed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('checklist_status')->default('belum_diisi'); // lengkap/tidak_lengkap/perlu_revisi/na
            $table->boolean('is_na')->default(false);
            $table->string('rubric_context')->nullable();
            $table->unsignedTinyInteger('score_value')->nullable();
            $table->longText('teacher_note')->nullable();
            $table->timestamps();
            $table->unique(['cycle_id', 'indicator_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accreditation_indicator_scores');
        Schema::dropIfExists('accreditation_evidence_links');
        Schema::dropIfExists('accreditation_evidences');
        Schema::dropIfExists('accreditation_cycles');
        Schema::dropIfExists('schools');
        Schema::dropIfExists('accreditation_indicator_evidence_suggestions');
        Schema::dropIfExists('accreditation_evidence_types');
        Schema::dropIfExists('accreditation_rubrics');
        Schema::dropIfExists('accreditation_rubric_levels');
        Schema::dropIfExists('accreditation_indicators');
        Schema::dropIfExists('accreditation_items');
        Schema::dropIfExists('accreditation_components');
        Schema::dropIfExists('accreditation_instruments');
    }
};
