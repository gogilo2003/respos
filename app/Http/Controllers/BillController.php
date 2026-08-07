<?php

namespace App\Http\Controllers;

use App\Domain\Billing\Services\BillService;
use App\Http\Requests\StoreBillRequest;
use App\Http\Resources\BillResource;
use App\Models\Bill;
use App\Models\TableSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class BillController extends Controller
{
    public function __construct(private BillService $billService)
    {
    }

    public function index(): JsonResponse
    {
        Gate::authorize('viewAny', Bill::class);

        $bills = $this->billService->all();

        return BillResource::collection($bills)->response()->setStatusCode(200);
    }

    public function show(Bill $bill): JsonResponse
    {
        Gate::authorize('view', $bill);

        $data = $this->billService->retrieve($bill->id);

        return (new BillResource($data))->response()->setStatusCode(200);
    }

    public function store(StoreBillRequest $request): JsonResponse
    {
        Gate::authorize('create', Bill::class);

        $session = TableSession::findOrFail($request->validated('session_id'));
        $order = $session->orders()->latest()->firstOrFail();

        $bill = $this->billService->generateFromOrder($order);

        return (new BillResource($bill))->response()->setStatusCode(201);
    }

    public function destroy(Bill $bill): JsonResponse
    {
        Gate::authorize('delete', $bill);

        $this->billService->delete($bill->id);

        return response()->json(null, 204);
    }
}
