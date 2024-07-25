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
        Schema::create('places', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')
                ->nullable()
                ->constrained('cities')
                ->nullOnDelete();
            $table->string('name');
            $table->float('l3_cg1_a');
            $table->float('l3_cg1_b');
            $table->float('l3_cg2_a');
            $table->float('l3_cg2_b');
            $table->float('l3_cg2_c');
            $table->float('l3_cg3_a');
            $table->float('l3_cg3_b');
            $table->float('l3_cg3_c');
            $table->float('l1_b');
            $table->float('l1_c');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('places');
    }
};
