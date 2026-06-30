<?php

namespace App\Http\Controllers;

use App\Interfaces\Repositories\MenuCategoryRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class MenuCategoryController extends Controller
{
    protected MenuCategoryRepositoryInterface $categoryRepository;

    public function __construct(MenuCategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function index()
    {
        Gate::authorize('admin');

        return Inertia::render('MenuCategories/Index', [
            'categories' => $this->categoryRepository->getCategoriesWithItemCount(),
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            'name' => 'required|string|max:80',
            'description' => 'nullable|string|max:200',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'required|boolean',
        ]);

        $this->categoryRepository->create($validated);

        return redirect()->back()->with('message', 'Category created successfully.');
    }

    public function update(Request $request, $id)
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            'name' => 'required|string|max:80',
            'description' => 'nullable|string|max:200',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'required|boolean',
        ]);

        $this->categoryRepository->update($id, $validated);

        return redirect()->back()->with('message', 'Category updated successfully.');
    }

    public function destroy($id)
    {
        Gate::authorize('admin');

        $this->categoryRepository->delete($id);

        return redirect()->back()->with('message', 'Category deleted successfully.');
    }
}
