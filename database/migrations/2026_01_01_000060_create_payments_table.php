<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number', 30)->unique();
            $table->foreignId('booking_id')->constrained()->restrictOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->decimal('amount_due', 15, 2); // total tagihan
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->enum('payment_method', [
                'transfer', 'cash', 'virtual_account', 'qris', 'lainnya'
            ])->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();
            $table->string('transaction_number')->nullable();
            $table->date('payment_date')->nullable();
            $table->string('proof_of_payment')->nullable(); // file path
            $table->enum('status', [
                'unpaid', 'waiting_verification', 'paid', 'partial', 'rejected', 'refunded'
            ])->default('unpaid');
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['booking_id', 'status']);
            $table->index('payment_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
