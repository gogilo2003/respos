<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Models\Bill;
use App\Services\BillService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PaymentController extends Controller
{
    protected BillService $billService;

    public function __construct(BillService $billService)
    {
        $this->billService = $billService;
    }

    public function store(StorePaymentRequest $request, Bill $bill)
    {
        Gate::authorize('cashier');

        $validated = $request->validated();

        $result = $this->billService->processPayment(
            $bill,
            (float) $validated['amount_received'],
            auth()->id()
        );

        return response()->json([
            'payment_id' => $result['payment']->id,
            'change_due' => $result['change_due'],
            'bill_status' => $result['status'],
        ]);
    }
}
