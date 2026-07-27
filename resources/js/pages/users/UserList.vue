<script setup>
import { ref, onMounted, watch, computed } from "vue";
import { useRouter } from "vue-router";
import axios from "@/lib/axios";
import { showError, showConfirm } from "@/lib/swal";
import { Plus, Pencil, Trash2, Download, Upload, Search, RefreshCw } from "lucide-vue-next";
import { useAuth } from "@/composables/useAuth";
import { useStatusBadge } from "@/composables/useStatusBadge";
import { useDateFormat } from "@/composables/useDateFormat";
import { useToast } from "@/composables/useToast";
import BaseCard from "@/components/ui/BaseCard.vue";
import DataTable from "@/components/ui/DataTable.vue";
import BaseButton from "@/components/ui/BaseButton.vue";
import BaseBadge from "@/components/ui/BaseBadge.vue";
import BaseModal from "@/components/ui/BaseModal.vue";
import ExportModal from "@/components/ui/ExportModal.vue";
import ImportModal from "@/components/ui/ImportModal.vue";

const router = useRouter();
const { isAdministrator } = useAuth();
const { getVariant } = useStatusBadge();
const { formatDate } = useDateFormat();
const { toastSuccess } = useToast();

const users = ref([]);
const loading = ref(false);
const searchQuery = ref("");
const searchDebounceTimer = ref(null);
const sortField = ref("");
const sortDirection = ref("");
const filterRole = ref("");
const roles = ref([]);
const currentPage = ref(1);
const lastPage = ref(1);
const perPage = ref(10);
const total = ref(0);
const showDeleteModal = ref(false);
const userToDelete = ref(null);
const showExportModal = ref(false);
const showImportModal = ref(false);

const columns = [
    { key: "name", label: "Name", sortable: true },
    { key: "email", label: "Email", sortable: true },
    { key: "role", label: "Role", sortable: true },
    { key: "is_active", label: "Status", sortable: false },
    { key: "created_at", label: "Created At", sortable: true },
];

onMounted(() => {
    fetchRoles();
    fetchUsers();
});

const fetchRoles = async () => {
    try {
        const response = await axios.get("/roles");
        if (response.data && response.data.data) {
            roles.value = response.data.data;
        } else {
            roles.value = response.data;
        }
    } catch (error) {
        console.error("Failed to fetch roles:", error);
        roles.value = [];
    }
};

const fetchUsers = async () => {
    loading.value = true;
    try {
        const params = {
            page: currentPage.value,
            per_page: perPage.value,
        };

        if (searchQuery.value) {
            params.search = searchQuery.value;
        }

        if (filterRole.value) {
            params.role_id = filterRole.value;
        }

        if (sortField.value && sortDirection.value) {
            params.sort = sortField.value;
            params.order = sortDirection.value;
        }

        const response = await axios.get("/users", { params });

        if (response.data && response.data.data) {
            users.value = response.data.data;
            currentPage.value = response.data.current_page || 1;
            lastPage.value =
                response.data.last_page ||
                Math.ceil((response.data.total || 0) / perPage.value) ||
                1;
            total.value = response.data.total || 0;
        } else {
            users.value = response.data;
            total.value = users.value.length;
            lastPage.value = Math.ceil(total.value / perPage.value) || 1;
        }
    } catch (error) {
        console.error("Failed to fetch users:", error);
        users.value = [];
        total.value = 0;
    } finally {
        loading.value = false;
    }
};

const handleSearchInput = () => {
    if (searchDebounceTimer.value) {
        clearTimeout(searchDebounceTimer.value);
    }
    
    searchDebounceTimer.value = setTimeout(() => {
        currentPage.value = 1;
        fetchUsers();
    }, 500);
};

const handleClear = () => {
    searchQuery.value = "";
    sortField.value = "";
    sortDirection.value = "";
    filterRole.value = "";
    currentPage.value = 1;
    fetchUsers();
};

const handleFilterChange = () => {
    currentPage.value = 1;
    fetchUsers();
};

const handleSort = (sortData) => {
    sortField.value = sortData.key;
    sortDirection.value = sortData.direction;
    currentPage.value = 1;
    fetchUsers();
};

const handleRefresh = () => {
    fetchUsers();
};

const handlePageChange = (page) => {
    currentPage.value = page;
    fetchUsers();
};



const navigateToCreate = () => {
    router.push("/users/create");
};

const handleEdit = (row) => {
    router.push(`/users/${row.id}/edit`);
};

const openDeleteModal = (row) => {
    userToDelete.value = row;
    showDeleteModal.value = true;
};

const confirmDelete = async () => {
    if (!userToDelete.value) return;

    try {
        loading.value = true;
        await axios.delete(`/users/${userToDelete.value.id}`);
        toastSuccess("User Deleted", "User has been deleted successfully");
        fetchUsers();

        showDeleteModal.value = false;
        userToDelete.value = null;
    } catch (error) {
        console.error("Failed to delete user:", error);
        showError(
            error.response?.data?.message ||
                "Failed to delete user. Please try again.",
        );
    } finally {
        loading.value = false;
    }
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    userToDelete.value = null;
};

