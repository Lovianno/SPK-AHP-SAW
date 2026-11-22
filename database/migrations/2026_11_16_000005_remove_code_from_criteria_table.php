<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('criteria', 'code')) {
            Schema::table('criteria', function (Blueprint $table) {
                $table->dropUnique('criteria_code_unique');
                $table->dropColumn('code');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('criteria', 'code')) {
            Schema::table('criteria', function (Blueprint $table) {
                $table->string('code', 10)->nullable()->unique();
            });
        }
    }
};
