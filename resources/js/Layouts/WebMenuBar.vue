<script lang="ts" setup>
import { Link } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const links = ref([
    { name: 'Home', href: '/' },
    { name: 'Categories', href: '/categories' },
    { name: 'Menu', href: '/menu' },
    { name: 'About', href: '/about' },
    { name: 'Cart', href: '/cart' },
]);

// Pixels scrolled before the bar switches from transparent (floating over
// hero content) to a solid dark bar. Swap for `window.innerHeight` if you
// want the switch tied to "past the first screen" rather than a fixed value.
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
    <!--
        Fixed to the viewport, floating with a bit of top margin so it
        reads as a pill rather than a full-bleed bar. Because it's `fixed`,
        the page underneath needs top padding equal to roughly this bar's
        height so content doesn't start out hidden behind it.
    -->
    <header class="fixed inset-x-0 top-0 z-50 flex justify-center px-4">
        <div class="flex w-full max-w-7xl items-center justify-between rounded-full px-8 py-3 transition-all duration-300"
            :class="isScrolled
                ? 'bg-gray-800/90 shadow-lg shadow-black/20 backdrop-blur-md ring-1 ring-white/10'
                : 'bg-transparent'">
            <Link href="/" class="text-base font-bold tracking-wide text-white">
                <ApplicationLogo class="h-10 w-auto" />
            </Link>

            <!-- Desktop links -->
            <nav class="hidden items-center gap-6 md:flex">
                <Link v-for="link in links" :key="link.name" :href="link.href"
                    class="text-xl font-medium uppercase text-white/90 transition-colors hover:text-white">
                    {{ link.name }}
                </Link>
            </nav>

            <!-- Mobile toggle -->
            <button type="button"
                class="flex h-9 w-9 items-center justify-center rounded-full text-white transition-colors hover:bg-white/10 md:hidden"
                :aria-expanded="mobileOpen" aria-label="Toggle menu" @click="toggleMobile">
                <svg viewBox="0 0 24 24" class="h-5 w-5 stroke-current fill-none" stroke-width="2">
                    <path v-if="!mobileOpen" stroke-linecap="round" stroke-linejoin="round"
                        d="M4 6h16M4 12h16M4 18h16" />
                    <path v-else stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18" />
                </svg>
            </button>
        </div>

        <!-- Mobile dropdown panel -->
        <Transition enter-active-class="transition duration-150 ease-out" enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0" leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
            <nav v-if="mobileOpen"
                class="absolute left-4 right-4 top-[4.5rem] flex flex-col gap-1 rounded-2xl bg-gray-800/95 p-3 shadow-lg shadow-black/20 ring-1 ring-white/10 backdrop-blur-md md:hidden">
                <Link v-for="link in links" :key="link.name" :href="link.href"
                    class="rounded-lg px-4 py-2.5 text-sm font-semibold text-white/90 transition-colors hover:bg-white/10 hover:text-white"
                    @click="mobileOpen = false">
                    {{ link.name }}
                </Link>
            </nav>
        </Transition>
    </header>
</template>