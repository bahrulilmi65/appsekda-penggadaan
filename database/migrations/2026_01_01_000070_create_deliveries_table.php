<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_number', 30)->unique();
            $table->foreignId('booking_id')->constrained()->restrictOnDelete();
            $table->enum('type', ['delivery', 'pickup'])->default('delivery');
            $table->string('destination_address')->nullable();
            $table->string('destination_person')->nullable();
            $table->string('destination_phone', 20)->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', [
                'waiting', 'preparing', 'ready_to_deliver',
                'in_delivery', 'delivered', 'returning', 'returned'
            ])->default('waiting');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('departed_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->text('notes')->nullable();
            $table->string('vehicle')->nullable();
            $table->string('driver_name')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'status']);
            $table->index('delivery_number');
        });

        Schema::create('delivery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained()->cascadeOnDelete();
            $table->foreignId('facility_id')->constrained()->restrictOnDelete();
            $table->foreignId('booking_item_id')->constrained()->restrictOnDelete();
            $table->integer('quantity_sent')->default(0);
            $table->integer('quantity_received')->nullable();
            $table->text('notes')->nullable();
            $table->enum('condition', ['good', 'damaged', 'incomplete'])->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_items');
        Schema::dropIfExists('deliveries');
    }
};
