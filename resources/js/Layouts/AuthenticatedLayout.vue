<script setup lang="ts">
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import Sidebar from '@/Components/Sidebar.vue';
import { usePolling } from '@/Composables/usePolling';
import { ref } from 'vue';

const sidebarRef = ref<InstanceType<typeof Sidebar> | null>(null);

const { unreadCount, notifications, markRead } = usePolling(8000);

const toggleSidebar = () => {
    sidebarRef.value?.toggle();
};
</script>

<template>
    <div>
        <Sidebar ref="sidebarRef" />

        <div class="flex flex-col md:pl-64">
            <div class="min-h-screen bg-gray-100">
                <!-- Top Navigation Bar -->
                <nav class="border-b border-gray-100 bg-white">
                    <!-- Primary Navigation Menu -->
                    <div class="mx-4 px-4 sm:px-6 lg:px-8">
                        <div class="flex h-16 justify-between">
                            <div class="flex">
                                <!-- Mobile menu button (triggers sidebar) -->
                                <div class="-me-2 flex items-center md:hidden">
                                    <button
                                        @click="toggleSidebar"
                                        class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none"
                                    >
                                        <svg
                                            class="h-6 w-6"
                                            stroke="currentColor"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                class="inline-flex"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M4 6h16M4 12h16M4 18h16"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="hidden sm:ms-6 sm:flex sm:items-center space-x-3">
                                <!-- Notification Dropdown -->
                                <div class="relative">
                                    <Dropdown align="right" width="80">
                                        <template #trigger>
                                            <button
                                                type="button"
                                                class="relative p-2 text-gray-500 hover:text-gray-700 transition rounded-full hover:bg-gray-100"
                                            >
                                                🔔
                                                <span
                                                    v-if="unreadCount > 0"
                                                    class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-[10px] font-bold text-white shadow"
                                                >
                                                    {{ unreadCount > 9 ? '9+' : unreadCount }}
                                                </span>
                                            </button>
                                        </template>

                                        <template #content>
                                            <div class="p-3 border-b border-gray-100 font-semibold text-xs text-gray-700 flex justify-between items-center">
                                                <span>Notifications</span>
                                                <span class="text-gray-400 font-normal">{{ notifications.length }} new</span>
                                            </div>
                                            <div class="max-h-64 overflow-y-auto divide-y divide-gray-100">
                                                <div
                                                    v-for="item in notifications"
                                                    :key="item.id"
                                                    class="p-3 text-xs hover:bg-gray-50 flex justify-between items-start"
                                                >
                                                    <div>
                                                        <div class="font-bold text-gray-900 capitalize">{{ item.event_type.replace('_', ' ') }}</div>
                                                        <div class="text-gray-500 text-[11px] mt-0.5" v-if="item.payload">
                                                            {{ item.payload.table_number ? `Table ${item.payload.table_number}` : '' }}
                                                            {{ item.payload.order_id ? `Order #${item.payload.order_id}` : '' }}
                                                        </div>
                                                        <div class="text-[10px] text-gray-400 mt-1">{{ item.time_ago }}</div>
                                                    </div>
                                                    <button
                                                        @click="markRead(item.id)"
                                                        class="text-[10px] text-blue-600 hover:underline"
                                                    >
                                                        Dismiss
                                                    </button>
                                                </div>
                                                <div v-if="notifications.length === 0" class="p-4 text-center text-xs text-gray-400">
                                                    No new notifications
                                                </div>
                                            </div>
                                        </template>
                                    </Dropdown>
                                </div>

                                <!-- Settings Dropdown -->
                                <div class="relative ms-3">
                                    <Dropdown align="right" width="48">
                                        <template #trigger>
                                            <span
                                                class="inline-flex rounded-md"
                                            >
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                                                >
                                                    {{
                                                        $page.props.auth.user
                                                            .name
                                                    }}

                                                    <svg
                                                        class="-me-0.5 ms-2 h-4 w-4"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        viewBox="0 0 20 20"
                                                        fill="currentColor"
                                                    >
                                                        <path
                                                            fill-rule="evenodd"
                                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                            clip-rule="evenodd"
                                                        />
                                                    </svg>
                                                </button>
                                            </span>
                                        </template>

                                        <template #content>
                                            <DropdownLink
                                                :href="route('profile.edit')"
                                            >
                                                Profile
                                            </DropdownLink>
                                            <DropdownLink
                                                :href="route('logout')"
                                                method="post"
                                                as="button"
                                            >
                                                Log Out
                                            </DropdownLink>
                                        </template>
                                    </Dropdown>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Page Heading -->
                <header class="bg-white shadow" v-if="$slots.header">
                    <div class="mx-4 px-4 py-6 sm:px-6 lg:px-8">
                        <slot name="header" />
                    </div>
                </header>

                <!-- Page Content -->
                <main>
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>
