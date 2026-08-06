<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bill\GenerateBillRequest;
use App\Http\Requests\Bill\SplitBillRequest;
use App\Http\Requests\Bill\ProcessPaymentRequest;
use App\Models\Bill;
use App\Models\TableSession;
use App\Services\BillService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class BillController extends Controller
{
    protected BillService $billService;

    public function __construct(BillService $billService)
    {
        $this->billService = $billService;
    }

    public function generate(Request $request)
    {
        Gate::authorize('cashier');

        $validated = $request->validate([
            'session_id' => 'required|exists:table_sessions,id',
        ]);

        $bill = $this->billService->generateBill($validated['session_id'], auth()->id());

        return response()->json([
            'bill_id' => $bill->id,
            'status' => 'success',
            'grand_total' => $bill->grand_total,
        ]);
    }

    public function split(Request $request, Bill $bill)
    {
        Gate::authorize('cashier');

        $validated = $request->validate([
            'split_type' => ['required', 'in:equally,by_item,custom'],
            'number_of_splits' => 'integer|min:1',
            'item_groups' => 'array',
            'custom_amounts' => 'array',
        ]);

        if ($validated['split_type'] === 'equally') {
            $splits = $this->billService->splitBillEqually($bill, $validated['number_of_splits']);
        } elseif ($validated['split_type'] === 'by_item') {
            $splits = $this->billService->splitBillByItem($bill, $validated['item_groups']);
        } else {
            $splits = $this->billService->splitBillCustom($bill, $validated['custom_amounts']);
        }

        return response()->json([
            'splits' => $splits,
            'status' => 'success',
        ]);
    }

    public function processPayment(Request $request, Bill $bill)
    {
        Gate::authorize('cashier');

        $validated = $request->validate([
            'amount_received' => 'required|numeric|min:0',
            'cashier_id' => 'required|exists:users,id',
        ]);

        $result = $this->billService->processPayment($bill, $validated['amount_received'], $validated['cashier_id']);

        return response()->json([
            'payment_id' => $result['payment']->id,
            'change_due' => $result['change_due'],
            'bill_status' => $result['status'],
        ]);
    }

    public function receipt(Bill $bill)
    {
        Gate::authorize('cashier');

        $receipt = $this->billService->generateReceipt($bill);

        return response($receipt, 200)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename=bill_' . $bill->id . '.txt');
    }
}
