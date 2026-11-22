<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ahp_comparisons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('criterion_a_id')
                ->constrained('criteria')
                ->restrictOnDelete();
            $table->foreignId('criterion_b_id')
                ->constrained('criteria')
                ->restrictOnDelete();
            // Rasio A terhadap B: skala Saaty 1-9 atau kebalikannya.
            $table->decimal('value', 20, 12);
            $table->timestamps();

            // Validasi aplikasi: criterion_a_id < criterion_b_id; tanpa diagonal.
            // Jika urutan input dibalik, balik pula value menjadi 1 / value.
            $table->unique(
                ['criterion_a_id', 'criterion_b_id'],
                'ahp_comparisons_pair_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ahp_comparisons');
    }
};
