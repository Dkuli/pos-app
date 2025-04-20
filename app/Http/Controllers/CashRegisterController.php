<?php

namespace App\Http\Controllers;

use App\Http\Requests\CashRegister\StoreCashRegisterRequest;
use App\Http\Requests\CashRegister\UpdateCashRegisterRequest;
use App\Http\Requests\CashRegisterSession\StoreCashRegisterSessionRequest;
use App\Http\Requests\CashRegisterSession\UpdateCashRegisterSessionRequest;
use App\Http\Requests\CashRegisterTransaction\StoreCashRegisterTransactionRequest;
use App\Models\CashRegister;
use App\Models\CashRegisterSession;
use App\Models\Store;
use App\Services\CashRegisterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CashRegisterController extends Controller
{
    use AuthorizesRequests;
    protected $cashRegisterService;

    public function __construct(CashRegisterService $cashRegisterService)
    {
        $this->cashRegisterService = $cashRegisterService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $storeIds = $request->user()->stores->pluck('id')->toArray();

        $query = CashRegister::where('tenant_id', $tenantId)
            ->whereIn('store_id', $storeIds);

        // Apply filters
        if ($request->filled('store')) {
            $query->where('store_id', $request->input('store'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $registers = $query->with('store')->get();

        $stores = Store::where('tenant_id', $tenantId)
            ->whereIn('id', $storeIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('cash-registers.index', compact('registers', 'stores'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tenantId = Auth::user()->tenant_id;
        $storeIds = Auth::user()->stores->pluck('id')->toArray();

        $stores = Store::where('tenant_id', $tenantId)
            ->whereIn('id', $storeIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('cash-registers.create', compact('stores'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCashRegisterRequest $request)
    {
        try {
            $data = $request->validated();
            $data['tenant_id'] = Auth::user()->tenant_id;

            $register = $this->cashRegisterService->create($data);

            return redirect()->route('cash-registers.show', $register->id)
                ->with('success', 'Cash register created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error creating cash register: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CashRegister $cashRegister)
    {
        $this->authorize('view', $cashRegister);

        $cashRegister->load(['store', 'sessions' => function($query) {
            $query->latest()->limit(10);
        }]);

        // Check if register has an active session
        $activeSession = $cashRegister->sessions()->where('status', 'open')->first();

        return view('cash-registers.show', compact('cashRegister', 'activeSession'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CashRegister $cashRegister)
    {
        $this->authorize('update', $cashRegister);

        $tenantId = Auth::user()->tenant_id;
        $storeIds = Auth::user()->stores->pluck('id')->toArray();

        $stores = Store::where('tenant_id', $tenantId)
            ->whereIn('id', $storeIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('cash-registers.edit', compact('cashRegister', 'stores'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCashRegisterRequest $request, CashRegister $cashRegister)
    {
        $this->authorize('update', $cashRegister);

        try {
            $this->cashRegisterService->update($cashRegister, $request->validated());

            return redirect()->route('cash-registers.show', $cashRegister->id)
                ->with('success', 'Cash register updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error updating cash register: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CashRegister $cashRegister)
    {
        $this->authorize('delete', $cashRegister);

        try {
            $this->cashRegisterService->delete($cashRegister);

            return redirect()->route('cash-registers.index')
                ->with('success', 'Cash register deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting cash register: ' . $e->getMessage());
        }
    }

    /**
     * Show open register form.
     */
    public function showOpenForm(CashRegister $cashRegister)
    {
        $this->authorize('operate', $cashRegister);

        // Check if register already has an open session
        if ($cashRegister->sessions()->where('status', 'open')->exists()) {
            return back()->with('error', 'This register already has an open session.');
        }

        // Check if user already has an open session
        $hasOpenSession = CashRegisterSession::where('user_id', Auth::id())
            ->where('status', 'open')
            ->exists();

        if ($hasOpenSession) {
            return back()->with('error', 'You already have an open session on another register.');
        }

        return view('cash-registers.open', compact('cashRegister'));
    }

    /**
     * Open cash register session.
     */
    public function openRegister(StoreCashRegisterSessionRequest $request, CashRegister $cashRegister)
    {
        $this->authorize('operate', $cashRegister);

        try {
            $data = $request->validated();
            $data['tenant_id'] = Auth::user()->tenant_id;
            $data['user_id'] = Auth::id();
            $data['cash_register_id'] = $cashRegister->id;

            $session = $this->cashRegisterService->openSession($data);

            return redirect()->route('cash-registers.session', $session->id)
                ->with('success', 'Cash register opened successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error opening cash register: ' . $e->getMessage());
        }
    }

    /**
     * Show active register session.
     */
    public function showSession(CashRegisterSession $session)
    {
        $this->authorize('operate', $session->cashRegister);

        if ($session->user_id !== Auth::id()) {
            return redirect()->route('cash-registers.index')
                ->with('error', 'You do not have permission to view this session.');
        }

        $summary = $this->cashRegisterService->getSessionSummary($session);

        return view('cash-registers.session', compact('session', 'summary'));
    }

    /**
     * Show close register form.
     */
    public function showCloseForm(CashRegisterSession $session)
    {
        $this->authorize('operate', $session->cashRegister);

        if ($session->user_id !== Auth::id()) {
            return redirect()->route('cash-registers.index')
                ->with('error', 'You do not have permission to close this session.');
        }

        if ($session->status !== 'open') {
            return redirect()->route('cash-registers.index')
                ->with('error', 'This session is already closed.');
        }

        $summary = $this->cashRegisterService->getSessionSummary($session);

        return view('cash-registers.close', compact('session', 'summary'));
    }

    /**
     * Close cash register session.
     */
    public function closeRegister(UpdateCashRegisterSessionRequest $request, CashRegisterSession $session)
    {
        $this->authorize('operate', $session->cashRegister);

        if ($session->user_id !== Auth::id()) {
            return redirect()->route('cash-registers.index')
                ->with('error', 'You do not have permission to close this session.');
        }

        try {
            $this->cashRegisterService->closeSession($session, $request->validated());

            return redirect()->route('cash-registers.show', $session->cash_register_id)
                ->with('success', 'Cash register closed successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error closing cash register: ' . $e->getMessage());
        }
    }

    /**
     * Add register transaction.
     */
    public function addTransaction(StoreCashRegisterTransactionRequest $request, CashRegisterSession $session)
    {
        $this->authorize('operate', $session->cashRegister);

        if ($session->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to operate this session.'
            ]);
        }

        try {
            $data = $request->validated();
            $data['tenant_id'] = Auth::user()->tenant_id;
            $data['cash_register_session_id'] = $session->id;

            $transaction = $this->cashRegisterService->addTransaction($session, $data);

            $summary = $this->cashRegisterService->getSessionSummary($session);

            return response()->json([
                'success' => true,
                'transaction' => $transaction,
                'summary' => $summary
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Print session summary.
     */
    public function printSummary(CashRegisterSession $session)
    {
        $this->authorize('view', $session->cashRegister);

        $summary = $this->cashRegisterService->getSessionSummary($session);

        return view('cash-registers.print-summary', compact('session', 'summary'));
    }

    /**
     * Get current user's active session.
     */
    public function getCurrentSession()
    {
        $user = Auth::user();
        $session = $this->cashRegisterService->getActiveSessionForUser($user);

        if ($session) {
            return response()->json([
                'success' => true,
                'has_active_session' => true,
                'session' => $session
            ]);
        } else {
            return response()->json([
                'success' => true,
                'has_active_session' => false
            ]);
        }
    }
}
