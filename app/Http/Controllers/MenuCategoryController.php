<?php

namespace App\Http\Controllers;

use App\Http\Requests\MenuCategory\StoreMenuCategoryRequest;
use App\Http\Requests\MenuCategory\UpdateMenuCategoryRequest;
use App\Interfaces\Repositories\MenuCategoryRepositoryInterface;
use App\Models\MenuCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class MenuCategoryController extends Controller
{
    public function __construct(
        protected MenuCategoryRepositoryInterface $categoryRepository
    ) {}

    public function index()
    {
        Gate::authorize('viewAny', MenuCategory::class);

        return Inertia::render('Categories/Index', [
            'categories' => $this->categoryRepository->getCategoriesWithItemCount(),
        ]);
    }

    public function store(StoreMenuCategoryRequest $request)
    {
        $validated = $request->validated();

        $this->categoryRepository->create($validated);

        return redirect()->back()->with('message', 'Category created successfully.');
    }

    public function update(UpdateMenuCategoryRequest $request, int $id)
    {
        $category = $this->categoryRepository->find($id);
        if (! $category) {
            abort(404);
        }

        $validated = $request->validated();

        $this->categoryRepository->update($id, $validated);

        return redirect()->back()->with('message', 'Category updated successfully.');
    }

    public function toggleActive(Request $request, int $id)
    {
        $category = $this->categoryRepository->find($id);
        if (! $category) {
            abort(404);
        }

        Gate::authorize('toggleActive', $category);

        $this->categoryRepository->update($id, [
            'is_active' => ! $category->is_active,
        ]);

        return redirect()->back()->with('message', 'Category status updated successfully.');
    }

    public function destroy(int $id)
    {
        $category = $this->categoryRepository->find($id);
        if (! $category) {
            abort(404);
        }

        Gate::authorize('delete', $category);

        $this->categoryRepository->delete($id);

        return redirect()->back()->with('message', 'Category deleted successfully.');
    }
}
