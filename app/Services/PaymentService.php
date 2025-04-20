<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PaymentService
{
    /**
     * Create a new payment for a transaction
     *
     * @param Transaction $transaction
     * @param array $data
     * @return Payment
     */
    public function createPayment(Transaction $transaction, array $data): Payment
    {
        DB::beginTransaction();
        try {
            $payment = Payment::create([
                'tenant_id' => $transaction->tenant_id,
                'reference_type' => get_class($transaction),
                'reference_id' => $transaction->id,
                'user_id' => Auth::id(),
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                // other fields
            ]);

            // Update the transaction's payment status
            $this->updateTransactionPaymentStatus($transaction);

            DB::commit();
            return $payment;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Update an existing payment
     *
     * @param Payment $payment
     * @param array $data
     * @return Payment
     */
    public function updatePayment(Payment $payment, array $data): Payment
    {
        DB::beginTransaction();
        try {
            $payment->update([
                'amount' => $data['amount'] ?? $payment->amount,
                'payment_method' => $data['payment_method'] ?? $payment->payment_method,
                'reference' => $data['reference'] ?? $payment->reference,
                'payment_date' => $data['payment_date'] ?? $payment->payment_date,
                'notes' => $data['notes'] ?? $payment->notes,
            ]);

            // Update the transaction's payment status
            $this->updateTransactionPaymentStatus($payment->transaction);

            DB::commit();
            return $payment->fresh();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Delete a payment
     *
     * @param Payment $payment
     * @return bool
     */
    public function deletePayment(Payment $payment): bool
    {
        DB::beginTransaction();
        try {
            $transaction = $payment->transaction;
            $result = $payment->delete();

            // Update the transaction's payment status
            $this->updateTransactionPaymentStatus($transaction);

            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Update transaction payment status based on payments
     *
     * @param Transaction $transaction
     * @return Transaction
     */
    private function updateTransactionPaymentStatus(Transaction $transaction): Transaction
    {
        $totalAmount = $transaction->total_amount;
        $paidAmount = $transaction->payments()->sum('amount');

        if ($paidAmount >= $totalAmount) {
            $transaction->update(['payment_status' => 'paid']);
        } elseif ($paidAmount > 0) {
            $transaction->update(['payment_status' => 'partial']);
        } else {
            $transaction->update(['payment_status' => 'unpaid']);
        }

        return $transaction->fresh();
    }

    /**
     * Get payment summary by method
     *
     * @param int $tenantId
     * @param string $dateFrom
     * @param string $dateTo
     * @return array
     */
    public function getPaymentSummaryByMethod(int $tenantId, string $dateFrom, string $dateTo): array
    {
        $payments = Payment::where('tenant_id', $tenantId)
            ->whereBetween('payment_date', [$dateFrom, $dateTo])
            ->get();

        $summary = [];

        foreach ($payments as $payment) {
            $method = $payment->payment_method;
            if (!isset($summary[$method])) {
                $summary[$method] = 0;
            }
            $summary[$method] += $payment->amount;
        }

        return $summary;
    }
}
