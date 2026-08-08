<?php

namespace App\Http\Controllers;

use App\Http\Requests\MenuItem\StoreMenuItemRequest;
use App\Http\Requests\MenuItem\UpdateMenuItemRequest;
use App\Interfaces\Repositories\MenuCategoryRepositoryInterface;
use App\Interfaces\Repositories\MenuItemRepositoryInterface;
use App\Models\MenuItem;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class MenuItemController extends Controller
{
    public function __construct(
        protected MenuItemRepositoryInterface $itemRepository,
        protected MenuCategoryRepositoryInterface $categoryRepository,
        protected ImageUploadService $imageUploadService
    ) {}

    public function index()
    {
        Gate::authorize('viewAny', MenuItem::class);

        return Inertia::render('Menu/Index', [
            'items' => $this->itemRepository->getItemsWithCategory(),
            'categories' => $this->categoryRepository->getActiveCategories(),
        ]);
    }

    public function store(StoreMenuItemRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            $validated['image_url'] = $this->imageUploadService->uploadMenuItemImage($request->file('image'));
        }

        $this->itemRepository->create($validated);

        return redirect()->back()->with('message', 'Menu item created successfully.');
    }

    public function update(UpdateMenuItemRequest $request, int $id)
    {
        $item = $this->itemRepository->find($id);
        if (! $item) {
            abort(404);
        }

        $validated = $request->validated();
        $oldImageUrl = $item->image_url;

        if ($request->hasFile('image')) {
            $validated['image_url'] = $this->imageUploadService->uploadMenuItemImage($request->file('image'));
            if ($oldImageUrl) {
                $this->imageUploadService->deleteMenuItemImage($oldImageUrl);
            }
        }

        $this->itemRepository->update($id, $validated);

        return redirect()->back()->with('message', 'Menu item updated successfully.');
    }

    public function toggleAvailability(Request $request, int $id)
    {
        $item = $this->itemRepository->find($id);
        if (! $item) {
            abort(404);
        }

        Gate::authorize('toggleAvailability', $item);

        $this->itemRepository->update($id, [
            'is_available' => ! $item->is_available,
        ]);

        return redirect()->back()->with('message', 'Menu item availability updated successfully.');
    }

    public function destroy(int $id)
    {
        $item = $this->itemRepository->find($id);
        if (! $item) {
            abort(404);
        }

        Gate::authorize('delete', $item);

        if ($item->image_url) {
            $this->imageUploadService->deleteMenuItemImage($item->image_url);
        }

        $this->itemRepository->delete($id);

        return redirect()->back()->with('message', 'Menu item deleted successfully.');
    }
}
