<?php

namespace App\Http\Controllers;

use App\Interfaces\Repositories\CashReconciliationRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class CashReconciliationController extends Controller
{
    public function __construct(
        protected CashReconciliationRepositoryInterface $reconciliationRepository
    ) {}

    public function index(Request $request)
    {
        Gate::authorize('cashier');

        $date = $request->query('date', today()->toDateString());
        $systemCashTotal = $this->reconciliationRepository->getSystemCashTotalForDate($date);

        $reconciliations = $this->reconciliationRepository->all()->map(fn ($r) => [
            'id' => $r->id,
            'reconciliation_date' => $r->reconciliation_date->format('Y-m-d'),
            'prepared_by' => $r->preparedBy?->name ?? 'Staff',
            'approved_by' => $r->approvedBy?->name,
            'system_total' => (float) $r->system_total,
            'physical_count' => (float) $r->physical_count,
            'variance_amount' => (float) $r->variance_amount,
            'variance_pct' => (float) $r->variance_pct,
            'flagged' => $r->isFlagged(),
            'notes' => $r->notes,
            'created_at' => $r->created_at->format('H:i, M d, Y'),
        ]);

        return Inertia::render('Reconciliation/Index', [
            'reconciliations' => $reconciliations,
            'selected_date' => $date,
            'system_cash_total' => $systemCashTotal,
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('cashier');

        $validated = $request->validate([
            'reconciliation_date' => 'required|date',
            'physical_count' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $systemTotal = $this->reconciliationRepository->getSystemCashTotalForDate($validated['reconciliation_date']);

        $reconciliation = $this->reconciliationRepository->create([
            'reconciliation_date' => $validated['reconciliation_date'],
            'prepared_by' => auth()->id(),
            'system_total' => $systemTotal,
            'physical_count' => $validated['physical_count'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->back()->with('message', 'Cash reconciliation submitted successfully.');
    }

    public function approve(Request $request, int $id)
    {
        Gate::authorize('admin');

        $this->reconciliationRepository->approve($id, auth()->id());

        return redirect()->back()->with('message', 'Reconciliation approved successfully.');
    }
}
