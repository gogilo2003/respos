# Milestone 3: Menu Management (Admin) - Complete Implementation Guide

## Overview
This milestone implements full CRUD (Create, Read, Update, Delete) functionality for Menu Categories and Menu Items, accessible only to admin users. The implementation follows the repository pattern already established in your project and mirrors the User CRUD implementation.

---

## Database Schema Reference

### menu_categories table
- `id` - BIGINT UNSIGNED (Primary Key)
- `name` - VARCHAR(80) NOT NULL
- `description` - VARCHAR(200) NULL
- `sort_order` - SMALLINT UNSIGNED DEFAULT 0
- `is_active` - TINYINT(1) DEFAULT 1
- `created_at` - TIMESTAMP

### menu_items table
- `id` - BIGINT UNSIGNED (Primary Key)
- `category_id` - BIGINT UNSIGNED (Foreign Key to menu_categories)
- `name` - VARCHAR(80) NOT NULL
- `description` - VARCHAR(200) NULL
- `base_price` - DECIMAL(10,2) NOT NULL
- `tax_inclusive` - TINYINT(1) DEFAULT 1
- `prep_time_min` - TINYINT UNSIGNED DEFAULT 10
- `image_url` - VARCHAR(255) NULL
- `modifier_groups` - JSON NULL
- `is_available` - TINYINT(1) DEFAULT 1
- `sort_order` - SMALLINT UNSIGNED DEFAULT 0
- `created_at` - TIMESTAMP
- `updated_at` - TIMESTAMP

---

## Part 1: Menu Categories CRUD (Backend + Frontend)

### Step 1.1: Create Repository Interface
**File:** `app/Interfaces/Repositories/MenuCategoryRepositoryInterface.php`

**What to do:**
- Create interface extending `RepositoryInterface`
- Add any category-specific methods if needed (e.g., `getActiveCategories()`, `reorder()`)

**Code structure:**
```php
<?php
namespace App\Interfaces\Repositories;

interface MenuCategoryRepositoryInterface extends RepositoryInterface
{
    public function getActiveCategories();
    public function getCategoriesWithItemCount();
}
```

---

### Step 1.2: Create Concrete Repository
**File:** `app/Repositories/MenuCategoryRepository.php`

**What to do:**
- Extend `BaseRepository`
- Implement `MenuCategoryRepositoryInterface`
- Set model to `MenuCategory::class`
- Implement custom methods

**Code structure:**
```php
<?php
namespace App\Repositories;

use App\Interfaces\Repositories\MenuCategoryRepositoryInterface;
use App\Models\MenuCategory;

class MenuCategoryRepository extends BaseRepository implements MenuCategoryRepositoryInterface
{
    public function __construct(MenuCategory $model)
    {
        parent::__construct($model);
    }

    public function getActiveCategories()
    {
        return $this->model->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function getCategoriesWithItemCount()
    {
        return $this->model->withCount('menuItems')
            ->orderBy('sort_order')
            ->get();
    }
}
```

---

### Step 1.3: Register Repository in Service Provider
**File:** `app/Providers/AppServiceProvider.php`

**What to do:**
- Add binding in the `register()` method
- Bind interface to concrete implementation

**Add this line:**
```php
$this->app->bind(
    \App\Interfaces\Repositories\MenuCategoryRepositoryInterface::class,
    \App\Repositories\MenuCategoryRepository::class
);
```

---

### Step 1.4: Create Controller
**File:** `app/Http/Controllers/MenuCategoryController.php`

**What to do:**
- Inject `MenuCategoryRepositoryInterface`
- Implement index, store, update, destroy methods
- Use `Gate::authorize('admin')` for authorization
- Return Inertia responses for index
- Return redirects with messages for mutations

**Code structure:**
```php
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
```

---

### Step 1.5: Add Routes
**File:** `routes/web.php`

**What to do:**
- Add resource routes for menu categories
- Group under middleware if needed

