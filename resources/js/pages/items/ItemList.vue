<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { showConfirm } from "@/lib/swal";
import { Plus, Pencil, Trash2 } from "lucide-vue-next";
import BaseCard from "@/components/ui/BaseCard.vue";
import DataTable from "@/components/ui/DataTable.vue";
import BaseButton from "@/components/ui/BaseButton.vue";
import BasePagination from "@/components/ui/BasePagination.vue";
import SearchFilterBar from "@/components/ui/SearchFilterBar.vue";

const router = useRouter();

const items = ref([]);
const loading = ref(false);
const searchQuery = ref("");
const sortValue = ref("");
const currentPage = ref(1);
const perPage = ref(10);
const total = ref(0);

const sortOptions = [
    { value: "name_asc", label: "Name (A-Z)" },
    { value: "name_desc", label: "Name (Z-A)" },
    { value: "quantity_asc", label: "Quantity (Low to High)" },
    { value: "quantity_desc", label: "Quantity (High to Low)" },
];

const columns = [
    { key: "code", label: "Code", sortable: true },
    { key: "name", label: "Name", sortable: true },
    { key: "category", label: "Category", sortable: false },
    { key: "quantity", label: "Quantity", sortable: true },
    { key: "unit", label: "Unit", sortable: false },
    { key: "location", label: "Location", sortable: false },
];

onMounted(() => {
    fetchItems();
});

const fetchItems = async () => {
    loading.value = true;
    try {
        items.value = [
            {
                id: 1,
                code: "ITM001",
                name: "Laptop Dell XPS 15",
                category: "Electronics",
                quantity: 45,
                unit: "pcs",
                location: "Warehouse A",
            },
            {
                id: 2,
                code: "ITM002",
                name: "Mouse Wireless Logitech",
                category: "Electronics",
                quantity: 120,
                unit: "pcs",
                location: "Warehouse A",
            },
            {
                id: 3,
                code: "ITM003",
                name: "Keyboard Mechanical",
                category: "Electronics",
                quantity: 85,
                unit: "pcs",
                location: "Warehouse B",
            },
            {
                id: 4,
                code: "ITM004",
                name: "Monitor 27 inch",
                category: "Electronics",
                quantity: 32,
                unit: "pcs",
                location: "Warehouse A",
            },
            {
                id: 5,
                code: "ITM005",
                name: "USB Cable Type-C",
                category: "Accessories",
                quantity: 250,
                unit: "pcs",
                location: "Warehouse C",
            },
        ];
        total.value = items.value.length;
    } catch (error) {
        console.error("Failed to fetch items:", error);
    } finally {
        loading.value = false;
    }
};

const handleSearch = () => {
    currentPage.value = 1;
    fetchItems();
};

const handleClear = () => {
    searchQuery.value = "";
    sortValue.value = "";
    currentPage.value = 1;
    fetchItems();
};

const handleSort = (columnKey) => {
    console.log("Sort by:", columnKey);
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
    router.push("/items/create");
};

const handleEdit = (row) => {
    router.push(`/items/${row.id}/edit`);
};

const handleDelete = async (row) => {
    const confirmed = await showConfirm(
        `Are you sure you want to delete <strong>${row.name}</strong>?`,
        'Delete Item'
    );
    
    if (confirmed) {
        console.log("Delete item:", row.id);
        fetchItems();
    }
};
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                    Items
                </h1>
                <p class="mt-1 text-sm text-slate-600">
                    Manage inventory items and stock levels
                </p>
            </div>
            <BaseButton @click="navigateToCreate">
                <Plus :size="18" />
                Add Item
            </BaseButton>
        </div>

        <BaseCard>
            <SearchFilterBar
                v-model:search-query="searchQuery"
                v-model:sort-value="sortValue"
                :sort-options="sortOptions"
                placeholder="Search items by name or code..."
                @search="handleSearch"
                @clear="handleClear"
            />
        </BaseCard>

        <DataTable
            :columns="columns"
            :data="items"
            :loading="loading"
            empty-message="No items found"
            @sort="handleSort"
            @row-click="handleRowClick"
        >
            <template #cell-quantity="{ value }">
                <span
                    :class="{
                        'text-danger-600 font-semibold': value < 20,
                        'text-warning-600 font-semibold':
                            value >= 20 && value < 50,
                        'text-success-600': value >= 50,
                    }"
                >
                    {{ value }}
                </span>
            </template>

            <template #actions="{ row }">
                <div class="flex items-center gap-1 justify-end">
                    <button
                        @click.stop="handleEdit(row)"
                        class="p-2 text-slate-600 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors"
                        title="Edit Item"
                    >
                        <Pencil :size="16" />
                    </button>
                    <button
                        @click.stop="handleDelete(row)"
                        class="p-2 text-slate-600 hover:text-danger-600 hover:bg-danger-50 rounded-lg transition-colors"
                        title="Delete Item"
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
