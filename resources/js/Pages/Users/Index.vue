<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import type { Role, UserListItem as User } from '@/interfaces/user';

const props = defineProps<{
    users: User[];
    roles: Role[];
}>();

const confirmingUserDeletion = ref(false);
const userToDelete = ref<number | null>(null);
const editingUser = ref<User | null>(null);
const showUserModal = ref(false);

const form = useForm({
    role_id: '',
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    is_active: true,
});

const openCreateModal = () => {
    editingUser.value = null;
    form.reset();
    showUserModal.value = true;
};

const openEditModal = (user: User) => {
    editingUser.value = user;
    form.clearErrors();
    form.role_id = user.role_id.toString();
    form.name = user.name;
    form.username = user.username;
    form.email = user.email || '';
    form.is_active = user.is_active;
    form.password = '';
    form.password_confirmation = '';
    showUserModal.value = true;
};

const submit = () => {
    if (editingUser.value) {
        form.patch(route('users.update', editingUser.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('users.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const closeModal = () => {
    showUserModal.value = false;
    form.reset();
};

const confirmUserDeletion = (id: number) => {
    userToDelete.value = id;
    confirmingUserDeletion.value = true;
};

const deleteUser = () => {
    if (userToDelete.value) {
        form.delete(route('users.destroy', userToDelete.value), {
            onSuccess: () => (confirmingUserDeletion.value = false),
        });
    }
};
</script>

<template>

    <Head title="Users Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Users Management
                </h2>
                <PrimaryButton @click="openCreateModal">
                    Add User
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Name</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Username</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Role</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="user in users" :key="user.id">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ user.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ user.username }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ user.role.name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            {{ user.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <button @click="openEditModal(user)"
                                            class="text-indigo-600 hover:text-indigo-900">Edit</button>
                                        <button @click="confirmUserDeletion(user.id)"
                                            class="text-red-600 hover:text-red-900">Delete</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Modal -->
        <Modal :show="showUserModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ editingUser ? 'Edit User' : 'Add New User' }}
                </h2>

                <form @submit.prevent="submit" class="mt-6 space-y-4">
                    <div>
                        <InputLabel for="role_id" value="Role" />
                        <select id="role_id" v-model="form.role_id"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="" disabled>Select a role</option>
                            <option v-for="role in roles" :key="role.id" :value="role.id.toString()">
                                {{ role.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.role_id" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="name" value="Name" />
                        <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="username" value="Username" />
                        <TextInput id="username" v-model="form.username" type="text" class="mt-1 block w-full"
                            required />
                        <InputError :message="form.errors.username" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="email" value="Email (Optional)" />
                        <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" />
                        <InputError :message="form.errors.email" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="password"
                            :value="editingUser ? 'Password (Leave blank to keep current)' : 'Password'" />
                        <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full"
                            :required="!editingUser" />
                        <InputError :message="form.errors.password" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="password_confirmation" value="Confirm Password" />
                        <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password"
                            class="mt-1 block w-full" :required="!editingUser" />
                    </div>

                    <div v-if="editingUser" class="flex items-center">
                        <input type="checkbox" id="is_active" v-model="form.is_active"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <SecondaryButton @click="closeModal"> Cancel </SecondaryButton>
                        <PrimaryButton class="ms-3" :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing">
                            {{ editingUser ? 'Update User' : 'Create User' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="confirmingUserDeletion" @close="confirmingUserDeletion = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Are you sure you want to delete this user?
                </h2>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="confirmingUserDeletion = false"> Cancel </SecondaryButton>
                    <DangerButton class="ms-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing"
                        @click="deleteUser">
                        Delete User
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
