<script setup lang="ts">
import WebLayout from '@/Layouts/WebLayout.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <WebLayout title="Invalid QR Code">
        <div class="min-h-screen bg-gray-50">
            <div class="mx-auto max-w-md px-4 py-16">
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <div class="p-6 text-center">
                        <h1 class="text-2xl font-bold text-gray-900">
                            Invalid QR Code
                        </h1>
                        <p class="mt-2 text-sm text-gray-600">
                            This QR code is invalid or the table is no longer
                            active. Please contact the restaurant staff for
                            assistance.
                        </p>

                        <Link
                            href="/"
                            class="mt-6 inline-block rounded-lg bg-black px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-800"
                        >
                            Return to Home
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </WebLayout>
</template>
