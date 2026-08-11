<script setup lang="ts">
/**
 * SideBar.vue
 * ------------------------------------------------------------------
 * Data-driven sidebar. Pass `items` in, it renders the nav from that
 * array. Collapse/expand is controlled by the parent (v-model:collapsed),
 * and the component also exposes a `toggle()` method so a parent can
 * flip it imperatively via a template ref instead, if that fits better.
 *
 * Usage (controlled via v-model):
 *   <SideBar v-model:collapsed="sidebarCollapsed" :items="navItems" :user="authUser" />
 *
 * Usage (controlled via exposed method):
 *   <SideBar ref="sidebarRef" :items="navItems" />
 *   <button @click="sidebarRef?.toggle()">Menu</button>
 * ------------------------------------------------------------------
 */
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import {
    ArrowRightOnRectangleIcon,
    ChartBarIcon,
    Cog6ToothIcon,
    HomeIcon,
    InboxIcon,
    ShoppingCartIcon,
    UserIcon,
} from '@heroicons/vue/24/outline';
import { Link } from '@inertiajs/vue3';
import { computed, type Component } from 'vue';

export type SidebarIcon =
    | 'home'
    | 'profile'
    | 'inbox'
    | 'analytics'
    | 'order'
    | 'settings'
    | 'logout';

export type BadgeColor = 'amber' | 'red' | 'green' | 'violet' | 'blue' | 'rose';

export interface SidebarItem {
    key: string;
    label: string;
    icon: SidebarIcon;
    href?: string;
    /** Marks this item as the current/active route. If omitted, the
     *  component falls back to matching `href` against window.location. */
    active?: boolean;
    badgeColor?: BadgeColor;
}

interface Props {
    items: SidebarItem[];
    collapsed?: boolean;
    user?: { name: string; avatarUrl?: string } | null;
    logoutHref?: string;
    /** Page background behind the sidebar, used to color the notch cutouts.
     *  Override if your layout's background isn't the default. */
    pageBg?: string;
}

const props = withDefaults(defineProps<Props>(), {
    collapsed: false,
    user: null,
    logoutHref: '/logout',
    pageBg: '#14161a',
});

const emit = defineEmits<{
    (e: 'update:collapsed', value: boolean): void;
}>();

function toggle() {
    emit('update:collapsed', !props.collapsed);
}

// Exposed so a parent can do `sidebarRef.value.toggle()` as an alternative
// to v-model, e.g. from a navbar hamburger button that lives elsewhere.
defineExpose({ toggle });

function isActive(item: SidebarItem): boolean {
    if (typeof item.active === 'boolean') return item.active;
    if (typeof window === 'undefined' || !item.href) return false;
    return window.location.pathname === item.href;
}

const badgeClasses: Record<BadgeColor, string> = {
    amber: 'bg-amber-400 shadow-amber-400/30',
    red: 'bg-red-500 shadow-red-500/30',
    green: 'bg-green-500 shadow-green-500/30',
    violet: 'bg-violet-500 shadow-violet-500/30',
    blue: 'bg-blue-500 shadow-blue-500/30',
    rose: 'bg-rose-500 shadow-rose-500/30',
};

function badgeClass(item: SidebarItem): string {
    return badgeClasses[item.badgeColor ?? 'amber'];
}

const ICONS: Record<SidebarIcon, Component> = {
    home: HomeIcon,
    profile: UserIcon,
    inbox: InboxIcon,
    analytics: ChartBarIcon,
    order: ShoppingCartIcon,
    settings: Cog6ToothIcon,
    logout: ArrowRightOnRectangleIcon,
};

const asideWidth = computed(() => (props.collapsed ? 'w-20' : 'w-72'));
</script>

