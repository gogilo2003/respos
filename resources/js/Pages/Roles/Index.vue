<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface RoleData {
    id: number;
    name: string;
    permissions: string[];
}

interface PermissionItem {
    key: string;
    label: string;
    routeName: string;
    showInNav: boolean;
}

interface Props {
    roles: RoleData[];
    catalogGrouped: Record<string, PermissionItem[]>;
}

const props = defineProps<Props>();

const localRoles = ref<RoleData[]>(JSON.parse(JSON.stringify(props.roles)));
const savingRoleId = ref<number | null>(null);
const successMessage = ref<string | null>(null);

const hasPermission = (role: RoleData, permKey: string): boolean => {
    return role.permissions.includes(permKey);
};

const togglePermission = (roleIndex: number, permKey: string) => {
    const role = localRoles.value[roleIndex];
    if (role.name === 'admin') {
        // Admin retains all permissions
        return;
    }

    const index = role.permissions.indexOf(permKey);
    if (index > -1) {
        role.permissions.splice(index, 1);
    } else {
        role.permissions.push(permKey);
    }
};

const saveRolePermissions = (role: RoleData) => {
    savingRoleId.value = role.id;
    successMessage.value = null;

    router.patch(
        route('roles.update', role.id),
        { permissions: role.permissions },
        {
            preserveScroll: true,
            onSuccess: () => {
                savingRoleId.value = null;
                successMessage.value = `Permissions for role '${role.name}' saved successfully.`;
                setTimeout(() => {
                    successMessage.value = null;
                }, 4000);
            },
            onError: () => {
                savingRoleId.value = null;
            },
        }
    );
};
</script>

<template>
    <Head title="Role & Permission Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Role & Permission Management
                    </h2>
                    <p class="mt-1 text-xs text-gray-500">
                        Map route permissions to roles and dynamically control sidebar navigation and workspace access.
                    </p>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">

                <div v-if="successMessage" class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-800 shadow-sm">
                    {{ successMessage }}
                </div>

                <!-- Roles Overview & Permission Matrix -->
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                        <h3 class="text-base font-bold text-gray-900">
                            Role Permissions Matrix
                        </h3>
                        <p class="text-xs text-gray-500">
                            Configure permissions for each role. Changes update backend middleware checks and sidebar navigation menus immediately.
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                            <thead class="bg-gray-100 font-semibold text-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 min-w-[240px]">
                                        Feature / Permission Key
                                    </th>
                                    <th
                                        v-for="(role, roleIdx) in localRoles"
                                        :key="role.id"
                                        scope="col"
                                        class="px-4 py-3 text-center min-w-[130px] uppercase tracking-wider text-xs"
                                    >
                                        <div class="font-bold text-gray-900">{{ role.name }}</div>
                                        <button
                                            v-if="role.name !== 'admin'"
                                            type="button"
                                            class="mt-1 inline-flex items-center text-[10px] font-semibold text-blue-600 hover:text-blue-800 disabled:opacity-50"
                                            :disabled="savingRoleId === role.id"
                                            @click="saveRolePermissions(role)"
                                        >
                                            {{ savingRoleId === role.id ? 'Saving...' : 'Save Role' }}
                                        </button>
                                        <span v-else class="mt-1 block text-[10px] font-medium text-gray-400">
                                            (Super-Admin)
                                        </span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <template v-for="(items, groupName) in props.catalogGrouped" :key="groupName">
                                    <!-- Group Header Row -->
                                    <tr class="bg-gray-50/80">
                                        <td :colspan="localRoles.length + 1" class="px-6 py-2 text-xs font-bold uppercase tracking-wider text-gray-500">
                                            {{ groupName }}
                                        </td>
                                    </tr>

                                    <!-- Permission Item Rows -->
                                    <tr
                                        v-for="item in items"
                                        :key="item.key"
                                        class="hover:bg-gray-50 transition"
                                    >
                                        <td class="px-6 py-3">
                                            <div class="font-medium text-gray-900">{{ item.label }}</div>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <code class="text-[11px] font-mono text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">
                                                    {{ item.key }}
                                                </code>
                                                <span v-if="item.showInNav" class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-medium text-blue-700">
                                                    Sidebar Item
                                                </span>
                                            </div>
                                        </td>

                                        <td
                                            v-for="(role, roleIdx) in localRoles"
                                            :key="role.id"
                                            class="px-4 py-3 text-center align-middle"
                                        >
                                            <input
                                                type="checkbox"
                                                :checked="hasPermission(role, item.key)"
                                                :disabled="role.name === 'admin' || savingRoleId === role.id"
                                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 disabled:opacity-60 cursor-pointer"
                                                @change="togglePermission(roleIdx, item.key)"
                                            />
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