**Add these routes:**
```php
Route::prefix('menu-categories')
    ->name('menu-categories')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/', [MenuCategoryController::class, 'index']);
        Route::post('/', [MenuCategoryController::class, 'store'])->name('.store');
        Route::patch('/{category}', [MenuCategoryController::class, 'update'])->name('.update');
        Route::delete('/{category}', [MenuCategoryController::class, 'destroy'])->name('.destroy');
    });
```

**Don't forget to import:**
```php
use App\Http\Controllers\MenuCategoryController;
```

---

### Step 1.6: Create TypeScript Interfaces
**File:** `resources/js/interfaces/menu.ts`

**What to do:**
- Define TypeScript interfaces for MenuCategory and MenuItem
- Export for use in Vue components

**Code:**
```typescript
export interface MenuCategory {
    id: number;
    name: string;
    description: string | null;
    sort_order: number;
    is_active: boolean;
    created_at: string;
    menu_items_count?: number;
}

export interface MenuItem {
    id: number;
    category_id: number;
    name: string;
    description: string | null;
    base_price: string | number;
    tax_inclusive: boolean;
    prep_time_min: number;
    image_url: string | null;
    modifier_groups: any[] | null;
    is_available: boolean;
    sort_order: number;
    created_at: string;
    updated_at: string;
    category?: MenuCategory;
}
```

---

### Step 1.7: Create Vue Index Page
**File:** `resources/js/Pages/MenuCategories/Index.vue`

**What to do:**
- Create a page similar to `Users/Index.vue`
- Include table with categories
- Add Create/Edit modal with form
- Add Delete confirmation modal
- Use Inertia form helpers
- Display item count per category

**Key features:**
- Header with "Add Category" button
- Table showing: Name, Description, Items Count, Sort Order, Status, Actions
- Modal for Create/Edit with fields:
  - Name (required, max 80)
  - Description (optional, max 200)
  - Sort Order (number, default 0)
  - Is Active (checkbox/toggle)
- Delete confirmation dialog
- Success/error messages

**Component structure:**
```vue
<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import type { MenuCategory } from '@/interfaces/menu';

const props = defineProps<{
    categories: MenuCategory[];
}>();

// State management
const showModal = ref(false);
const editingCategory = ref<MenuCategory | null>(null);
const confirmingDeletion = ref(false);
const categoryToDelete = ref<number | null>(null);

// Form
const form = useForm({
    name: '',
    description: '',
    sort_order: 0,
    is_active: true,
});

// Methods
const openCreateModal = () => { /* ... */ };
const openEditModal = (category: MenuCategory) => { /* ... */ };
const submit = () => { /* ... */ };
const closeModal = () => { /* ... */ };
const confirmDeletion = (id: number) => { /* ... */ };
const deleteCategory = () => { /* ... */ };
</script>

<template>
    <Head title="Menu Categories" />
    
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold">Menu Categories</h2>
                <PrimaryButton @click="openCreateModal">
                    Add Category
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <!-- Table with categories -->
            <!-- Create/Edit Modal -->
            <!-- Delete Confirmation Modal -->
        </div>
    </AuthenticatedLayout>
</template>
```

---

### Step 1.8: Add Navigation Link
**File:** `resources/js/Components/Sidebar.vue` or navigation component

**What to do:**
- Add link to Menu Categories page
- Only show for admin users

**Example:**
```vue
<NavLink :href="route('menu-categories')" :active="route().current('menu-categories')">
    Menu Categories
</NavLink>
```

---

## Part 2: Menu Items CRUD (Backend + Frontend)

### Step 2.1: Create Repository Interface
**File:** `app/Interfaces/Repositories/MenuItemRepositoryInterface.php`

**Code structure:**
```php
<?php
namespace App\Interfaces\Repositories;

interface MenuItemRepositoryInterface extends RepositoryInterface
{
    public function getAvailableItems();
    public function getItemsByCategory($categoryId);
    public function getItemsWithCategory();
}
```

---

### Step 2.2: Create Concrete Repository
**File:** `app/Repositories/MenuItemRepository.php`

**What to do:**
- Extend `BaseRepository`
- Implement `MenuItemRepositoryInterface`
- Set model to `MenuItem::class`
- Implement custom methods with eager loading

