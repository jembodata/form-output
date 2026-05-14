<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('data', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();

            $table->bigInteger('planning')->nullable();
            $table->bigInteger('output_produksi_open')->nullable();
            $table->bigInteger('output_qc_transfer')->nullable();
            $table->bigInteger('under_testing')->nullable();
            $table->bigInteger('output_produksi_open_rp_ton_cu')->nullable();
            $table->bigInteger('output_produksi_open_rp_ton_al')->nullable();

            $table->decimal('planning_mtr', 15, 2)->nullable();
            $table->decimal('planning_ton_kabel', 15, 2)->nullable();

            $table->decimal('output_produksi_open_mtr', 15, 2)->nullable();
            $table->decimal('output_produksi_open_kabel', 15, 2)->nullable();
            $table->decimal('output_produksi_open_cu', 15, 2)->nullable();
            $table->decimal('output_produksi_open_al', 15, 2)->nullable();

            $table->decimal('output_qc_transfer_mtr', 15, 2)->nullable();
            $table->decimal('output_qc_transfer_ton_kabel', 15, 2)->nullable();
            $table->decimal('output_qc_transfer_cu', 15, 2)->nullable();
            $table->decimal('output_qc_transfer_al', 15, 2)->nullable();

            $table->decimal('under_testing_mtr', 15, 2)->nullable();
            $table->decimal('under_testing_ton_kabel', 15, 2)->nullable();
            $table->decimal('undertesting_open_ton_cu', 15, 2)->nullable();
            $table->decimal('under_testing_open_ton_al', 15, 2)->nullable();

            $table->decimal('pct_output_produksi_vs_planning_mtr', 8, 2)->nullable();
            $table->decimal('pct_output_produksi_vs_planning_ton_kabel', 8, 2)->nullable();
            $table->decimal('pct_output_produksi_vs_planning_rp', 8, 2)->nullable();
            $table->decimal('pct_output_qc_vs_output_produksi_mtr', 8, 2)->nullable();
            $table->decimal('pct_output_qc_vs_output_produksi_ton_kabel', 8, 2)->nullable();
            $table->decimal('pct_output_qc_vs_output_produksi_rp', 8, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data');
    }
};
