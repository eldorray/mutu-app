<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adiwiyata_indicator_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cycle_id')->constrained('adiwiyata_cycles')->cascadeOnDelete();
            $table->foreignId('indicator_id')->constrained('adiwiyata_indicators')->cascadeOnDelete();
            $table->foreignId('filled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->longText('note')->nullable();
            $table->json('checked_evidences')->nullable(); // checklist: id bukti yang terpenuhi
            $table->unsignedInteger('value_number')->nullable(); // count
            $table->unsignedInteger('value_numerator')->nullable(); // percentage
            $table->unsignedInteger('value_denominator')->nullable(); // percentage
            $table->decimal('value_percentage', 5, 2)->nullable(); // percentage (hasil)
            $table->string('status')->default('belum_diisi'); // belum_diisi, terisi
            $table->timestamps();
            $table->unique(['cycle_id', 'indicator_id']);
        });

        Schema::create('adiwiyata_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cycle_id')->constrained('adiwiyata_cycles')->cascadeOnDelete();
            $table->foreignId('indicator_id')->constrained('adiwiyata_indicators')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type')->default('dokumen'); // dokumen, foto, video, tautan
            $table->string('title');
            $table->string('file_path')->nullable();
            $table->text('external_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adiwiyata_evidences');
        Schema::dropIfExists('adiwiyata_indicator_answers');
    }
};
