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
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    categories: MenuCategory[];
}>();

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
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Menu Categories
                </h2>
                <PrimaryButton @click="openCreateModal">
                    Add Category
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto p-6 text-gray-900">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Name
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Description
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Items Count
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
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="category in categories" :key="category.id">
                                    <td class="whitespace-nowrap px-6 py-4">
                                        {{ category.name }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ category.description || '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        {{ category.menu_items_count }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        {{ category.sort_order }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span
                                            :class="
                                                category.is_active
                                                    ? 'bg-green-100 text-green-800'
                                                    : 'bg-red-100 text-red-800'
                                            "
                                            class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                        >
                                            {{
                                                category.is_active ? 'Active' : 'Inactive'
                                            }}
                                        </span>
                                    </td>
                                    <td
                                        class="space-x-2 whitespace-nowrap px-6 py-4 text-sm font-medium"
                                    >
                                        <button
                                            @click="openEditModal(category)"
                                            class="text-indigo-600 hover:text-indigo-900"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            @click="
                                                confirmCategoryDeletion(
                                                    category.id
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
                        <InputLabel for="name" value="Name" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            required
                            maxlength="80"
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
                        <InputError :message="form.errors.sort_order" class="mt-2" />
                    </div>

                    <div class="flex items-center">
                        <input
                            type="checkbox"
                            id="is_active"
                            v-model="form.is_active"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                        />
                        <label
                            for="is_active"
                            class="ml-2 block text-sm text-gray-900"
                            >Active</label
                        >
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
                            {{ editingCategory ? 'Update Category' : 'Create Category' }}
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
                    This action cannot be undone. Categories with associated menu items
                    may not be deletable.
                </p>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="confirmingCategoryDeletion = false">
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