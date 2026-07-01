<?php

namespace App\Http\Controllers;

use App\Interfaces\Repositories\MenuCategoryRepositoryInterface;
use App\Interfaces\Repositories\MenuItemRepositoryInterface;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class MenuItemController extends Controller
{
    protected MenuItemRepositoryInterface $itemRepository;

    protected MenuCategoryRepositoryInterface $categoryRepository;

    protected ImageUploadService $imageUploadService;

    public function __construct(
        MenuItemRepositoryInterface $itemRepository,
        MenuCategoryRepositoryInterface $categoryRepository,
        ImageUploadService $imageUploadService
    ) {
        $this->itemRepository = $itemRepository;
        $this->categoryRepository = $categoryRepository;
        $this->imageUploadService = $imageUploadService;
    }

    public function index()
    {
        Gate::authorize('admin');

        return Inertia::render('MenuItems/Index', [
            'items' => $this->itemRepository->getItemsWithCategory(),
            'categories' => $this->categoryRepository->getActiveCategories(),
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            'category_id' => 'required|exists:menu_categories,id',
            'name' => 'required|string|max:80',
            'description' => 'nullable|string|max:200',
            'base_price' => 'required|numeric|min:0',
            'tax_inclusive' => 'required|boolean',
            'prep_time_min' => 'required|integer|min:1|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_available' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('image')) {
            $validated['image_url'] = $this->imageUploadService->uploadMenuItemImage($request->file('image'));
        }

        $this->itemRepository->create($validated);

        return redirect()->back()->with('message', 'Menu item created successfully.');
    }

    public function update(Request $request, $id)
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            'category_id' => 'required|exists:menu_categories,id',
            'name' => 'required|string|max:80',
            'description' => 'nullable|string|max:200',
            'base_price' => 'required|numeric|min:0',
            'tax_inclusive' => 'required|boolean',
            'prep_time_min' => 'required|integer|min:1|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_available' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $item = $this->itemRepository->find($id);
        $oldImageUrl = $item?->image_url;

        if ($request->hasFile('image')) {
            $validated['image_url'] = $this->imageUploadService->uploadMenuItemImage($request->file('image'));
            if ($oldImageUrl) {
                $this->imageUploadService->deleteMenuItemImage($oldImageUrl);
            }
        }

        $this->itemRepository->update($id, $validated);

        return redirect()->back()->with('message', 'Menu item updated successfully.');
    }

    public function destroy($id)
    {
        Gate::authorize('admin');

        $item = $this->itemRepository->find($id);
        if ($item && $item->image_url) {
            $this->imageUploadService->deleteMenuItemImage($item->image_url);
        }

        $this->itemRepository->delete($id);

        return redirect()->back()->with('message', 'Menu item deleted successfully.');
    }
}
