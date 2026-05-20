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
        Schema::create('defects', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->unique();
            $table->decimal('output_fg', 10, 2);
            $table->decimal('defect', 10, 2)->nullable();
            $table->decimal('km', 10, 2)->nullable();
            $table->decimal('target_dc', 5, 2)->default(0.65);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('defects');
    }
};
