<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import BaseCard from '@/components/ui/BaseCard.vue';
import BaseTable from '@/components/ui/BaseTable.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import BaseBadge from '@/components/ui/BaseBadge.vue';
import BasePagination from '@/components/ui/BasePagination.vue';
import SearchFilterBar from '@/components/ui/SearchFilterBar.vue';

const router = useRouter();

const mutations = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const sortValue = ref('');
const currentPage = ref(1);
const perPage = ref(10);
const total = ref(0);

const sortOptions = [
  { value: 'date_desc', label: 'Date (Newest First)' },
  { value: 'date_asc', label: 'Date (Oldest First)' },
  { value: 'status_pending', label: 'Status (Pending First)' },
  { value: 'quantity_desc', label: 'Quantity (High to Low)' },
];

const columns = [
  { key: 'code', label: 'Code', sortable: true },
  { key: 'item_name', label: 'Item', sortable: true },
  { key: 'type', label: 'Type', sortable: false },
  { key: 'quantity', label: 'Quantity', sortable: true },
  { key: 'status', label: 'Status', sortable: false },
  { key: 'created_by', label: 'Created By', sortable: false },
  { key: 'date', label: 'Date', sortable: true },
];

onMounted(() => {
  fetchMutations();
});

const fetchMutations = async () => {
  loading.value = true;
  try {
    mutations.value = [
      { id: 1, code: 'MUT001', item_name: 'Laptop Dell XPS 15', type: 'IN', quantity: 10, status: 'pending', created_by: 'John Doe', date: '2026-07-25' },
      { id: 2, code: 'MUT002', item_name: 'Mouse Wireless', type: 'OUT', quantity: 5, status: 'approved', created_by: 'Jane Smith', date: '2026-07-25' },
      { id: 3, code: 'MUT003', item_name: 'Keyboard Mechanical', type: 'IN', quantity: 8, status: 'pending', created_by: 'Mike Johnson', date: '2026-07-24' },
      { id: 4, code: 'MUT004', item_name: 'Monitor 27 inch', type: 'OUT', quantity: 3, status: 'rejected', created_by: 'Sarah Williams', date: '2026-07-24' },
      { id: 5, code: 'MUT005', item_name: 'USB Cable Type-C', type: 'IN', quantity: 50, status: 'approved', created_by: 'John Doe', date: '2026-07-23' },
    ];
    total.value = mutations.value.length;
  } catch (error) {
    console.error('Failed to fetch mutations:', error);
  } finally {
    loading.value = false;
  }
};

const handleSearch = () => {
  currentPage.value = 1;
  fetchMutations();
};

const handleClear = () => {
  searchQuery.value = '';
  sortValue.value = '';
  currentPage.value = 1;
  fetchMutations();
};

const handleSort = (columnKey) => {
  console.log('Sort by:', columnKey);
  fetchMutations();
};

const handlePageChange = (page) => {
  currentPage.value = page;
  fetchMutations();
};

const handleRowClick = (row) => {
  router.push(`/stock-mutations/${row.id}`);
};

const navigateToCreate = () => {
  router.push('/stock-mutations/create');
};

const handleView = (row) => {
  router.push(`/stock-mutations/${row.id}`);
};

const getStatusVariant = (status) => {
  const variants = {
    pending: 'pending',
    approved: 'approved',
    rejected: 'rejected',
  };
  return variants[status] || 'default';
};

const getTypeVariant = (type) => {
  return type === 'IN' ? 'success' : 'warning';
};
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Stock Mutations</h1>
        <p class="mt-1 text-sm text-gray-600">Track all inventory movements and changes</p>
      </div>
      <BaseButton @click="navigateToCreate" variant="primary">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        New Mutation
      </BaseButton>
    </div>

    <BaseCard :padding="false" :shadow="true">
      <div class="p-6">
        <SearchFilterBar
          v-model:search-query="searchQuery"
          v-model:sort-value="sortValue"
          :sort-options="sortOptions"
          placeholder="Search mutations by code or item..."
          @search="handleSearch"
          @clear="handleClear"
        />
      </div>

      <BaseTable
        :columns="columns"
        :data="mutations"
        :loading="loading"
        :sortable="true"
        @sort="handleSort"
        @row-click="handleRowClick"
      >
        <template #cell-type="{ value }">
          <BaseBadge :variant="getTypeVariant(value)">
            {{ value }}
          </BaseBadge>
        </template>

        <template #cell-status="{ value }">
          <BaseBadge :variant="getStatusVariant(value)">
            {{ value }}
          </BaseBadge>
        </template>

        <template #cell-quantity="{ value, row }">
          <span :class="{ 'text-green-600 font-semibold': row.type === 'IN', 'text-red-600 font-semibold': row.type === 'OUT' }">
            {{ row.type === 'IN' ? '+' : '-' }}{{ value }}
          </span>
        </template>

        <template #actions="{ row }">
          <BaseButton @click.stop="handleView(row)" variant="ghost" size="sm">
            View Details
          </BaseButton>
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
