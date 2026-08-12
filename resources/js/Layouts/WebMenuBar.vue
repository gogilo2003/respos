<script lang="ts" setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { useCartStore } from '@/Stores/cartStore';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

interface NavLinkItem {
    name: string;
    href: string;
    isCart?: boolean;
    isTrack?: boolean;
}

const cartStore = useCartStore();
const page = usePage();

const activeOrderId = computed(() => {
    const fromProps = (page.props as any).activeOrderId;
    return cartStore.activeOrderId || fromProps || null;
});

const links = computed<NavLinkItem[]>(() => {
    const list: NavLinkItem[] = [
        { name: 'Home', href: '/' },
        { name: 'Categories', href: '/categories' },
        { name: 'Menu', href: '/menu' },
        { name: 'About', href: '/about' },
    ];

    if (activeOrderId.value) {
        list.push({
            name: 'Track Order',
            href: `/orders/${activeOrderId.value}/track`,
            isTrack: true,
        });
    }

    list.push({ name: 'Cart', href: '/cart', isCart: true });

    return list;
});

const SCROLL_THRESHOLD = 64;

const isScrolled = ref(false);
const mobileOpen = ref(false);

function handleScroll() {
    isScrolled.value = window.scrollY > SCROLL_THRESHOLD;
}

function toggleMobile() {
    mobileOpen.value = !mobileOpen.value;
}

onMounted(() => {
    handleScroll();
    window.addEventListener('scroll', handleScroll, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <header class="fixed inset-x-0 top-0 z-50 flex justify-center px-4">
        <div
            class="flex w-full max-w-7xl items-center justify-between rounded-full px-8 py-3 transition-all duration-300"
            :class="
                isScrolled
                    ? 'bg-gray-800/90 shadow-lg shadow-black/20 ring-1 ring-white/10 backdrop-blur-md'
                    : 'bg-transparent'
            "
        >
            <Link href="/" class="text-base font-bold tracking-wide text-white">
                <ApplicationLogo class="h-10 w-auto" />
            </Link>

            <!-- Desktop links -->
            <nav class="hidden items-center gap-6 md:flex">
                <Link
                    v-for="link in links"
                    :key="link.name"
                    :href="link.href"
                    class="relative text-xl font-medium uppercase text-white/90 transition-colors hover:text-white"
                >
                    <span v-if="link.isTrack" class="inline-flex items-center gap-1.5 font-bold text-amber-400 hover:text-amber-300">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="absolute inline-flex h-full w-full animate-pulse rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                        </span>
                        {{ link.name }}
                    </span>
                    <template v-else>
                        {{ link.name }}
                    </template>

                    <span
                        v-if="link.isCart && cartStore.totalCount > 0"
                        class="ml-1.5 inline-flex items-center justify-center rounded-full bg-yellow-400 px-2 py-0.5 text-xs font-bold text-gray-900"
                    >
                        {{ cartStore.totalCount }}
                    </span>
                </Link>
            </nav>

            <!-- Mobile toggle -->
            <button
                type="button"
                class="flex h-9 w-9 items-center justify-center rounded-full text-white transition-colors hover:bg-white/10 active:scale-95 md:hidden"
                :aria-expanded="mobileOpen"
                aria-label="Toggle menu"
                @click="toggleMobile"
            >
                <svg
                    viewBox="0 0 24 24"
                    class="h-5 w-5 fill-none stroke-current"
                    stroke-width="2"
                >
                    <path
                        v-if="!mobileOpen"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M4 6h16M4 12h16M4 18h16"
                    />
                    <path
                        v-else
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M6 6l12 12M18 6L6 18"
                    />
                </svg>
            </button>
        </div>

        <!-- Mobile dropdown panel -->
        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-150 ease-out"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2"
        >
            <nav
                v-if="mobileOpen"
                class="absolute left-4 right-4 top-[4.5rem] flex flex-col gap-1 rounded-2xl bg-gray-800/95 p-3 shadow-lg shadow-black/20 ring-1 ring-white/10 backdrop-blur-md md:hidden"
            >
                <Link
                    v-for="link in links"
                    :key="link.name"
                    :href="link.href"
                    class="flex items-center justify-between rounded-lg px-4 py-2.5 text-sm font-semibold text-white/90 transition-colors hover:bg-white/10 hover:text-white"
                    @click="mobileOpen = false"
                >
                    <span v-if="link.isTrack" class="inline-flex items-center gap-1.5 text-amber-400 font-bold">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-pulse rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-amber-500"></span>
                        </span>
                        {{ link.name }}
                    </span>
                    <span v-else>{{ link.name }}</span>

                    <span
                        v-if="link.isCart && cartStore.totalCount > 0"
                        class="rounded-full bg-yellow-400 px-2 py-0.5 text-xs font-bold text-gray-900"
                    >
                        {{ cartStore.totalCount }}
                    </span>
                </Link>
            </nav>
        </Transition>
    </header>
</template>