**Code structure:**
```php
<?php
namespace App\Repositories;

use App\Interfaces\Repositories\MenuItemRepositoryInterface;
use App\Models\MenuItem;

class MenuItemRepository extends BaseRepository implements MenuItemRepositoryInterface
{
    public function __construct(MenuItem $model)
    {
        parent::__construct($model);
    }

    public function getAvailableItems()
    {
        return $this->model->where('is_available', true)
            ->with('category')
            ->orderBy('sort_order')
            ->get();
    }

    public function getItemsByCategory($categoryId)
    {
        return $this->model->where('category_id', $categoryId)
            ->orderBy('sort_order')
            ->get();
    }

    public function getItemsWithCategory()
    {
        return $this->model->with('category')
            ->orderBy('sort_order')
            ->get();
    }
}
```

---

### Step 2.3: Register Repository in Service Provider
**File:** `app/Providers/AppServiceProvider.php`

**Add:**
```php
$this->app->bind(
    \App\Interfaces\Repositories\MenuItemRepositoryInterface::class,
    \App\Repositories\MenuItemRepository::class
);
```

---

### Step 2.4: Create Controller
**File:** `app/Http/Controllers/MenuItemController.php`

**What to do:**
- Inject both `MenuItemRepositoryInterface` and `MenuCategoryRepositoryInterface`
- Implement CRUD methods
- Handle image upload in store/update (Part 3)
- Validate all fields including price, prep time, etc.

**Code structure:**
```php
<?php
namespace App\Http\Controllers;

use App\Interfaces\Repositories\MenuItemRepositoryInterface;
use App\Interfaces\Repositories\MenuCategoryRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class MenuItemController extends Controller
{
    protected MenuItemRepositoryInterface $itemRepository;
    protected MenuCategoryRepositoryInterface $categoryRepository;

    public function __construct(
        MenuItemRepositoryInterface $itemRepository,
        MenuCategoryRepositoryInterface $categoryRepository
    ) {
        $this->itemRepository = $itemRepository;
        $this->categoryRepository = $categoryRepository;
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
            'image_url' => 'nullable|string|max:255', // Will handle file upload in Part 3
            'is_available' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

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
            'image_url' => 'nullable|string|max:255',
            'is_available' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $this->itemRepository->update($id, $validated);

        return redirect()->back()->with('message', 'Menu item updated successfully.');
    }

    public function destroy($id)
    {
        Gate::authorize('admin');

        $this->itemRepository->delete($id);

        return redirect()->back()->with('message', 'Menu item deleted successfully.');
    }
}
```

---

### Step 2.5: Add Routes
**File:** `routes/web.php`

**Add:**
```php
Route::prefix('menu-items')
    ->name('menu-items')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/', [MenuItemController::class, 'index']);
        Route::post('/', [MenuItemController::class, 'store'])->name('.store');
        Route::patch('/{item}', [MenuItemController::class, 'update'])->name('.update');
        Route::delete('/{item}', [MenuItemController::class, 'destroy'])->name('.destroy');
    });
```

**Import:**
```php
use App\Http\Controllers\MenuItemController;
```

---

### Step 2.6: Create Vue Index Page
**File:** `resources/js/Pages/MenuItems/Index.vue`

**What to do:**
- Similar structure to MenuCategories/Index.vue
- More complex form with additional fields
- Display category name in table
- Show image thumbnail if available
- Format price display

**Key features:**
- Table columns: Image, Name, Category, Description, Price, Prep Time, Status, Actions
- Form fields:
  - Category (dropdown/select)
  - Name (required, max 80)
  - Description (optional, max 200)
  - Base Price (required, number, min 0)
  - Tax Inclusive (checkbox)
  - Prep Time (minutes, required, 1-255)
  - Image URL (text input for now, file upload in Part 3)
  - Is Available (checkbox/toggle)
  - Sort Order (number, default 0)
- Filter by category (optional enhancement)
- Search functionality (optional enhancement)

---

### Step 2.7: Add Navigation Link
**File:** Navigation component

**Add:**
```vue
<NavLink :href="route('menu-items')" :active="route().current('menu-items')">
    Menu Items
</NavLink>
```

---

## Part 3: Image Upload Handling for Menu Items

