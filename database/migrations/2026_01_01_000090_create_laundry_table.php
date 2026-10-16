<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laundry', function (Blueprint $table) {
            $table->id();
            $table->string('laundry_number', 30)->unique();
            $table->foreignId('facility_id')->constrained()->restrictOnDelete();
            $table->foreignId('return_item_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('quantity');
            $table->date('date_in');
            $table->date('estimated_done')->nullable();
            $table->date('date_done')->nullable();
            $table->enum('status', ['pending', 'in_laundry', 'done', 'returned'])->default('pending');
            $table->string('laundry_vendor')->nullable();
            $table->decimal('laundry_cost', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['facility_id', 'status']);
            $table->index('laundry_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laundry');
    }
};
