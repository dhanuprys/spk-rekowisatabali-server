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
        Schema::create('calculation_logs', function (Blueprint $table) {
            $table->id();

            $table->float('inp_l3_cg1_a');
            $table->float('inp_l3_cg1_b');

            $table->float('inp_l3_cg2_a');
            $table->float('inp_l3_cg2_b');
            $table->float('inp_l3_cg2_c');

            $table->float('inp_l3_cg3_a');
            $table->float('inp_l3_cg3_b');
            $table->float('inp_l3_cg3_c');

            $table->float('inp_l2_cg1_a');
            $table->float('inp_l2_cg1_b');
            $table->float('inp_l2_cg1_c');

            $table->float('inp_l1_a');
            $table->float('inp_l1_b');
            $table->float('inp_l1_c');

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
        Schema::dropIfExists('calculation_logs');
    }
};
