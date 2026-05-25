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
        Schema::create('backorders', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->date('tanggal');
            $table->decimal('total_os', 18, 2)->default(0);
            $table->decimal('penerimaan_po_so', 18, 2)->default(0);
            $table->decimal('penjualan', 18, 2)->default(0);
            $table->decimal('penerimaan_um', 18, 2)->default(0);
            $table->decimal('lpk', 18, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backorders');
    }
};
