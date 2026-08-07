<?php

namespace App\Http\Controllers;

use App\Domain\Billing\Services\BillService;
use App\Http\Requests\StoreBillRequest;
use App\Http\Resources\BillResource;
use App\Models\Bill;
use App\Models\TableSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class BillController extends Controller
{
    public function __construct(private BillService $billService)
    {
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Bill::class);

        $bills = $this->billService->all();
        $formattedBills = BillResource::collection($bills)->resolve();

        if ($request->wantsJson() && ! $request->header('X-Inertia')) {
            return BillResource::collection($bills)->response()->setStatusCode(200);
        }

        return Inertia::render('Bills/Index', [
            'bills' => $formattedBills,
        ]);
    }

    public function show(Request $request, Bill $bill)
    {
        Gate::authorize('view', $bill);

        $data = $this->billService->retrieve($bill->id);
        $formattedBill = (new BillResource($data))->resolve();

        if ($request->wantsJson() && ! $request->header('X-Inertia')) {
            return (new BillResource($data))->response()->setStatusCode(200);
        }

        return Inertia::render('Bills/Show', [
            'bill' => $formattedBill,
        ]);
    }

    public function store(StoreBillRequest $request)
    {
        Gate::authorize('create', Bill::class);

        $session = TableSession::findOrFail($request->validated('session_id'));
        $order = $session->orders()->latest()->firstOrFail();

        $bill = $this->billService->generateFromOrder($order);
        $resource = new BillResource($bill);

        if ($request->wantsJson() && ! $request->header('X-Inertia')) {
            return $resource->response()->setStatusCode(201);
        }

        return redirect()->route('bills.show', $bill->id)->with('message', 'Bill generated successfully.');
    }

    public function void(Request $request, Bill $bill)
    {
        Gate::authorize('void', $bill);

        $data = $this->billService->void($bill->id);
        $resource = new BillResource($data);

        if ($request->wantsJson() && ! $request->header('X-Inertia')) {
            return $resource->response()->setStatusCode(200);
        }

        return redirect()->back()->with('message', 'Bill voided successfully.');
    }

    public function destroy(Request $request, Bill $bill)
    {
        Gate::authorize('delete', $bill);

        $this->billService->delete($bill->id);

        if ($request->wantsJson() && ! $request->header('X-Inertia')) {
            return response()->json(null, 204);
        }

        return redirect()->route('bills.index')->with('message', 'Bill deleted successfully.');
    }
}
