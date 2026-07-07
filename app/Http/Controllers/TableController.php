<?php

namespace App\Http\Controllers;

use App\Interfaces\Repositories\TableRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TableController extends Controller
{
    protected TableRepositoryInterface $tableRepository;

    public function __construct(TableRepositoryInterface $tableRepository)
    {
        $this->tableRepository = $tableRepository;
    }

    public function index()
    {
        Gate::authorize('admin');

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

        $this->tableRepository->create($validated);

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

    public function destroy($id)
    {
        Gate::authorize('admin');

        $this->tableRepository->delete($id);

        return redirect()->back()->with('message', 'Table deleted successfully.');
    }
}
