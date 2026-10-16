<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->cascadeOnDelete();
            $table->enum('movement_type', [
                'purchase', 'booking', 'reservation', 'delivery',
                'return', 'laundry', 'laundry_complete', 'damage',
                'loss', 'adjustment', 'maintenance', 'maintenance_complete',
                'inspection_pass', 'inspection_fail', 'cancel'
            ]);
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30)->nullable();
            $table->integer('quantity');
            $table->integer('qty_before')->default(0);
            $table->integer('qty_after')->default(0);
            $table->string('reference_type')->nullable(); // booking, delivery, return, dll
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['facility_id', 'movement_type']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
