<script setup>
import { ref, onMounted, watch, computed } from "vue";
import { useRouter } from "vue-router";
import axios from "@/lib/axios";
import { showError, showConfirm } from "@/lib/swal";
import { Plus, Pencil, Trash2, Download, Upload } from "lucide-vue-next";
import { useAuth } from "@/composables/useAuth";
import { useStatusBadge } from "@/composables/useStatusBadge";
import { useDateFormat } from "@/composables/useDateFormat";
import { useToast } from "@/composables/useToast";
import BaseCard from "@/components/ui/BaseCard.vue";
import DataTable from "@/components/ui/DataTable.vue";
import BaseButton from "@/components/ui/BaseButton.vue";
import BaseBadge from "@/components/ui/BaseBadge.vue";
import SearchFilterBar from "@/components/ui/SearchFilterBar.vue";
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
const sortValue = ref("");
const currentPage = ref(1);
const lastPage = ref(1);
const perPage = ref(10);
const total = ref(0);
const showDeleteModal = ref(false);
const userToDelete = ref(null);
const showExportModal = ref(false);
const showImportModal = ref(false);

const sortOptions = [
    { value: "name_asc", label: "Name (A-Z)" },
    { value: "name_desc", label: "Name (Z-A)" },
    { value: "role_asc", label: "Role (A-Z)" },
    { value: "role_desc", label: "Role (Z-A)" },
];

const columns = [
    { key: "name", label: "Name", sortable: true },
    { key: "email", label: "Email", sortable: true },
    { key: "role", label: "Role", sortable: true },
    { key: "is_active", label: "Status", sortable: false },
    { key: "created_at", label: "Created At", sortable: true },
];

onMounted(() => {
    fetchUsers();
});

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

        if (sortValue.value) {
            const [field, direction] = sortValue.value.split("_");
            params.sort = field;
            params.order = direction;
        }

        const response = await axios.get("/users", { params });

        // Handle paginated response if backend sends it
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

const handleSearch = () => {
    currentPage.value = 1;
    fetchUsers();
};

const handleClear = () => {
    searchQuery.value = "";
    sortValue.value = "";
    currentPage.value = 1;
    fetchUsers();
};

const handleSort = (columnKey) => {
    console.log("Sort by:", columnKey);
    fetchUsers();
};

const handlePageChange = (page) => {
    currentPage.value = page;
    fetchUsers();
};

const handleRowClick = (row) => {
    router.push(`/users/${row.id}/edit`);
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

watch(searchQuery, () => {
    currentPage.value = 1;
    fetchUsers();
});

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
            <SearchFilterBar
                v-model:search-query="searchQuery"
                v-model:sort-value="sortValue"
                :sort-options="sortOptions"
                placeholder="Search users by name or email..."
                @search="handleSearch"
                @clear="handleClear"
            />
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
            @row-click="handleRowClick"
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