### Step 3.1: Create Image Upload Service (Optional but Recommended)
**File:** `app/Services/ImageUploadService.php`

**What to do:**
- Handle file validation
- Store images in `storage/app/public/menu-items`
- Generate unique filenames
- Delete old images when updating
- Return public URL

**Code structure:**
```php
<?php
namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUploadService
{
    public function uploadMenuItemImage(UploadedFile $file): string
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('menu-items', $filename, 'public');
        
        return Storage::url($path);
    }

    public function deleteMenuItemImage(?string $imageUrl): void
    {
        if (!$imageUrl) {
            return;
        }

        $path = str_replace('/storage/', '', $imageUrl);
        Storage::disk('public')->delete($path);
    }
}
```

---

### Step 3.2: Update MenuItemController
**File:** `app/Http/Controllers/MenuItemController.php`

**What to do:**
- Inject `ImageUploadService`
- Update validation to accept file upload
- Handle image upload in store method
- Handle image replacement in update method
- Delete old image when updating with new one

**Updated validation:**
```php
$validated = $request->validate([
    'category_id' => 'required|exists:menu_categories,id',
    'name' => 'required|string|max:80',
    'description' => 'nullable|string|max:200',
    'base_price' => 'required|numeric|min:0',
    'tax_inclusive' => 'required|boolean',
    'prep_time_min' => 'required|integer|min:1|max:255',
    'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // 2MB max
    'is_available' => 'required|boolean',
    'sort_order' => 'nullable|integer|min:0',
]);

if ($request->hasFile('image')) {
    $validated['image_url'] = $this->imageUploadService->uploadMenuItemImage($request->file('image'));
}
```

---

### Step 3.3: Create Storage Link
**Command to run:**
```bash
php artisan storage:link
```

**What it does:**
- Creates symbolic link from `public/storage` to `storage/app/public`
- Allows public access to uploaded images

---

### Step 3.4: Update Vue Form for File Upload
**File:** `resources/js/Pages/MenuItems/Index.vue`

**What to do:**
- Change form to handle file uploads
- Use `FormData` instead of regular form submission
- Add file input with preview
- Show existing image when editing

**Key changes:**
```typescript
// Use FormData for file uploads
const submitWithFile = () => {
    const formData = new FormData();
    formData.append('category_id', form.category_id);
    formData.append('name', form.name);
    // ... append all fields
    if (imageFile.value) {
        formData.append('image', imageFile.value);
    }
    
    if (editingItem.value) {
        formData.append('_method', 'PATCH');
        router.post(route('menu-items.update', editingItem.value.id), formData);
    } else {
        router.post(route('menu-items.store'), formData);
    }
};
```

**Template:**
```vue
<div>
    <InputLabel for="image" value="Image" />
    <input
        type="file"
        id="image"
        accept="image/*"
        @change="handleImageChange"
        class="mt-1 block w-full"
    />
    <div v-if="imagePreview" class="mt-2">
        <img :src="imagePreview" alt="Preview" class="h-32 w-32 object-cover rounded" />
    </div>
    <InputError :message="form.errors.image" class="mt-2" />
</div>
```

---

## Testing Checklist

### Backend Tests (Optional but Recommended)
**File:** `tests/Feature/MenuCategoryTest.php` and `tests/Feature/MenuItemTest.php`

**What to test:**
- [ ] Admin can create category
- [ ] Admin can update category
- [ ] Admin can delete category
- [ ] Non-admin cannot access category endpoints
- [ ] Validation rules work correctly
- [ ] Admin can create menu item
- [ ] Admin can update menu item
- [ ] Admin can delete menu item
- [ ] Image upload works correctly
- [ ] Old images are deleted when updating

**Run tests:**
```bash
php artisan test
```

---

### Manual Testing Checklist

#### Menu Categories:
- [ ] Navigate to Menu Categories page
- [ ] Create a new category
- [ ] Edit an existing category
- [ ] Toggle category active status
- [ ] Delete a category (should fail if it has items)
- [ ] Verify sort order works
- [ ] Check validation errors display correctly

