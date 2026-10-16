<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Notification;
use App\Models\Payment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        protected BookingService $bookingService
    ) {}

    /**
     * Buat payment baru untuk booking
     */
    public function createPayment(Booking $booking): Payment
    {
        return Payment::firstOrCreate(
            ['booking_id' => $booking->id, 'status' => 'unpaid'],
            [
                'customer_id' => $booking->customer_id,
                'amount_due' => $booking->total_amount,
                'amount_paid' => 0,
                'status' => 'unpaid',
                'due_date' => now()->addDays(3),
            ]
        );
    }

    /**
     * Upload bukti pembayaran oleh penyewa
     */
    public function uploadProof(Payment $payment, UploadedFile $file, array $data): Payment
    {
        return DB::transaction(function () use ($payment, $file, $data) {
            $path = $file->store('payments/proof', 'public');

            $payment->update([
                'proof_of_payment' => $path,
                'payment_method' => $data['payment_method'],
                'bank_name' => $data['bank_name'] ?? null,
                'account_number' => $data['account_number'] ?? null,
                'account_name' => $data['account_name'] ?? null,
                'transaction_number' => $data['transaction_number'] ?? null,
                'payment_date' => $data['payment_date'],
                'amount_paid' => $data['amount_paid'],
                'notes' => $data['notes'] ?? null,
                'status' => 'waiting_verification',
            ]);

            // Update booking payment status
            $payment->booking->update(['payment_status' => 'waiting_verification']);

            // Notifikasi bendahara
            $this->notifyBendahara($payment);

            ActivityLog::record('upload', 'payment', "Upload bukti pembayaran {$payment->payment_number}");

            return $payment;
        });
    }

    /**
     * Verifikasi pembayaran oleh bendahara/admin
     */
    public function verifyPayment(Payment $payment, bool $approved, ?string $notes = null): Payment
    {
        return DB::transaction(function () use ($payment, $approved, $notes) {
            if ($approved) {
                $payment->update([
                    'status' => 'paid',
                    'verified_by' => auth()->id(),
                    'verified_at' => now(),
                    'notes' => $notes,
                ]);

                // Update booking status
                $booking = $payment->booking;
                $booking->update([
                    'payment_status' => 'paid',
                    'status' => 'paid',
                ]);

                // Catat status history
                $this->bookingService->changeStatus($booking, 'paid', 'Pembayaran diverifikasi oleh ' . auth()->user()->name);

                // Generate invoice
                $this->generateInvoice($booking, $payment);

                // Notifikasi customer
                Notification::send(
                    $booking->user_id,
                    'Pembayaran Diverifikasi',
                    "Pembayaran untuk booking {$booking->booking_number} telah diverifikasi. Silakan cek invoice Anda.",
                    'success',
                    route('user.bookings.show', $booking->id),
                    'payment',
                    $payment->id
                );

                ActivityLog::record('verify', 'payment', "Memverifikasi pembayaran {$payment->payment_number}", [
                    'reference_type' => 'payment',
                    'reference_id' => $payment->id,
                ]);
            } else {
                $payment->update([
                    'status' => 'rejected',
                    'rejection_reason' => $notes,
                    'verified_by' => auth()->id(),
                    'verified_at' => now(),
                ]);

                $booking = $payment->booking;

                Notification::send(
                    $booking->user_id,
                    'Pembayaran Ditolak',
                    "Bukti pembayaran untuk booking {$booking->booking_number} ditolak. Alasan: {$notes}. Silakan upload ulang.",
                    'danger',
                    route('user.bookings.show', $booking->id),
                    'payment',
                    $payment->id
                );

                ActivityLog::record('reject', 'payment', "Menolak pembayaran {$payment->payment_number} - {$notes}");
            }

            return $payment;
        });
    }

    /**
     * Generate invoice PDF
     */
    public function generateInvoice(Booking $booking, Payment $payment): Invoice
    {
        return Invoice::firstOrCreate(
            ['booking_id' => $booking->id],
            [
                'payment_id' => $payment->id,
                'customer_id' => $booking->customer_id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(14)->toDateString(),
                'subtotal' => $booking->subtotal,
                'discount' => $booking->discount_amount,
                'additional_fee' => $booking->additional_fee,
                'total' => $booking->total_amount,
                'status' => 'issued',
                'created_by' => auth()->id(),
            ]
        );
    }

    protected function notifyBendahara(Payment $payment): void
    {
        // Notifikasi ke semua user dengan role bendahara
        // Simplified - bisa dikembangkan
    }
}
