<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['building', 'asset', 'equipment', 'consumable'])->default('asset');
            $table->string('unit', 30)->default('unit'); // pcs, unit, set, m2, dll
            $table->integer('total_stock')->default(1);
            $table->integer('minimum_stock')->default(0);
            $table->decimal('price', 15, 2)->default(0);
            $table->enum('price_type', ['per_hour', 'per_day', 'per_item', 'per_event'])->default('per_day');
            $table->integer('capacity')->nullable(); // untuk gedung
            $table->string('location')->nullable();
            $table->string('floor')->nullable();
            $table->string('building')->nullable();
            $table->text('facilities_list')->nullable(); // fasilitas dalam gedung
            $table->text('terms_conditions')->nullable();
            $table->boolean('is_bookable')->default(true);
            $table->boolean('requires_approval')->default(true);
            $table->boolean('is_active')->default(true);
            $table->integer('min_booking_hours')->default(1);
            $table->integer('max_booking_days')->default(30);
            $table->integer('booking_advance_days')->default(1); // minimal H-X
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'type', 'is_active']);
            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facilities');
    }
};
