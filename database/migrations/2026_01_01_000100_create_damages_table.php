<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('damages', function (Blueprint $table) {
            $table->id();
            $table->string('damage_number', 30)->unique();
            $table->foreignId('facility_id')->constrained()->restrictOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('return_item_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('quantity');
            $table->enum('damage_type', ['damage', 'loss'])->default('damage');
            $table->string('description');
            $table->string('photo')->nullable();
            $table->decimal('repair_cost', 12, 2)->default(0);
            $table->decimal('replacement_cost', 12, 2)->default(0);
            $table->enum('status', ['reported', 'in_repair', 'repaired', 'written_off'])->default('reported');
            $table->date('reported_date');
            $table->date('repaired_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['facility_id', 'status']);
            $table->index('damage_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('damages');
    }
};
