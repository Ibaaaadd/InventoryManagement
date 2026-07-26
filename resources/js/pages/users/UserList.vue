<script setup>
import { ref, onMounted, watch, computed } from "vue";
import { useRouter } from "vue-router";
import axios from "@/lib/axios";
import { Plus, Pencil, Trash2 } from "lucide-vue-next";
import { useAuth } from "@/composables/useAuth";
import { useStatusBadge } from "@/composables/useStatusBadge";
import BaseCard from "@/components/ui/BaseCard.vue";
import DataTable from "@/components/ui/DataTable.vue";
import BaseButton from "@/components/ui/BaseButton.vue";
import BaseBadge from "@/components/ui/BaseBadge.vue";
import BasePagination from "@/components/ui/BasePagination.vue";
import SearchFilterBar from "@/components/ui/SearchFilterBar.vue";

const router = useRouter();
const { isAdministrator } = useAuth();
const { getVariant } = useStatusBadge();

const users = ref([]);
const loading = ref(false);
const searchQuery = ref("");
const sortValue = ref("");
const currentPage = ref(1);
const perPage = ref(10);
const total = ref(0);

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
    { key: "status", label: "Status", sortable: false },
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
            const [field, direction] = sortValue.value.split('_');
            params.sort = field;
            params.order = direction;
        }
        
        const response = await axios.get('/users', { params });
        users.value = response.data.data || response.data;
        total.value = response.data.total || users.value.length;
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

const handleDelete = async (row) => {
    if (!confirm(`Are you sure you want to delete user ${row.name}?`)) {
        return;
    }
    
    try {
        loading.value = true;
        await axios.delete(`/users/${row.id}`);
        fetchUsers();
    } catch (error) {
        console.error("Failed to delete user:", error);
        alert(error.response?.data?.message || "Failed to delete user. Please try again.");
    } finally {
        loading.value = false;
    }
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
            <BaseButton @click="navigateToCreate">
                <Plus :size="18" />
                Add User
            </BaseButton>
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
            @sort="handleSort"
            @row-click="handleRowClick"
        >
            <template #cell-role="{ value }">
                <BaseBadge :variant="getRoleBadgeVariant(value)">
                    {{ value }}
                </BaseBadge>
            </template>

            <template #cell-status="{ value }">
                <BaseBadge :variant="getVariant(value)">
                    {{ value }}
                </BaseBadge>
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

            <template #pagination>
                <BasePagination
                    :current-page="currentPage"
                    :total-pages="Math.ceil(total / perPage)"
                    :per-page="perPage"
                    :total="total"
                    @page-change="handlePageChange"
                />
            </template>
        </DataTable>
    </div>
</template>
