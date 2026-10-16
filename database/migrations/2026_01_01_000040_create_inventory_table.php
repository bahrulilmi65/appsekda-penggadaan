<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Inventory tracks quantity by status for each facility
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->unique()->constrained()->cascadeOnDelete();
            $table->integer('qty_available')->default(0);
            $table->integer('qty_reserved')->default(0);    // dipesan belum bayar
            $table->integer('qty_preparing')->default(0);   // sedang disiapkan
            $table->integer('qty_in_delivery')->default(0); // dalam pengantaran
            $table->integer('qty_in_use')->default(0);      // sedang digunakan
            $table->integer('qty_returned')->default(0);    // dikembalikan belum diinspeksi
            $table->integer('qty_inspection')->default(0);  // dalam pemeriksaan
            $table->integer('qty_laundry')->default(0);     // laundry
            $table->integer('qty_damaged')->default(0);     // rusak
            $table->integer('qty_lost')->default(0);        // hilang
            $table->integer('qty_maintenance')->default(0); // maintenance
            $table->integer('qty_total')->default(0);       // total keseluruhan
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};