const openExportModal = () => {
    showExportModal.value = true;
};

const openImportModal = () => {
    showImportModal.value = true;
};

const handleExported = (jobId) => {
    router.push({ name: "ExportImportHistory" });
};

const handleImported = (jobId) => {
    fetchUsers();
    router.push({ name: "ExportImportHistory" });
};

const handleDelete = (row) => {
    openDeleteModal(row);
};



const getRoleBadgeVariant = (role) => {
    const variants = {
        Administrator: "danger",
        Manager: "warning",
        Staff: "info",
    };
    return variants[role] || "default";
};
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                    Users
                </h1>
                <p class="mt-1 text-sm text-slate-600">
                    Manage system users and their roles
                </p>
            </div>
            <div class="flex items-center gap-2">
                <BaseButton @click="openExportModal" variant="secondary">
                    <Download :size="18" />
                    Export
                </BaseButton>
                <BaseButton @click="openImportModal" variant="secondary">
                    <Upload :size="18" />
                    Import
                </BaseButton>
                <BaseButton @click="navigateToCreate">
                    <Plus :size="18" />
                    Add User
                </BaseButton>
            </div>
        </div>

        <BaseCard>
            <div class="flex items-center gap-3">
                <div class="relative flex-1">
                    <Search
                        class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
                        :size="20"
                    />
                    <input
                        v-model="searchQuery"
                        @input="handleSearchInput"
                        type="text"
                        placeholder="Search users by name or email..."
                        class="w-full pl-10 pr-4 py-2.5 text-sm border focus:outline-none border-slate-300 rounded-lg focus:ring-1/2 focus:ring-primary-500 focus:border-primary-500 transition-all"
                    />
                </div>
                <div class="min-w-[200px]">
                    <select
                        v-model="filterRole"
                        @change="handleFilterChange"
                        class="w-full pl-3 pr-9 py-2.5 text-sm border focus:outline-none border-slate-300 rounded-lg focus:ring-1/2 focus:ring-primary-500 focus:border-primary-500 transition-all bg-white appearance-none bg-[url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27%23666%27 stroke-width=%272%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3e%3cpolyline points=%276 9 12 15 18 9%27%3e%3c/polyline%3e%3c/svg%3e')] bg-[length:20px] bg-[center_right_0.75rem] bg-no-repeat"
                    >
                        <option value="">All Roles</option>
                        <option v-for="role in roles" :key="role.id" :value="role.id">
                            {{ role.name }}
                        </option>
                    </select>
                </div>
                <button
                    @click="handleRefresh"
                    :disabled="loading"
                    class="p-2.5 text-slate-600 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                    title="Refresh"
                >
                    <RefreshCw :size="20" :class="{ 'animate-spin': loading }" />
                </button>
                <button
                    v-if="searchQuery || filterRole"
                    @click="handleClear"
                    class="px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors whitespace-nowrap"
                >
                    Clear
                </button>
            </div>
        </BaseCard>

        <DataTable
            :columns="columns"
            :data="users"
            :loading="loading"
            empty-message="No users found"
            :show-pagination="true"
            :current-page="currentPage"
            :last-page="lastPage"
            :total="total"
            @sort="handleSort"
            @page-change="handlePageChange"
        >
            <template #cell-role="{ row }">
                <BaseBadge :variant="getRoleBadgeVariant(row.role?.name)">
                    {{ row.role?.name || "-" }}
                </BaseBadge>
            </template>

            <template #cell-is_active="{ value }">
                <BaseBadge :variant="value ? 'success' : 'default'">
                    {{ value ? "Active" : "Inactive" }}
                </BaseBadge>
            </template>

            <template #cell-created_at="{ value }">
                {{ formatDate(value) }}
            </template>

            <template #actions="{ row }">
                <div class="flex items-center gap-1 justify-end">
                    <button
                        @click.stop="handleEdit(row)"
                        class="p-2 text-slate-600 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors"
                        title="Edit User"
                    >
                        <Pencil :size="16" />
                    </button>
                    <button
                        @click.stop="handleDelete(row)"
                        class="p-2 text-slate-600 hover:text-danger-600 hover:bg-danger-50 rounded-lg transition-colors"
                        title="Delete User"
                    >
                        <Trash2 :size="16" />
                    </button>
                </div>
            </template>
        </DataTable>

        <BaseModal v-model="showDeleteModal" title="Delete User" size="sm">
            <div class="space-y-4">
                <p class="text-sm text-slate-600">
                    Are you sure you want to delete user
                    <strong>{{ userToDelete?.name }}</strong
                    >? This action cannot be undone.
                </p>
            </div>

            <template #footer>
                <BaseButton variant="ghost" @click="cancelDelete">
                    Cancel
                </BaseButton>
                <BaseButton variant="danger" @click="confirmDelete">
                    Delete User
                </BaseButton>
            </template>
        </BaseModal>

        <ExportModal
            v-model="showExportModal"
            model="user"
            @exported="handleExported"
        />

        <ImportModal
            v-model="showImportModal"
            model="user"
            @imported="handleImported"
        />
    </div>
</template>
