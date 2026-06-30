<script setup lang="ts">
import LandingNavBar from '@/Components/LandingNavBar.vue';
import { Head } from '@inertiajs/vue3';

const features = [
    {
        title: 'QR Ordering',
        description:
            'Customers scan a QR code to view the menu and place orders instantly.',
    },
    {
        title: 'Fast Billing',
        description:
            'Cash-first billing with clear receipts and smooth checkout for staff.',
    },
    {
        title: 'Kitchen & Waiter Flow',
        description:
            'Orders move from QR → kitchen → payment with fewer handoffs.',
    },
];
</script>

<template>
    <Head title="About" />

    <div class="min-h-screen bg-white">
        <LandingNavBar />

        <header class="bg-[#ffea95] py-16">
            <div class="mx-auto max-w-7xl px-4">
                <h1 class="text-4xl font-extrabold text-black">About ResPos</h1>
                <p class="mt-3 max-w-2xl text-sm text-black/90 sm:text-base">
                    ResPos is a QR-based restaurant ordering and billing system
                    built to make operations simpler—for customers, waiters, and
                    the kitchen.
                </p>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-12">
            <section class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <article
                    v-for="f in features"
                    :key="f.title"
                    class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm"
                >
                    <h2 class="text-lg font-bold text-gray-900">
                        {{ f.title }}
                    </h2>
                    <p class="mt-2 text-sm text-gray-600">
                        {{ f.description }}
                    </p>
                </article>
            </section>

            <section class="mt-10">
                <h2 class="text-2xl font-bold text-gray-900">Our goal</h2>
                <p class="mt-3 max-w-3xl text-sm text-gray-600 sm:text-base">
                    Help restaurants reduce waiting time, improve order
                    accuracy, and speed up billing. Everything is designed to
                    work smoothly on a cash-first release.
                </p>
            </section>
        </main>

        <footer class="bg-[#ffea95] py-8">
            <div class="mx-auto max-w-7xl px-4">
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="text-sm font-semibold text-black">
                        © {{ new Date().getFullYear() }} ResPos. All rights
                        reserved.
                    </div>
                    <div class="flex items-center gap-6 text-sm text-black/90">
                        <a href="/welcome-categories" class="hover:underline"
                            >Food Menu</a
                        >
                        <a href="/" class="hover:underline">Home</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
