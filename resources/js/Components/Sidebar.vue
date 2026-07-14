<script setup lang="ts">
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const isCollapsed = ref(false);
const isOpen = ref(false);

const toggle = () => {
    isCollapsed.value = !isCollapsed.value;
};

const close = () => {
    isOpen.value = false;
};

defineExpose({
    toggle,
    close,
    isOpen,
    isCollapsed,
});

const appName = ref(usePage().props.appName || 'Laravel');
</script>

<template>
    <!-- Desktop Sidebar (md and up) -->
    <div
        class="hidden md:fixed md:inset-y-0 md:flex md:flex-col"
        :class="isCollapsed ? 'md:w-16' : 'md:w-64'"
    >
        <div class="flex min-h-0 flex-1 flex-col bg-gray-800">
            <div class="flex flex-1 flex-col overflow-y-auto pb-4 pt-5">
                <div
                    class="flex flex-shrink-0 items-center px-4 text-gray-200"
                    :class="isCollapsed ? 'justify-center' : 'justify-between'"
                >
                    <Link
                        v-if="!isCollapsed"
                        :href="route('dashboard')"
                        class="flex items-center gap-2"
                    >
                        <svg
                            class="h-8 w-auto fill-current text-gray-100"
                            viewBox="0 0 24 24"
                            fill="currentColor"
                        >
                            <path
                                d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"
                                stroke="currentColor"
                                stroke-width="2"
                                fill="none"
                            />
                        </svg>
                        <div v-text="appName"></div>
                    </Link>

                    <!-- Collapse/Expand toggle (hamburger) -->
                    <button
                        type="button"
                        @click="toggle"
                        class="inline-flex items-center justify-center rounded-md p-2 text-gray-300 transition duration-150 ease-in-out hover:bg-gray-700 hover:text-white focus:bg-gray-700 focus:text-white focus:outline-none"
                        :aria-label="isCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                        :aria-expanded="!isCollapsed"
                    >
                        <svg
                            class="h-6 w-6"
                            stroke="currentColor"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                        </svg>
                    </button>
                </div>
                <nav
                    class="mt-5 flex flex-1 flex-col gap-1 pl-3"
                    :class="isCollapsed ? 'items-center pl-0' : ''"
                >
                    <NavLink
                        :href="route('dashboard')"
                        :active="route().current('dashboard')"
                        :title="isCollapsed ? 'Dashboard' : undefined"
                        :class="isCollapsed ? 'w-10 justify-center !pr-0 !pl-0' : ''"
                    >
                        <span v-if="!isCollapsed">Dashboard</span>
                        <span v-else class="text-lg font-bold">D</span>
                    </NavLink>
                    <NavLink
                        v-if="$page.props.auth.user.role === 'admin'"
                        :href="route('users')"
                        :active="route().current('users*')"
                        :title="isCollapsed ? 'Users' : undefined"
                        :class="isCollapsed ? 'w-10 justify-center !pr-0 !pl-0' : ''"
                    >
                        <span v-if="!isCollapsed">Users</span>
                        <span v-else class="text-lg font-bold">U</span>
                    </NavLink>
                    <NavLink
                        v-if="$page.props.auth.user.role === 'admin'"
                        :href="route('menu-categories')"
                        :active="route().current('menu-categories*')"
                        :title="isCollapsed ? 'Menu Categories' : undefined"
                        :class="isCollapsed ? 'w-10 justify-center !pr-0 !pl-0' : ''"
                    >
                        <span v-if="!isCollapsed">Menu Categories</span>
                        <span v-else class="text-lg font-bold">C</span>
                    </NavLink>
                    <NavLink
                        v-if="$page.props.auth.user.role === 'admin'"
                        :href="route('menu-items')"
                        :active="route().current('menu-items*')"
                        :title="isCollapsed ? 'Menu Items' : undefined"
                        :class="isCollapsed ? 'w-10 justify-center !pr-0 !pl-0' : ''"
                    >
                        <span v-if="!isCollapsed">Menu Items</span>
                        <span v-else class="text-lg font-bold">M</span>
                    </NavLink>
                    <NavLink
                        v-if="$page.props.auth.user.role === 'admin'"
                        :href="route('tables')"
                        :active="route().current('tables*')"
                        :title="isCollapsed ? 'Tables' : undefined"
                        :class="isCollapsed ? 'w-10 justify-center !pr-0 !pl-0' : ''"
                    >
                        <span v-if="!isCollapsed">Tables</span>
                        <span v-else class="text-lg font-bold">T</span>
                    </NavLink>
                    <!-- Add more navigation links here -->
                </nav>
            </div>
        </div>
    </div>

    <!-- Mobile Overlay -->
    <div
        v-if="isOpen"
        class="fixed inset-0 z-40 flex md:hidden"
        role="dialog"
        aria-modal="true"
    >
        <!-- Backdrop -->
        <div
            class="fixed inset-0 bg-gray-600 bg-opacity-75 transition-opacity"
            @click="close"
        ></div>

        <!-- Sidebar panel -->
        <div
            class="relative flex w-full max-w-xs flex-1 flex-col bg-white pb-4 pt-5"
        >
            <!-- Close button -->
            <div class="absolute right-0 top-0 -mr-12 pt-2">
                <button
                    @click="close"
                    class="ml-1 flex h-10 w-10 items-center justify-center rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
                >
                    <span class="sr-only">Close sidebar</span>
                    <svg
                        class="h-6 w-6 text-white"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            <div class="flex flex-shrink-0 items-center px-4">
                <Link :href="route('dashboard')" @click="close">
                    <svg
                        class="h-8 w-auto fill-current text-red-800"
                        viewBox="0 0 24 24"
                        fill="currentColor"
                    >
                        <path
                            d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"
                            stroke="currentColor"
                            stroke-width="2"
                            fill="none"
                        />
                    </svg>
                </Link>
            </div>

            <div class="mt-5 h-0 flex-1 overflow-y-auto">
                <nav class="space-y-1 px-2">
                    <ResponsiveNavLink
                        :href="route('dashboard')"
                        :active="route().current('dashboard')"
                        @click="close"
                    >
                        Dashboard
                    </ResponsiveNavLink>
                    <ResponsiveNavLink
                        v-if="$page.props.auth.user.role === 'admin'"
                        :href="route('users')"
                        :active="route().current('users*')"
                        @click="close"
                    >
                        Users
                    </ResponsiveNavLink>
                    <ResponsiveNavLink
                        v-if="$page.props.auth.user.role === 'admin'"
                        :href="route('menu-categories')"
                        :active="route().current('menu-categories*')"
                        @click="close"
                    >
                        Menu Ctegories
                    </ResponsiveNavLink>
<ResponsiveNavLink
                            v-if="$page.props.auth.user.role === 'admin'"
                            :href="route('menu-items')"
                            :active="route().current('menu-items*')"
                            @click="close"
                        >
                            Menu Items
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="$page.props.auth.user.role === 'admin'"
                            :href="route('tables')"
                            :active="route().current('tables*')"
                            @click="close"
                        >
                            Tables
                        </ResponsiveNavLink>
                        <!-- Add more navigation links here -->
                </nav>
            </div>
        </div>

        <div class="w-14 flex-shrink-0"></div>
    </div>
</template>
