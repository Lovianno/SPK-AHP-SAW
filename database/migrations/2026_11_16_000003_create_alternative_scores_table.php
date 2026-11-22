<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alternative_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alternative_id')
                ->constrained('alternatives')
                ->cascadeOnDelete();
            $table->foreignId('criterion_id')
                ->constrained('criteria')
                ->restrictOnDelete();
            // Skor alternatif 1-100; validasi rentang di Form Request/controller.
            $table->decimal('value', 15, 4);
            $table->timestamps();

            $table->unique(
                ['alternative_id', 'criterion_id'],
                'alternative_scores_pair_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alternative_scores');
    }
};