#### Menu Items:
- [ ] Navigate to Menu Items page
- [ ] Create a new item with all fields
- [ ] Create an item with image upload
- [ ] Edit an existing item
- [ ] Update item image (verify old image is deleted)
- [ ] Toggle item availability
- [ ] Delete an item
- [ ] Verify category dropdown shows active categories
- [ ] Check price formatting
- [ ] Verify validation errors

#### Integration:
- [ ] Create category, then create items in that category
- [ ] Verify item count shows correctly on categories page
- [ ] Try to delete category with items (should handle gracefully)
- [ ] Check that public menu view shows the new items
- [ ] Verify cart functionality still works with new items

---

## Summary of Files to Create/Modify

### New Files to Create:
1. `app/Interfaces/Repositories/MenuCategoryRepositoryInterface.php`
2. `app/Repositories/MenuCategoryRepository.php`
3. `app/Http/Controllers/MenuCategoryController.php`
4. `app/Interfaces/Repositories/MenuItemRepositoryInterface.php`
5. `app/Repositories/MenuItemRepository.php`
6. `app/Http/Controllers/MenuItemController.php`
7. `app/Services/ImageUploadService.php`
8. `resources/js/interfaces/menu.ts`
9. `resources/js/Pages/MenuCategories/Index.vue`
10. `resources/js/Pages/MenuItems/Index.vue`
11. `tests/Feature/MenuCategoryTest.php` (optional)
12. `tests/Feature/MenuItemTest.php` (optional)

### Files to Modify:
1. `app/Providers/AppServiceProvider.php` - Add repository bindings
2. `routes/web.php` - Add routes for both controllers
3. Navigation component (Sidebar.vue or similar) - Add menu links

### Commands to Run:
```bash
# Create storage link (if not already done)
php artisan storage:link

# Run tests
php artisan test

# Format code
vendor/bin/pint

# Build frontend
npm run build

# Or run dev server
npm run dev
```

---

## Estimated Time Breakdown

- **Part 1 (Menu Categories):** 2-3 hours
  - Backend (repositories, controller, routes): 1 hour
  - Frontend (Vue page, forms, modals): 1-2 hours

- **Part 2 (Menu Items):** 3-4 hours
  - Backend (repositories, controller, routes): 1 hour
  - Frontend (Vue page with more complex form): 2-3 hours

- **Part 3 (Image Upload):** 1-2 hours
  - Service creation and controller updates: 30 minutes
  - Frontend file upload handling: 30 minutes - 1 hour
  - Testing and debugging: 30 minutes

**Total Estimated Time:** 6-9 hours

---

## Tips for Success

1. **Start with Categories First** - They're simpler and will help you establish the pattern
2. **Test as You Go** - Don't wait until everything is built to test
3. **Reuse Existing Code** - Copy from UserController and Users/Index.vue as templates
4. **Use Browser DevTools** - Check network tab for API responses and errors
5. **Check Laravel Logs** - `storage/logs/laravel.log` for backend errors
6. **Commit Frequently** - Commit after each working part (categories, items, images)
7. **Mobile Testing** - Test on mobile viewport since this is a restaurant app
8. **Seed Some Data** - Create a seeder with sample categories and items for testing

---

## Next Steps After Completion

Once Milestone 3 is complete, you'll have:
- ✅ Full admin interface for managing menu categories
- ✅ Full admin interface for managing menu items
- ✅ Image upload capability for menu items
- ✅ Foundation for the public menu display (already partially done)

This sets you up perfectly for:
- **Milestone 4:** Tables, QR Codes & Session Management
- **Milestone 5:** Complete the customer ordering flow
- **Milestone 6:** Waiter interface to manage orders

---

## Questions to Consider

1. **Do you want to add bulk operations?** (e.g., bulk activate/deactivate items)
2. **Should categories be deletable if they have items?** (Soft delete vs. hard delete)
3. **Do you want drag-and-drop reordering?** (For sort_order)
4. **Should there be a preview mode?** (View menu as customer would see it)
5. **Do you need modifier groups now?** (e.g., size, toppings) - Currently stored as JSON

Let me know if you want me to implement any specific part or if you need clarification on any step!
