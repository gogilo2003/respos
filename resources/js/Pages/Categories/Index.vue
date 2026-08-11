<script setup lang="ts">
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import type { MenuCategory } from '@/interfaces/menu';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    categories: MenuCategory[];
}>();

const searchQuery = ref('');
const confirmingCategoryDeletion = ref(false);
const categoryToDelete = ref<number | null>(null);
const editingCategory = ref<MenuCategory | null>(null);
const showCategoryModal = ref(false);

const form = useForm({
    name: '',
    description: '',
    sort_order: '0',
    is_active: true,
});

const filteredCategories = computed(() => {
    if (!searchQuery.value.trim()) {
        return props.categories;
    }
    const q = searchQuery.value.toLowerCase();
    return props.categories.filter(
        (c) =>
            c.name.toLowerCase().includes(q) ||
            (c.description && c.description.toLowerCase().includes(q)),
    );
});

const openCreateModal = () => {
    editingCategory.value = null;
    form.reset();
    form.is_active = true;
    form.sort_order = '0';
    showCategoryModal.value = true;
};

const openEditModal = (category: MenuCategory) => {
    editingCategory.value = category;
    form.clearErrors();
    form.name = category.name;
    form.description = category.description || '';
    form.sort_order = category.sort_order.toString();
    form.is_active = category.is_active;
    showCategoryModal.value = true;
};

const submit = () => {
    if (editingCategory.value) {
        form.patch(route('menu-categories.update', editingCategory.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('menu-categories.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const toggleActive = (category: MenuCategory) => {
    router.patch(
        route('menu-categories.toggle-active', category.id),
        {},
        {
            preserveScroll: true,
        },
    );
};

const closeModal = () => {
    showCategoryModal.value = false;
    form.reset();
};

const confirmCategoryDeletion = (id: number) => {
    categoryToDelete.value = id;
    confirmingCategoryDeletion.value = true;
};

const deleteCategory = () => {
    if (categoryToDelete.value) {
        form.delete(route('menu-categories.destroy', categoryToDelete.value), {
            onSuccess: () => (confirmingCategoryDeletion.value = false),
        });
    }
};
</script>

<template>
    <Head title="Menu Categories" />

    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h2
                        class="text-xl font-semibold leading-tight text-gray-800"
                    >
                        Menu Categories Management
                    </h2>
                    <p class="mt-1 text-xs text-gray-500">
                        Organize your restaurant food & beverage menu categories
                    </p>
                </div>
                <PrimaryButton @click="openCreateModal">
                    + Add Category
                </PrimaryButton>
            </div>
        </template>

        <div class="py-8">
            <div class="px-4 sm:px-6 lg:px-8">
                <!-- Search & Filters Bar -->
                <div
                    class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="relative w-full max-w-xs">
                        <TextInput
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search categories..."
                            class="w-full pl-9 text-sm"
                        />
                        <span class="absolute left-3 top-2.5 text-gray-400"
                            >🔍</span
                        >
                    </div>

                    <div class="text-xs text-gray-500">
                        Total:
                        <span class="font-bold text-gray-800">{{
                            categories.length
                        }}</span>
                        categories
                    </div>
                </div>

                <div
                    class="overflow-hidden border border-gray-200 bg-white shadow-sm sm:rounded-lg"
                >
                    <div class="overflow-x-auto p-6 text-gray-900">
                        <div
                            v-if="filteredCategories.length === 0"
                            class="py-12 text-center text-sm text-gray-500"
                        >
                            No menu categories found.
                        </div>

                        <table
                            v-else
                            class="min-w-full divide-y divide-gray-200"
                        >
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Category Name
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Description
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Menu Items
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Sort Order
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Status
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr
                                    v-for="category in filteredCategories"
                                    :key="category.id"
                                >
                                    <td
                                        class="whitespace-nowrap px-6 py-4 font-semibold text-gray-900"
                                    >
                                        {{ category.name }}
                                    </td>
                                    <td
                                        class="max-w-xs truncate px-6 py-4 text-sm text-gray-600"
                                    >
                                        {{ category.description || '-' }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm"
                                    >
                                        <span
                                            class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700"
                                        >
                                            {{ category.menu_items_count ?? 0 }}
                                            items
                                        </span>
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm text-gray-700"
                                    >
                                        {{ category.sort_order }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm"
                                    >
                                        <button
                                            @click="toggleActive(category)"
                                            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold transition"
                                            :class="
                                                category.is_active
                                                    ? 'bg-green-100 text-green-800 hover:bg-green-200'
                                                    : 'bg-red-100 text-red-800 hover:bg-red-200'
                                            "
                                        >
                                            <span
                                                class="h-1.5 w-1.5 rounded-full"
                                                :class="
                                                    category.is_active
                                                        ? 'bg-green-600'
                                                        : 'bg-red-600'
                                                "
                                            ></span>
                                            {{
                                                category.is_active
                                                    ? 'Active'
                                                    : 'Inactive'
                                            }}
                                        </button>
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium"
                                    >
                                        <button
                                            @click="openEditModal(category)"
                                            class="mr-4 text-indigo-600 hover:text-indigo-900"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            @click="
                                                confirmCategoryDeletion(
                                                    category.id,
                                                )
                                            "
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Modal -->
        <Modal :show="showCategoryModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ editingCategory ? 'Edit Category' : 'Add New Category' }}
                </h2>

                <form @submit.prevent="submit" class="mt-6 space-y-4">
                    <div>
                        <InputLabel for="name" value="Category Name" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            required
                            maxlength="80"
                            placeholder="e.g. Main Courses, Drinks, Desserts"
                        />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="description" value="Description" />
                        <TextInput
                            id="description"
                            v-model="form.description"
                            type="text"
                            class="mt-1 block w-full"
                            maxlength="200"
                            placeholder="Optional category summary"
                        />
                        <InputError
                            :message="form.errors.description"
                            class="mt-2"
                        />
                    </div>

                    <div>
                        <InputLabel for="sort_order" value="Sort Order" />
                        <TextInput
                            id="sort_order"
                            v-model="form.sort_order"
                            type="number"
                            min="0"
                            class="mt-1 block w-full"
                        />
                        <InputError
                            :message="form.errors.sort_order"
                            class="mt-2"
                        />
                    </div>

                    <div class="flex items-center pt-2">
                        <input
                            type="checkbox"
                            id="is_active"
                            v-model="form.is_active"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                        />
                        <label
                            for="is_active"
                            class="ml-2 block text-sm font-medium text-gray-900"
                        >
                            Active (visible to customer ordering system)
                        </label>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <SecondaryButton @click="closeModal">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton
                            class="ms-3"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            {{
                                editingCategory
                                    ? 'Update Category'
                                    : 'Create Category'
                            }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal
            :show="confirmingCategoryDeletion"
            @close="confirmingCategoryDeletion = false"
        >
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Are you sure you want to delete this category?
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    This action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton
                        @click="confirmingCategoryDeletion = false"
                    >
                        Cancel
                    </SecondaryButton>
                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteCategory"
                    >
                        Delete Category
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
