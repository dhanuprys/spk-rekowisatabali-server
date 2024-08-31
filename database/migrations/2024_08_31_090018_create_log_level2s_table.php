<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('log_level2s', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('calculation_logs')->onDelete('CASCADE');
            $table->foreignId('place_id')->constrained('places')->onDelete('CASCADE');
            $table->float('l1_a');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_level2s');
    }
};
