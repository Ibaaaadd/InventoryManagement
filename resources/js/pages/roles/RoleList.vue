<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useRouter } from "vue-router";
import axios from "@/lib/axios";
import { Search, Plus, Pencil, Trash2 } from "lucide-vue-next";
import BaseButton from "@/components/ui/BaseButton.vue";
import BaseCard from "@/components/ui/BaseCard.vue";
import DataTable from "@/components/ui/DataTable.vue";
import BaseBadge from "@/components/ui/BaseBadge.vue";
import BaseModal from "@/components/ui/BaseModal.vue";
import BaseInput from "@/components/ui/BaseInput.vue";

const router = useRouter();

const roles = ref([]);
const loading = ref(false);
const searchQuery = ref("");
const showDeleteModal = ref(false);
const roleToDelete = ref(null);

const fetchRoles = async () => {
    try {
        loading.value = true;
        const params = {};
        if (searchQuery.value) {
            params.search = searchQuery.value;
        }
        const response = await axios.get("/roles", { params });
        roles.value = response.data.data || response.data;
    } catch (error) {
        console.error("Failed to fetch roles:", error);
    } finally {
        loading.value = false;
    }
};

const columns = [
    { key: "name", label: "Role Name", sortable: true },
    { key: "description", label: "Description", sortable: false },
    { key: "users_count", label: "Users", sortable: true },
    { key: "created_at", label: "Created Date", sortable: true },
];

const filteredRoles = computed(() => roles.value);

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("id-ID", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const handleEdit = (role) => {
    router.push({ name: "RoleEdit", params: { id: role.id } });
};

const openDeleteModal = (role) => {
    roleToDelete.value = role;
    showDeleteModal.value = true;
};

const confirmDelete = async () => {
    if (!roleToDelete.value) return;

    try {
        loading.value = true;
        await axios.delete(`/roles/${roleToDelete.value.id}`);
        
        roles.value = roles.value.filter((r) => r.id !== roleToDelete.value.id);
        
        showDeleteModal.value = false;
        roleToDelete.value = null;
    } catch (error) {
        console.error("Failed to delete role:", error);
        alert(error.response?.data?.message || "Failed to delete role. Please try again.");
    } finally {
        loading.value = false;
    }
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    roleToDelete.value = null;
};

watch(searchQuery, () => {
    fetchRoles();
});

onMounted(() => {
    fetchRoles();
});
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                    Role Management
                </h1>
                <p class="text-sm text-slate-600 mt-1">
                    Manage user roles and permissions
                </p>
            </div>
            <BaseButton @click="router.push({ name: 'RoleCreate' })">
                <Plus :size="18" />
                Add Role
            </BaseButton>
        </div>

        <BaseCard>
            <div class="relative">
                <Search
                    class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
                    :size="20"
                />
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search roles..."
                    class="w-full pl-10 pr-4 py-2.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
                />
            </div>
        </BaseCard>

        <DataTable
            :columns="columns"
            :data="filteredRoles"
            :loading="loading"
            empty-message="No roles found"
        >
            <template #cell-name="{ row }">
                <div class="font-medium text-slate-900">{{ row.name }}</div>
            </template>

            <template #cell-description="{ row }">
                <div class="text-sm text-slate-600 max-w-md truncate">
                    {{ row.description }}
                </div>
            </template>

            <template #cell-users_count="{ row }">
                <BaseBadge variant="info" size="sm">
                    {{ row.users_count }}
                    {{ row.users_count === 1 ? "user" : "users" }}
                </BaseBadge>
            </template>

            <template #cell-created_at="{ row }">
                <div class="text-sm text-slate-600">
                    {{ formatDate(row.created_at) }}
                </div>
            </template>

            <template #actions="{ row }">
                <div class="flex items-center gap-1 justify-end">
                    <button
                        @click="handleEdit(row)"
                        class="p-2 text-slate-600 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors"
                        title="Edit Role"
                    >
                        <Pencil :size="16" />
                    </button>
                    <button
                        @click="openDeleteModal(row)"
                        class="p-2 text-slate-600 hover:text-danger-600 hover:bg-danger-50 rounded-lg transition-colors"
                        title="Delete Role"
                    >
                        <Trash2 :size="16" />
                    </button>
                </div>
            </template>
        </DataTable>

        <BaseModal v-model="showDeleteModal" title="Delete Role" size="sm">
            <div class="space-y-4">
                <p class="text-sm text-slate-600">
                    Are you sure you want to delete the role
                    <strong>{{ roleToDelete?.name }}</strong
                    >? This action cannot be undone.
                </p>
                <div
                    v-if="roleToDelete?.users_count > 0"
                    class="p-3 bg-warning-50 border border-warning-200 rounded-lg"
                >
                    <p class="text-sm text-warning-800">
                        <strong>Warning:</strong> This role is assigned to
                        {{ roleToDelete.users_count }} user(s). Deleting it may
                        affect their permissions.
                    </p>
                </div>
            </div>

            <template #footer>
                <BaseButton variant="ghost" @click="cancelDelete">
                    Cancel
                </BaseButton>
                <BaseButton variant="danger" @click="confirmDelete">
                    Delete Role
                </BaseButton>
            </template>
        </BaseModal>
    </div>
</template>
