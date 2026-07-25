<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import BaseCard from '@/components/ui/BaseCard.vue';
import BaseTable from '@/components/ui/BaseTable.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import BasePagination from '@/components/ui/BasePagination.vue';
import SearchFilterBar from '@/components/ui/SearchFilterBar.vue';

const router = useRouter();

const items = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const sortValue = ref('');
const currentPage = ref(1);
const perPage = ref(10);
const total = ref(0);

const sortOptions = [
  { value: 'name_asc', label: 'Name (A-Z)' },
  { value: 'name_desc', label: 'Name (Z-A)' },
  { value: 'quantity_asc', label: 'Quantity (Low to High)' },
  { value: 'quantity_desc', label: 'Quantity (High to Low)' },
];

const columns = [
  { key: 'code', label: 'Code', sortable: true },
  { key: 'name', label: 'Name', sortable: true },
  { key: 'category', label: 'Category', sortable: false },
  { key: 'quantity', label: 'Quantity', sortable: true },
  { key: 'unit', label: 'Unit', sortable: false },
  { key: 'location', label: 'Location', sortable: false },
];

onMounted(() => {
  fetchItems();
});

const fetchItems = async () => {
  loading.value = true;
  try {
    items.value = [
      { id: 1, code: 'ITM001', name: 'Laptop Dell XPS 15', category: 'Electronics', quantity: 45, unit: 'pcs', location: 'Warehouse A' },
      { id: 2, code: 'ITM002', name: 'Mouse Wireless Logitech', category: 'Electronics', quantity: 120, unit: 'pcs', location: 'Warehouse A' },
      { id: 3, code: 'ITM003', name: 'Keyboard Mechanical', category: 'Electronics', quantity: 85, unit: 'pcs', location: 'Warehouse B' },
      { id: 4, code: 'ITM004', name: 'Monitor 27 inch', category: 'Electronics', quantity: 32, unit: 'pcs', location: 'Warehouse A' },
      { id: 5, code: 'ITM005', name: 'USB Cable Type-C', category: 'Accessories', quantity: 250, unit: 'pcs', location: 'Warehouse C' },
    ];
    total.value = items.value.length;
  } catch (error) {
    console.error('Failed to fetch items:', error);
  } finally {
    loading.value = false;
  }
};

const handleSearch = () => {
  currentPage.value = 1;
  fetchItems();
};

const handleClear = () => {
  searchQuery.value = '';
  sortValue.value = '';
  currentPage.value = 1;
  fetchItems();
};

const handleSort = (columnKey) => {
  console.log('Sort by:', columnKey);
  fetchItems();
};

const handlePageChange = (page) => {
  currentPage.value = page;
  fetchItems();
};

const handleRowClick = (row) => {
  router.push(`/items/${row.id}/edit`);
};

const navigateToCreate = () => {
  router.push('/items/create');
};

const handleEdit = (row) => {
  router.push(`/items/${row.id}/edit`);
};

const handleDelete = (row) => {
  if (confirm(`Are you sure you want to delete ${row.name}?`)) {
    console.log('Delete item:', row.id);
    fetchItems();
  }
};
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Items</h1>
        <p class="mt-1 text-sm text-gray-600">Manage inventory items and stock levels</p>
      </div>
      <BaseButton @click="navigateToCreate" variant="primary">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Item
      </BaseButton>
    </div>

    <BaseCard :padding="false" :shadow="true">
      <div class="p-6">
        <SearchFilterBar
          v-model:search-query="searchQuery"
          v-model:sort-value="sortValue"
          :sort-options="sortOptions"
          placeholder="Search items by name or code..."
          @search="handleSearch"
          @clear="handleClear"
        />
      </div>

      <BaseTable
        :columns="columns"
        :data="items"
        :loading="loading"
        :sortable="true"
        @sort="handleSort"
        @row-click="handleRowClick"
      >
        <template #cell-quantity="{ value, row }">
          <span
            :class="{
              'text-red-600 font-semibold': value < 20,
              'text-yellow-600 font-semibold': value >= 20 && value < 50,
              'text-green-600': value >= 50,
            }"
          >
            {{ value }}
          </span>
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
