<script setup lang="ts">
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import type { MenuCategory, MenuItem, ModifierGroup } from '@/interfaces/menu';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    items: MenuItem[];
    categories: MenuCategory[];
}>();

const searchQuery = ref('');
const selectedCategoryId = ref<string>('');
const confirmingItemDeletion = ref(false);
const itemToDelete = ref<number | null>(null);
const editingItem = ref<MenuItem | null>(null);
const showItemModal = ref(false);
const imageFile = ref<File | null>(null);
const imagePreview = ref<string | null>(null);
const modifierGroups = ref<ModifierGroup[]>([]);

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

const filteredItems = computed(() => {
    let result = props.items;

    if (selectedCategoryId.value) {
        const catId = Number(selectedCategoryId.value);
        result = result.filter((item) => item.category_id === catId);
    }

    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase();
        result = result.filter(
            (item) =>
                item.name.toLowerCase().includes(q) ||
                (item.description && item.description.toLowerCase().includes(q)),
        );
    }

    return result;
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
    modifierGroups.value = [];
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
    modifierGroups.value = item.modifier_groups ? JSON.parse(JSON.stringify(item.modifier_groups)) : [];
    showItemModal.value = true;
};

const addModifierGroup = () => {
    modifierGroups.value.push({
        name: '',
        required: false,
        options: [{ name: '', price: 0 }],
    });
};

const removeModifierGroup = (groupIndex: number) => {
    modifierGroups.value.splice(groupIndex, 1);
};

const addModifierOption = (groupIndex: number) => {
    modifierGroups.value[groupIndex].options.push({ name: '', price: 0 });
};

const removeModifierOption = (groupIndex: number, optionIndex: number) => {
    modifierGroups.value[groupIndex].options.splice(optionIndex, 1);
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

    if (modifierGroups.value.length > 0) {
        modifierGroups.value.forEach((group, gIdx) => {
            formData.append(`modifier_groups[${gIdx}][name]`, group.name);
            formData.append(`modifier_groups[${gIdx}][required]`, group.required ? '1' : '0');
            group.options.forEach((opt, oIdx) => {
                formData.append(`modifier_groups[${gIdx}][options][${oIdx}][name]`, opt.name);
                formData.append(`modifier_groups[${gIdx}][options][${oIdx}][price]`, opt.price.toString());
            });
        });
    }

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
            },
        );
    } else {
        router.post(route('menu-items.store'), formData, {
            onSuccess: () => closeModal(),
        });
    }
};

const toggleAvailability = (item: MenuItem) => {
    router.patch(
        route('menu-items.toggle-availability', item.id),
        {},
        {
            preserveScroll: true,
        },
    );
};