<template>
    <aside
        :style="{ '--page-bg': pageBg }"
        class="relative flex flex-col overflow-visible rounded-[28px] bg-white py-8 shadow-2xl transition-[width] duration-200 ease-out"
        :class="asideWidth"
    >
        <!-- Logo -->
        <div
            class="flex items-center gap-3 px-8 pb-8"
            :class="{ 'justify-center px-0': collapsed }"
        >
            <ApplicationLogo class="h-6 w-6 shrink-0 fill-neutral-900" />
            <span
                v-if="!collapsed"
                class="whitespace-nowrap text-sm font-bold tracking-wide text-neutral-900"
            >
                WEBSITE LOGO
            </span>
        </div>

        <!-- Nav -->
        <nav class="flex flex-col">
            <div v-for="item in items" :key="item.key" class="relative">
                <!-- Active item: page-colored cut row + notch + floating badge -->
                <component
                    :is="item.href ? Link : 'div'"
                    v-if="isActive(item)"
                    :href="item.href"
                    class="notch row-active relative flex items-center gap-4 py-4"
                    :class="collapsed ? 'w-full justify-center' : 'pl-8 pr-8'"
                >
                    <span v-if="!collapsed" class="h-5 w-5 shrink-0" />
                    <span
                        v-if="!collapsed"
                        class="whitespace-nowrap text-sm font-semibold tracking-wide"
                        :class="`text-${item.badgeColor ?? 'amber'}-400`"
                    >
                        {{ item.label.toUpperCase() }}
                    </span>

                    <span
                        class="absolute top-1/2 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full shadow-lg"
                        :class="[
                            badgeClass(item),
                            collapsed
                                ? 'right-1/2 translate-x-1/2'
                                : '-right-6',
                        ]"
                    >
                        <component
                            :is="ICONS[item.icon]"
                            class="h-5 w-5 text-white"
                            aria-hidden="true"
                        />
                    </span>
                </component>

                <!-- Inactive item -->
                <component
                    :is="item.href ? Link : 'a'"
                    v-else
                    :href="item.href"
                    class="hover-row flex items-center gap-4 py-4 text-neutral-800"
                    :class="collapsed ? 'w-full justify-center' : 'px-8'"
                    :title="collapsed ? item.label : undefined"
                >
                    <component
                        :is="ICONS[item.icon]"
                        class="h-5 w-5 shrink-0 text-current"
                        aria-hidden="true"
                    />
                    <span
                        v-if="!collapsed"
                        class="whitespace-nowrap text-sm font-semibold tracking-wide"
                    >
                        {{ item.label.toUpperCase() }}
                    </span>
                </component>
            </div>
        </nav>

        <div class="flex-1" />

        <!-- Footer -->
        <div
            class="border-t border-neutral-100 px-8 pt-6"
            :class="{ 'px-0': collapsed }"
        >
            <div
                v-if="user"
                class="flex items-center gap-3 pb-4"
                :class="{ 'justify-center px-0': collapsed }"
            >
                <img
                    v-if="user.avatarUrl"
                    :src="user.avatarUrl"
                    class="h-9 w-9 shrink-0 rounded-full object-cover"
                    :alt="user.name"
                />
                <div
                    v-else
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-neutral-200 text-xs font-semibold text-neutral-600"
                >
                    {{ user.name.slice(0, 1) }}
                </div>
                <span
                    v-if="!collapsed"
                    class="whitespace-nowrap text-sm font-semibold tracking-wide text-neutral-800"
                >
                    {{ user.name.toUpperCase() }}
                </span>
            </div>

            <Link
                :href="logoutHref"
                method="post"
                as="button"
                class="hover-row -ml-8 flex items-center gap-4 pb-2 pl-8 pt-3 text-neutral-500"
                :class="collapsed ? 'ml-0 w-full justify-center pl-0' : ''"
            >
                <ArrowRightOnRectangleIcon
                    class="h-5 w-5 shrink-0 text-current"
                    aria-hidden="true"
                />
                <span
                    v-if="!collapsed"
                    class="text-sm font-semibold tracking-wide"
                    >LOGOUT</span
                >
            </Link>
        </div>

        <!-- Optional built-in toggle handle; remove if the parent drives
         collapse from its own button instead. -->
        <button
            type="button"
            class="absolute -right-3 top-10 flex h-7 w-7 items-center justify-center rounded-full border border-neutral-200 bg-white shadow-md"
            :aria-label="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
            @click="toggle"
        >
            <svg
                viewBox="0 0 24 24"
                class="h-3.5 w-3.5 fill-none stroke-neutral-600 transition-transform"
                :class="{ 'rotate-180': collapsed }"
                stroke-width="2"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M15 6l-6 6 6 6"
                />
            </svg>
        </button>
    </aside>
</template>

<style scoped>
/* Concave "notch" corners: two circles drawn via box-shadow, colored to
   match the page background, so the card's straight edge curves smoothly
   into the round badge instead of meeting it at a sharp corner. */
.notch {
    position: relative;
}
.notch::before,
.notch::after {
    content: '';
    position: absolute;
    right: 0;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: transparent;
}
.notch::before {
    top: -22px;
    box-shadow: 14px 14px 0 6px var(--page-bg);
}
.notch::after {
    bottom: -22px;
    box-shadow: 14px -14px 0 6px var(--page-bg);
}

/* Active row fill: literally the page color, so it reads as a hole cut
   through the white card rather than a mismatched solid block. */
.row-active {
    background: var(--page-bg);
}

/* Hover preview on inactive rows: same page-colored fill on :hover */
.hover-row {
    position: relative;
    z-index: 0;
}
.hover-row::before {
    content: '';
    position: absolute;
    inset: 0;
    background: var(--page-bg);
    opacity: 0;
    transition: opacity 0.15s ease;
    z-index: -1;
    border-radius: 9999px 0 0 9999px;
}
.hover-row:hover {
    color: #fff;
}
.hover-row:hover::before {
    opacity: 1;
}
</style>
