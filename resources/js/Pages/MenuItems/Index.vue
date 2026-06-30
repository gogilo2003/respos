<script setup lang="ts">
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import type { MenuCategory, MenuItem } from '@/interfaces/menu';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    items: MenuItem[];
    categories: MenuCategory[];
}>();

const confirmingItemDeletion = ref(false);
const itemToDelete = ref<number | null>(null);
const editingItem = ref<MenuItem | null>(null);
const showItemModal = ref(false);
const imageFile = ref<File | null>(null);
const imagePreview = ref<string | null>(null);

const form = useForm({
    category_id: '',
    name: '',
    description: '',
    base_price: '',
    tax_inclusive: true,
    prep_time_min: '10',
    image_url: '',
    is_available: true,
    sort_order: '0',
});

const openCreateModal = () => {
    editingItem.value = null;
    form.reset();
    form.tax_inclusive = true;
    form.is_available = true;
    form.prep_time_min = '10';
    form.sort_order = '0';
    imageFile.value = null;
    imagePreview.value = null;
    showItemModal.value = true;
};

const openEditModal = (item: MenuItem) => {
    editingItem.value = item;
    form.clearErrors();
    form.category_id = item.category_id.toString();
    form.name = item.name;
    form.description = item.description || '';
    form.base_price = item.base_price.toString();
    form.tax_inclusive = item.tax_inclusive;
    form.prep_time_min = item.prep_time_min.toString();
    form.image_url = item.image_url || '';
    form.is_available = item.is_available;
    form.sort_order = item.sort_order.toString();
    imageFile.value = null;
    imagePreview.value = item.image_url;
    showItemModal.value = true;
};

const handleImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        imageFile.value = target.files[0];
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(imageFile.value);
    }
};

const submit = () => {
    const formData = new FormData();
    formData.append('category_id', form.category_id);
    formData.append('name', form.name);
    formData.append('description', form.description);
    formData.append('base_price', form.base_price);
    formData.append('tax_inclusive', form.tax_inclusive ? '1' : '0');
    formData.append('prep_time_min', form.prep_time_min.toString());
    formData.append('is_available', form.is_available ? '1' : '0');
    formData.append('sort_order', form.sort_order.toString());

    if (imageFile.value) {
        formData.append('image', imageFile.value);
    } else if (editingItem.value?.image_url) {
        formData.append('image_url', editingItem.value.image_url);
    }

    if (editingItem.value) {
        formData.append('_method', 'PATCH');
        router.post(
            route('menu-items.update', editingItem.value.id),
            formData,
            {
                onSuccess: () => closeModal(),
            }
        );
    } else {
        router.post(route('menu-items.store'), formData, {
            onSuccess: () => closeModal(),
        });
    }
};

const closeModal = () => {
    showItemModal.value = false;
    form.reset();
    imageFile.value = null;
    imagePreview.value = null;
};

const confirmItemDeletion = (id: number) => {
    itemToDelete.value = id;
    confirmingItemDeletion.value = true;
};

const deleteItem = () => {
    if (itemToDelete.value) {
        form.delete(route('menu-items.destroy', itemToDelete.value), {
            onSuccess: () => (confirmingItemDeletion.value = false),
        });
    }
};

const formatPrice = (price: string | number) => {
    return Number(price).toFixed(2);
};
</script>

<template>
    <Head title="Menu Items" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Menu Items
                </h2>
                <PrimaryButton @click="openCreateModal">
                    Add Menu Item
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
                                        Category
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Description
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Price
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Prep Time
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
                                <tr v-for="item in items" :key="item.id">
                                    <td class="whitespace-nowrap px-6 py-4">
                                        {{ item.name }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        {{ item.category?.name || '-' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ item.description || '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        ${{ formatPrice(item.base_price) }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        {{ item.prep_time_min }} min
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span
                                            :class="
                                                item.is_available
                                                    ? 'bg-green-100 text-green-800'
                                                    : 'bg-red-100 text-red-800'
                                            "
                                            class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                        >
                                            {{
                                                item.is_available ? 'Available' : 'Unavailable'
                                            }}
                                        </span>
                                    </td>
                                    <td
                                        class="space-x-2 whitespace-nowrap px-6 py-4 text-sm font-medium"
                                    >
                                        <button
                                            @click="openEditModal(item)"
                                            class="text-indigo-600 hover:text-indigo-900"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            @click="confirmItemDeletion(item.id)"
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

        <!-- Item Modal -->
        <Modal :show="showItemModal" @close="closeModal" :maxWidth="'xl'">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ editingItem ? 'Edit Menu Item' : 'Add New Menu Item' }}
                </h2>

                <form @submit.prevent="submit" class="mt-6 space-y-4">
                    <div>
                        <InputLabel for="category_id" value="Category" />
                        <select
                            id="category_id"
                            v-model="form.category_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        >
                            <option value="" disabled>Select a category</option>
                            <option
                                v-for="category in categories"
                                :key="category.id"
                                :value="category.id.toString()"
                            >
                                {{ category.name }}
                            </option>
                        </select>
                        <InputError
                            :message="form.errors.category_id"
                            class="mt-2"
                        />
                    </div>

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

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="base_price" value="Base Price" />
                            <TextInput
                                id="base_price"
                                v-model="form.base_price"
                                type="number"
                                step="0.01"
                                min="0"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.base_price" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="prep_time_min" value="Prep Time (min)" />
                            <TextInput
                                id="prep_time_min"
                                v-model="form.prep_time_min"
                                type="number"
                                min="1"
                                max="255"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.prep_time_min" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="image" value="Image (Optional)" />
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
                    </div>

                    <div class="flex items-center space-x-4">
                        <div class="flex items-center">
                            <input
                                type="checkbox"
                                id="tax_inclusive"
                                v-model="form.tax_inclusive"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            />
                            <label
                                for="tax_inclusive"
                                class="ml-2 block text-sm text-gray-900"
                                >Tax Inclusive</label
                            >
                        </div>

                        <div class="flex items-center">
                            <input
                                type="checkbox"
                                id="is_available"
                                v-model="form.is_available"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            />
                            <label
                                for="is_available"
                                class="ml-2 block text-sm text-gray-900"
                                >Available</label
                            >
                        </div>
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

                    <div class="mt-6 flex justify-end">
                        <SecondaryButton @click="closeModal">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton
                            class="ms-3"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            {{ editingItem ? 'Update Item' : 'Create Item' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal
            :show="confirmingItemDeletion"
            @close="confirmingItemDeletion = false"
        >
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Are you sure you want to delete this menu item?
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    This action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="confirmingItemDeletion = false">
                        Cancel
                    </SecondaryButton>
                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteItem"
                    >
                        Delete Item
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>