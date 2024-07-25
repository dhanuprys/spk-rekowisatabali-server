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
        Schema::create('recommendation_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->float('l3_cg1_a');
            $table->float('l3_cg1_b');
            $table->float('l3_cg2_a');
            $table->float('l3_cg2_b');
            $table->float('l3_cg2_c');
            $table->float('l3_cg3_a');
            $table->float('l3_cg3_b');
            $table->float('l3_cg3_c');
            $table->float('l2_cg1_a');
            $table->float('l2_cg1_b');
            $table->float('l2_cg1_c');
            $table->float('l1_a');
            $table->float('l1_b');
            $table->float('l1_c');
            $table->tinyInteger('l1_b_direction');
            $table->tinyInteger('l1_c_direction');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recommendation_templates');
    }
};
