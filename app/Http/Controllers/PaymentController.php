<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payment\StorePaymentRequest;
use App\Http\Requests\Payment\UpdatePaymentRequest;
use App\Models\Payment;
use App\Models\Transaction;
use App\Services\PaymentService;
use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PaymentController extends Controller
{
    use AuthorizesRequests;

    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Display a listing of the payments for a transaction.
     */
    public function index(Transaction $transaction)
    {
        $this->authorize('view', $transaction);

        $payments = $transaction->payments;
        $totalPaid = $payments->sum('amount');
        $remaining = $transaction->total_amount - $totalPaid;

        return view('payments.index', compact('transaction', 'payments', 'totalPaid', 'remaining'));
    }

    /**
     * Show the form for creating a new payment.
     */
    public function create(Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $totalPaid = $transaction->payments->sum('amount');
        $remaining = $transaction->total_amount - $totalPaid;

        return view('payments.create', compact('transaction', 'remaining'));
    }

    /**
     * Store a newly created payment in storage.
     */
    public function store(StorePaymentRequest $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        try {
            $data = $request->validated();

            $payment = $this->paymentService->createPayment($transaction, $data);

            return redirect()->route('transactions.payments.index', $transaction->id)
                ->with('success', 'Payment added successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error adding payment: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing a payment.
     */
    public function edit(Transaction $transaction, Payment $payment)
    {
        $this->authorize('update', $transaction);

        if ($payment->transaction_id !== $transaction->id) {
            abort(404);
        }

        return view('payments.edit', compact('transaction', 'payment'));
    }

    /**
     * Update the specified payment in storage.
     */
    public function update(UpdatePaymentRequest $request, Transaction $transaction, Payment $payment)
    {
        $this->authorize('update', $transaction);

        if ($payment->transaction_id !== $transaction->id) {
            abort(404);
        }

        try {
            $data = $request->validated();

            $payment = $this->paymentService->updatePayment($payment, $data);

            return redirect()->route('transactions.payments.index', $transaction->id)
                ->with('success', 'Payment updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error updating payment: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified payment from storage.
     */
    public function destroy(Transaction $transaction, Payment $payment)
    {
        $this->authorize('update', $transaction);

        if ($payment->transaction_id !== $transaction->id) {
            abort(404);
        }

        try {
            $this->paymentService->deletePayment($payment);

            return redirect()->route('transactions.payments.index', $transaction->id)
                ->with('success', 'Payment deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting payment: ' . $e->getMessage());
        }
    }

    /**
     * Print payment receipt.
     */
    public function printReceipt(Transaction $transaction, Payment $payment)
    {
        $this->authorize('view', $transaction);

        if ($payment->transaction_id !== $transaction->id) {
            abort(404);
        }

        $transaction->load(['store', 'customer']);

        return view('payments.receipt', compact('transaction', 'payment'));
    }
}
