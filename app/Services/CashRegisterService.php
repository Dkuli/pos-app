<?php

namespace App\Services;

use App\Models\CashRegister;
use App\Models\CashRegisterSession;
use App\Models\CashRegisterTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CashRegisterService
{
    /**
     * Create a new cash register
     *
     * @param array $data
     * @return CashRegister
     */
    public function create(array $data): CashRegister
    {
        return CashRegister::create($data);
    }

    /**
     * Update an existing cash register
     *
     * @param CashRegister $cashRegister
     * @param array $data
     * @return CashRegister
     */
    public function update(CashRegister $cashRegister, array $data): CashRegister
    {
        $cashRegister->update($data);
        return $cashRegister;
    }

    /**
     * Delete a cash register
     *
     * @param CashRegister $cashRegister
     * @return bool
     */
    public function delete(CashRegister $cashRegister): bool
    {
        // Check if cash register has active sessions
        if ($cashRegister->sessions()->where('status', 'open')->exists()) {
            throw new \Exception('Cannot delete cash register with active sessions.');
        }

        return $cashRegister->delete();
    }

    /**
     * Open a new cash register session
     *
     * @param array $data
     * @return CashRegisterSession
     */
    public function openSession(array $data): CashRegisterSession
    {
        // Check if user already has an open session on this cash register
        $existingSession = CashRegisterSession::where('cash_register_id', $data['cash_register_id'])
            ->where('user_id', $data['user_id'])
            ->where('status', 'open')
            ->first();

        if ($existingSession) {
            throw new \Exception('User already has an open session on this cash register.');
        }

        // Check if cash register has another open session
        $anotherSession = CashRegisterSession::where('cash_register_id', $data['cash_register_id'])
            ->where('status', 'open')
            ->first();

        if ($anotherSession) {
            throw new \Exception('Cash register already has an open session with another user.');
        }

        return CashRegisterSession::create([
            'tenant_id' => $data['tenant_id'],
            'cash_register_id' => $data['cash_register_id'],
            'user_id' => $data['user_id'],
            'opening_amount' => $data['opening_amount'],
            'opening_note' => $data['opening_note'] ?? null,
            'opening_time' => Carbon::now(),
            'status' => 'open',
        ]);
    }

    /**
     * Close a cash register session
     *
     * @param CashRegisterSession $session
     * @param array $data
     * @return CashRegisterSession
     */
    public function closeSession(CashRegisterSession $session, array $data): CashRegisterSession
    {
        if ($session->status !== 'open') {
            throw new \Exception('Session is not open and cannot be closed.');
        }

        $session->update([
            'closing_amount' => $data['closing_amount'],
            'closing_note' => $data['closing_note'] ?? null,
            'closing_time' => Carbon::now(),
            'status' => 'closed',
        ]);

        return $session->fresh();
    }

    /**
     * Add transaction to cash register session
     *
     * @param CashRegisterSession $session
     * @param array $data
     * @return CashRegisterTransaction
     */
    public function addTransaction(CashRegisterSession $session, array $data): CashRegisterTransaction
    {
        if ($session->status !== 'open') {
            throw new \Exception('Cannot add transaction to a closed session.');
        }

        return CashRegisterTransaction::create([
            'tenant_id' => $session->tenant_id,
            'cash_register_session_id' => $session->id,
            'transaction_type' => $data['transaction_type'],
            'amount' => $data['amount'],
            'note' => $data['note'] ?? null,
            'transaction_date' => $data['transaction_date'] ?? Carbon::now(),
        ]);
    }

    /**
     * Get cash register summary
     *
     * @param CashRegisterSession $session
     * @return array
     */
    public function getSessionSummary(CashRegisterSession $session): array
    {
        $transactions = $session->transactions;

        $addTransactions = $transactions->where('transaction_type', 'add');
        $subtractTransactions = $transactions->where('transaction_type', 'subtract');

        $totalAdded = $addTransactions->sum('amount');
        $totalSubtracted = $subtractTransactions->sum('amount');

        $expectedAmount = $session->opening_amount + $totalAdded - $totalSubtracted;
        $difference = 0;

        if ($session->status === 'closed') {
            $difference = $session->closing_amount - $expectedAmount;
        }

        return [
            'session' => $session,
            'opening_amount' => $session->opening_amount,
            'closing_amount' => $session->closing_amount,
            'total_added' => $totalAdded,
            'total_subtracted' => $totalSubtracted,
            'expected_amount' => $expectedAmount,
            'difference' => $difference,
            'transactions' => $transactions,
        ];
    }

    /**
     * Get active session for a user
     *
     * @param User $user
     * @return CashRegisterSession|null
     */
    public function getActiveSessionForUser(User $user): ?CashRegisterSession
    {
        return CashRegisterSession::where('user_id', $user->id)
            ->where('status', 'open')
            ->with(['cashRegister', 'transactions'])
            ->first();
    }
}
