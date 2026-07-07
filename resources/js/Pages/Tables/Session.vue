<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    table: {
        id: number;
        table_number: string;
        capacity: number;
        location: string | null;
        status: string;
        is_active: boolean;
    };
    session: {
        id: number;
        session_token: string;
        status: string;
        opened_at: string;
    };
    qr_payload: string;
    menu_url: string;
}>();

const sessionStatus = ref(props.session.status);

const continueToMenu = () => {
    window.location.href = props.menu_url + '?session_token=' + props.session.session_token;
};
</script>

<template>
    <Head title="Table Session" />

    <div class="min-h-screen bg-gray-50">
        <div class="mx-auto max-w-md px-4 py-16">
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <div class="p-6 text-center">
                    <h1 class="text-2xl font-bold text-gray-900">
                        Table {{ table.table_number }}
                    </h1>
                    <p class="mt-2 text-sm text-gray-600">
                        Your session is active. You can now browse the menu and place your order.
                    </p>

                    <div class="mt-6 rounded-md bg-green-50 p-4 text-sm text-green-700">
                        Session Status: {{ sessionStatus }}
                    </div>

                    <button
                        type="button"
                        @click="continueToMenu"
                        class="mt-6 w-full rounded-lg bg-black px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-800"
                    >
                        Continue to Menu
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>