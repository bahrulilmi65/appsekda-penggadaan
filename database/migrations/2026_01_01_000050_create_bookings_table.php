<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number', 30)->unique();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->date('booking_date');
            $table->date('start_date');
            $table->date('end_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('purpose'); // tujuan penggunaan
            $table->integer('participants')->nullable(); // jumlah peserta
            $table->text('notes')->nullable();
            $table->text('special_request')->nullable();

            // Finansial
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('additional_fee', 15, 2)->default(0);
            $table->text('additional_fee_notes')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);

            // Status
            $table->enum('status', [
                'draft', 'pending', 'waiting_approval', 'approved',
                'rejected', 'waiting_payment', 'paid', 'preparing',
                'in_delivery', 'in_use', 'return_process', 'completed',
                'cancelled', 'expired'
            ])->default('draft');

            $table->enum('payment_status', [
                'unpaid', 'waiting_verification', 'paid', 'partial', 'refunded'
            ])->default('unpaid');

            // Approval
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();

            // Hold / Expiry
            $table->timestamp('hold_until')->nullable(); // reservation hold expiry
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['customer_id', 'status']);
            $table->index(['start_date', 'end_date']);
            $table->index('booking_number');
            $table->index('status');
            $table->index('payment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
