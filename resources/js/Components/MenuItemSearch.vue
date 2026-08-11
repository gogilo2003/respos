<script setup lang="ts">
import { formatCurrency } from '@/utils/currency';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

interface MenuItem {
    id: number;
    name: string;
    price: number;
    category_id?: number;
    category?: { id: number; name: string };
}

interface Category {
    id: number;
    name: string;
}

interface Props {
    menuItems: MenuItem[];
    categories?: Category[];
    modelValue?: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:modelValue': [value: string];
    select: [item: MenuItem];
}>();

const query = ref(props.modelValue ?? '');
const activeCategoryId = ref<number | ''>('');
const selectedIndex = ref(0);

watch(
    () => props.modelValue,
    (value) => {
        query.value = value ?? '';
    },
);

watch(
    () => [query.value, activeCategoryId.value] as const,
    () => {
        selectedIndex.value = 0;
    },
);

const filteredMenuItems = computed(() => {
    const term = query.value.trim().toLowerCase();
    let items = props.menuItems;

    if (activeCategoryId.value !== '') {
        items = items.filter(
            (item) =>
                (item.category_id ?? item.category?.id) ===
                activeCategoryId.value,
        );
    }

    if (!term) {
        return items;
    }

    return items.filter((item) => item.name.toLowerCase().includes(term));
});

const visibleItems = computed(() => filteredMenuItems.value.slice(0, 8));

const onInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const value = target.value;
    query.value = value;
    emit('update:modelValue', value);
};

const selectItem = (item: MenuItem) => {
    emit('select', item);
    query.value = '';
    activeCategoryId.value = '';
};

const setCategory = (categoryId: number | '') => {
    activeCategoryId.value = categoryId;
};

const onKeyDown = (event: KeyboardEvent) => {
    if (event.key === 'ArrowDown') {
        event.preventDefault();
        selectedIndex.value = Math.min(
            selectedIndex.value + 1,
            Math.max(visibleItems.value.length - 1, 0),
        );
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        selectedIndex.value = Math.max(selectedIndex.value - 1, 0);
    } else if (event.key === 'Enter') {
        event.preventDefault();
        const item = visibleItems.value[selectedIndex.value];
        if (item) {
            selectItem(item);
        }
    } else if (event.key === 'Escape') {
        query.value = '';
        activeCategoryId.value = '';
    }
};

onMounted(() => {
    window.addEventListener('keydown', onKeyDown);
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', onKeyDown);
});
</script>

<template>
    <div class="space-y-3">
        <div>
            <input
                :value="query"
                type="text"
                class="block w-full rounded-md border border-gray-300 px-4 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                placeholder="Search menu items..."
                @input="onInput"
            />
        </div>

        <div
            v-if="categories && categories.length > 0"
            class="flex flex-wrap gap-2"
        >
            <button
                type="button"
                class="rounded-full border px-3 py-1.5 text-sm font-medium transition"
                :class="
                    activeCategoryId === ''
                        ? 'border-indigo-500 bg-indigo-50 text-indigo-700'
                        : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'
                "
                @click="setCategory('')"
            >
                All
            </button>
            <button
                v-for="category in categories"
                :key="category.id"
                type="button"
                class="rounded-full border px-3 py-1.5 text-sm font-medium transition"
                :class="
                    activeCategoryId === category.id
                        ? 'border-indigo-500 bg-indigo-50 text-indigo-700'
                        : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'
                "
                @click="setCategory(category.id)"
            >
                {{ category.name }}
            </button>
        </div>

        <div class="max-h-64 overflow-y-auto rounded-md border border-gray-200">
            <button
                v-for="(item, index) in visibleItems"
                :key="item.id"
                type="button"
                class="flex w-full items-center justify-between px-3 py-2 text-sm text-gray-700 transition"
                :class="
                    selectedIndex === index
                        ? 'bg-indigo-50'
                        : 'hover:bg-gray-50'
                "
                @click="selectItem(item)"
                @mouseenter="selectedIndex = index"
            >
                <span>{{ item.name }}</span>
                <span class="text-gray-500">
                    {{ formatCurrency(item.price) }}
                </span>
            </button>
            <div
                v-if="visibleItems.length === 0"
                class="px-3 py-4 text-sm text-gray-500"
            >
                No menu items found.
            </div>
        </div>
    </div>
</template>
