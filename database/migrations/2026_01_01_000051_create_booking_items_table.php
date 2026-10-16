<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('facility_id')->constrained()->restrictOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->string('price_type', 20); // per_hour, per_day, per_item, per_event
            $table->integer('duration')->default(1); // jumlah hari/jam
            $table->string('duration_unit', 10)->default('day'); // day, hour
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->text('notes')->nullable();

            // Status per item
            $table->enum('status', [
                'reserved', 'preparing', 'in_delivery', 'in_use',
                'returned', 'completed', 'cancelled'
            ])->default('reserved');

            $table->timestamps();

            $table->index(['booking_id', 'facility_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_items');
    }
};
