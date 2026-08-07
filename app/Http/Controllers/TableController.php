<?php

namespace App\Http\Controllers;

use App\Interfaces\Repositories\TableRepositoryInterface;
use App\Models\RestaurantTable;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TableController extends Controller
{
    public function __construct(
        protected TableRepositoryInterface $tableRepository,
        protected QrCodeService $qrCodeService
    ) {
    }

    public function index()
    {
        Gate::authorize('admin');

        $tables = $this->tableRepository->all()->load('qrCode');

        // Ensure every table has a QR Code record
        foreach ($tables as $table) {
            if (! $table->qrCode) {
                $this->qrCodeService->getOrCreateQrCode($table);
            }
        }

        return Inertia::render('Tables/Index', [
            'tables' => $this->tableRepository->all()->load('qrCode'),
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            'table_number' => 'required|string|max:20',
            'capacity' => 'required|integer|min:1|max:20',
            'location' => 'nullable|string|max:80',
            'status' => 'required|string|in:available,occupied,ordering,preparing,served,billing,paid,cleaning,reserved',
            'is_active' => 'required|boolean',
        ]);

        $table = $this->tableRepository->create($validated);
        $this->qrCodeService->getOrCreateQrCode($table);

        return redirect()->back()->with('message', 'Table created successfully.');
    }

    public function update(Request $request, $id)
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            'table_number' => 'required|string|max:20',
            'capacity' => 'required|integer|min:1|max:20',
            'location' => 'nullable|string|max:80',
            'status' => 'required|string|in:available,occupied,ordering,preparing,served,billing,paid,cleaning,reserved',
            'is_active' => 'required|boolean',
        ]);

        $this->tableRepository->update($id, $validated);

        return redirect()->back()->with('message', 'Table updated successfully.');
    }

    public function qrImage(RestaurantTable $table)
    {
        Gate::authorize('admin');

        $qrCode = $this->qrCodeService->getOrCreateQrCode($table);
        $svg = $this->qrCodeService->generateSvg($qrCode->payload);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => "inline; filename=\"table-{$table->table_number}-qr.svg\"",
        ]);
    }

    public function regenerateQr(RestaurantTable $table)
    {
        Gate::authorize('admin');

        $this->qrCodeService->regenerate($table);

        return redirect()->back()->with('message', 'QR Code regenerated successfully.');
    }

    public function destroy($id)
    {
        Gate::authorize('admin');

        $this->tableRepository->delete($id);

        return redirect()->back()->with('message', 'Table deleted successfully.');
    }
}