const closeModal = () => {
    showItemModal.value = false;
    form.reset();
    imageFile.value = null;
    imagePreview.value = null;
    modifierGroups.value = [];
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

import { formatCurrency } from '@/utils/currency';

const formatPrice = (price: string | number) => {
    return formatCurrency(price);
};
</script>

<template>
    <Head title="Menu Items" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Menu Items Management
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Manage food & drink menu offerings, pricing, preparation time, and options
                    </p>
                </div>
                <PrimaryButton @click="openCreateModal">
                    + Add Menu Item
                </PrimaryButton>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Filters Bar -->
                <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex flex-col sm:flex-row items-center gap-3 w-full max-w-lg">
                        <div class="relative w-full sm:w-64">
                            <TextInput
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search menu items..."
                                class="w-full pl-9 text-sm"
                            />
                            <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
                        </div>

                        <select
                            v-model="selectedCategoryId"
                            class="w-full sm:w-48 rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">All Categories</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id.toString()">
                                {{ cat.name }}
                            </option>
                        </select>
                    </div>

                    <div class="text-xs text-gray-500">
                        Showing <span class="font-bold text-gray-800">{{ filteredItems.length }}</span> items
                    </div>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg border border-gray-200">
                    <div class="overflow-x-auto p-6 text-gray-900">
                        <div v-if="filteredItems.length === 0" class="py-12 text-center text-sm text-gray-500">
                            No menu items found.
                        </div>

                        <table v-else class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Item
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Category
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Price
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Prep Time
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Availability
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="item in filteredItems" :key="item.id">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 flex-shrink-0 overflow-hidden rounded-lg bg-gray-100 border border-gray-200">
                                                <img
                                                    v-if="item.image_url"
                                                    :src="item.image_url"
                                                    :alt="item.name"
                                                    class="h-full w-full object-cover"
                                                />
                                                <div v-else class="flex h-full w-full items-center justify-center text-gray-400 text-xs">
                                                    🍲
                                                </div>
                                            </div>
                                            <div>
                                                <div class="font-semibold text-gray-900">{{ item.name }}</div>
                                                <div class="text-xs text-gray-500 max-w-xs truncate">
                                                    {{ item.description || 'No description' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                        <span class="rounded bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-800">
                                            {{ item.category?.name || 'Uncategorized' }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-bold text-gray-900">
                                        {{ formatPrice(item.base_price) }}
                                        <span v-if="item.tax_inclusive" class="text-[10px] font-normal text-gray-500 ml-1">(inc. tax)</span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                        ⏱️ {{ item.prep_time_min }} min
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <button
                                            @click="toggleAvailability(item)"
                                            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold transition"
                                            :class="
                                                item.is_available
                                                    ? 'bg-green-100 text-green-800 hover:bg-green-200'
                                                    : 'bg-red-100 text-red-800 hover:bg-red-200'
                                            "
                                        >
                                            <span class="h-1.5 w-1.5 rounded-full" :class="item.is_available ? 'bg-green-600' : 'bg-red-600'"></span>
                                            {{ item.is_available ? 'Available' : 'Out of Stock' }}
                                        </button>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                        <button
                                            @click="openEditModal(item)"
                                            class="text-indigo-600 hover:text-indigo-900 mr-4"
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

        <!-- Item Form Modal -->
        <Modal :show="showItemModal" @close="closeModal" :maxWidth="'2xl'">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ editingItem ? 'Edit Menu Item' : 'Add New Menu Item' }}
                </h2>

                <form @submit.prevent="submit" class="mt-6 space-y-4 max-h-[75vh] overflow-y-auto pr-1">
                    <div>
                        <InputLabel for="category_id" value="Category" />
                        <select
                            id="category_id"
                            v-model="form.category_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        >
                            <option value="" disabled>Select a category</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id.toString()">
                                {{ category.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.category_id" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="name" value="Item Name" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            required
                            maxlength="80"
                            placeholder="e.g. Cheeseburger, Iced Latte"
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
                            placeholder="Ingredients or details"
                        />
                        <InputError :message="form.errors.description" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                            <InputLabel for="prep_time_min" value="Prep Time (Minutes)" />
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
                        <InputLabel for="image" value="Item Image" />
                        <input
                            type="file"
                            id="image"
                            accept="image/*"
                            @change="handleImageChange"
                            class="mt-1 block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200"
                        />
                        <div v-if="imagePreview" class="mt-2">
                            <img :src="imagePreview" alt="Preview" class="h-24 w-24 rounded-lg object-cover border border-gray-200" />
                        </div>
                    </div>

                    <div class="flex items-center space-x-6 pt-2">
                        <label class="flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="form.tax_inclusive"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            />
                            <span class="ml-2 text-sm text-gray-900">Tax Inclusive</span>
                        </label>

                        <label class="flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="form.is_available"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            />
                            <span class="ml-2 text-sm text-gray-900">Available</span>
                        </label>
                    </div>

                    <!-- Modifier Groups Structured Editor -->
                    <div class="border-t border-gray-200 pt-4 mt-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-semibold text-gray-900">Modifier Options & Add-ons</h3>
                            <button
                                type="button"
                                @click="addModifierGroup"
                                class="text-xs font-semibold text-indigo-600 hover:underline"
                            >
                                + Add Option Group
                            </button>
                        </div>

                        <div v-if="modifierGroups.length === 0" class="text-xs text-gray-500 italic bg-gray-50 p-3 rounded">
                            No modifiers added (e.g. Size options, Extra toppings). Click above to add.
                        </div>

                        <div v-else class="space-y-4">
                            <div
                                v-for="(group, gIdx) in modifierGroups"
                                :key="gIdx"
                                class="rounded-lg border border-gray-200 bg-gray-50 p-3"
                            >
                                <div class="flex items-center gap-2 mb-2">
                                    <TextInput
                                        v-model="group.name"
                                        type="text"
                                        placeholder="Group Name (e.g. Choice of Drink, Extra Toppings)"
                                        class="w-full text-xs font-medium"
                                        required
                                    />
                                    <label class="flex items-center text-xs text-gray-700 whitespace-nowrap">
                                        <input type="checkbox" v-model="group.required" class="mr-1 rounded text-xs" />
                                        Required
                                    </label>
                                    <button
                                        type="button"
                                        @click="removeModifierGroup(gIdx)"
                                        class="text-xs text-red-600 hover:underline px-1"
                                    >
                                        Remove
                                    </button>
                                </div>

                                <div class="space-y-2 pl-2 border-l-2 border-indigo-200">
                                    <div
                                        v-for="(opt, oIdx) in group.options"
                                        :key="oIdx"
                                        class="flex items-center gap-2"
                                    >
                                        <TextInput
                                            v-model="opt.name"
                                            type="text"
                                            placeholder="Option (e.g. Large, Extra Cheese)"
                                            class="w-full text-xs"
                                            required
                                        />
                                        <TextInput
                                            v-model.number="opt.price"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            placeholder="+$ Price"
                                            class="w-28 text-xs"
                                            required
                                        />
                                        <button
                                            type="button"
                                            @click="removeModifierOption(gIdx, oIdx)"
                                            class="text-xs text-red-500 hover:text-red-700 px-1"
                                        >
                                            ✕
                                        </button>
                                    </div>
                                    <button
                                        type="button"
                                        @click="addModifierOption(gIdx)"
                                        class="text-[11px] text-indigo-600 hover:underline"
                                    >
                                        + Add Option
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end pt-4 border-t border-gray-100">
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
