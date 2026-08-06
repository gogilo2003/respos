<?php

namespace App\Http\Controllers;

use App\Http\Requests\Bill\ProcessPaymentRequest;
use App\Http\Requests\Bill\SplitBillRequest;
use App\Http\Requests\StoreBillRequest;
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

    public function generate(StoreBillRequest $request)
    {
        Gate::authorize('cashier');

        $validated = $request->validated();

        $bill = $this->billService->generateBill($validated['session_id'], auth()->id());

        return response()->json([
            'bill_id' => $bill->id,
            'status' => 'success',
            'grand_total' => $bill->grand_total,
        ]);
    }

    public function split(SplitBillRequest $request, Bill $bill)
    {
        Gate::authorize('cashier');

        $validated = $request->validated();

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

    public function processPayment(ProcessPaymentRequest $request, Bill $bill)
    {
        Gate::authorize('cashier');

        $validated = $request->validated();

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
