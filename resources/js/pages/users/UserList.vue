<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '@/composables/useAuth';
import BaseCard from '@/components/ui/BaseCard.vue';
import BaseTable from '@/components/ui/BaseTable.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import BaseBadge from '@/components/ui/BaseBadge.vue';
import BasePagination from '@/components/ui/BasePagination.vue';
import SearchFilterBar from '@/components/ui/SearchFilterBar.vue';

const router = useRouter();
const { isAdministrator } = useAuth();

const users = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const sortValue = ref('');
const currentPage = ref(1);
const perPage = ref(10);
const total = ref(0);

const sortOptions = [
  { value: 'name_asc', label: 'Name (A-Z)' },
  { value: 'name_desc', label: 'Name (Z-A)' },
  { value: 'role_asc', label: 'Role (A-Z)' },
  { value: 'role_desc', label: 'Role (Z-A)' },
];

const columns = [
  { key: 'name', label: 'Name', sortable: true },
  { key: 'email', label: 'Email', sortable: true },
  { key: 'role', label: 'Role', sortable: true },
  { key: 'status', label: 'Status', sortable: false },
  { key: 'created_at', label: 'Created At', sortable: true },
];

onMounted(() => {
  if (!isAdministrator.value) {
    router.push('/dashboard');
    return;
  }
  fetchUsers();
});

const fetchUsers = async () => {
  loading.value = true;
  try {
    users.value = [
      { id: 1, name: 'John Doe', email: 'john@example.com', role: 'Administrator', status: 'active', created_at: '2026-01-15' },
      { id: 2, name: 'Jane Smith', email: 'jane@example.com', role: 'Manager', status: 'active', created_at: '2026-02-20' },
      { id: 3, name: 'Mike Johnson', email: 'mike@example.com', role: 'Staff', status: 'active', created_at: '2026-03-10' },
      { id: 4, name: 'Sarah Williams', email: 'sarah@example.com', role: 'Staff', status: 'inactive', created_at: '2026-04-05' },
    ];
    total.value = users.value.length;
  } catch (error) {
    console.error('Failed to fetch users:', error);
  } finally {
    loading.value = false;
  }
};

const handleSearch = () => {
  currentPage.value = 1;
  fetchUsers();
};

const handleClear = () => {
  searchQuery.value = '';
  sortValue.value = '';
  currentPage.value = 1;
  fetchUsers();
};

const handleSort = (columnKey) => {
  console.log('Sort by:', columnKey);
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
  router.push('/users/create');
};

const handleEdit = (row) => {
  router.push(`/users/${row.id}/edit`);
};

const handleDelete = (row) => {
  if (confirm(`Are you sure you want to delete user ${row.name}?`)) {
    console.log('Delete user:', row.id);
    fetchUsers();
  }
};

const getStatusVariant = (status) => {
  return status === 'active' ? 'success' : 'default';
};

const getRoleBadgeVariant = (role) => {
  const variants = {
    Administrator: 'danger',
    Manager: 'warning',
    Staff: 'info',
  };
  return variants[role] || 'default';
};
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Users</h1>
        <p class="mt-1 text-sm text-gray-600">Manage system users and their roles</p>
      </div>
      <BaseButton @click="navigateToCreate" variant="primary">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add User
      </BaseButton>
    </div>

    <BaseCard :padding="false" :shadow="true">
      <div class="p-6">
        <SearchFilterBar
          v-model:search-query="searchQuery"
          v-model:sort-value="sortValue"
          :sort-options="sortOptions"
          placeholder="Search users by name or email..."
          @search="handleSearch"
          @clear="handleClear"
        />
      </div>

      <BaseTable
        :columns="columns"
        :data="users"
        :loading="loading"
        :sortable="true"
        @sort="handleSort"
        @row-click="handleRowClick"
      >
        <template #cell-role="{ value }">
          <BaseBadge :variant="getRoleBadgeVariant(value)">
            {{ value }}
          </BaseBadge>
        </template>

        <template #cell-status="{ value }">
          <BaseBadge :variant="getStatusVariant(value)">
            {{ value }}
          </BaseBadge>
        </template>

        <template #actions="{ row }">
          <div class="flex items-center gap-2">
            <BaseButton @click.stop="handleEdit(row)" variant="ghost" size="sm">
              Edit
            </BaseButton>
            <BaseButton @click.stop="handleDelete(row)" variant="ghost" size="sm" class="text-red-600 hover:text-red-700">
              Delete
            </BaseButton>
          </div>
        </template>
      </BaseTable>

      <div class="border-t border-gray-200">
        <BasePagination
          :current-page="currentPage"
          :total-pages="Math.ceil(total / perPage)"
          :per-page="perPage"
          :total="total"
          @page-change="handlePageChange"
        />
      </div>
    </BaseCard>
  </div>
</template>
