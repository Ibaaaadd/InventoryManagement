<script setup>
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import { showConfirm } from "@/lib/swal";
import { Plus, Pencil, Trash2 } from "lucide-vue-next";
import BaseCard from "@/components/ui/BaseCard.vue";
import DataTable from "@/components/ui/DataTable.vue";
import BaseButton from "@/components/ui/BaseButton.vue";
import SearchFilterBar from "@/components/ui/SearchFilterBar.vue";

const router = useRouter();

const items = ref([]);
const loading = ref(false);
const searchQuery = ref("");
const sortValue = ref("");
const currentPage = ref(1);
const perPage = ref(10);
const total = ref(0);

const lastPage = computed(() => Math.ceil(total.value / perPage.value) || 1);

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
        // Mock data with more than 10 items to test pagination
        const allItems = [
            { id: 1, code: "ITM001", name: "Laptop Dell XPS 15", category: "Electronics", quantity: 45, unit: "pcs", location: "Warehouse A" },
            { id: 2, code: "ITM002", name: "Mouse Wireless Logitech", category: "Electronics", quantity: 120, unit: "pcs", location: "Warehouse A" },
            { id: 3, code: "ITM003", name: "Keyboard Mechanical", category: "Electronics", quantity: 85, unit: "pcs", location: "Warehouse B" },
            { id: 4, code: "ITM004", name: "Monitor 27 inch", category: "Electronics", quantity: 32, unit: "pcs", location: "Warehouse A" },
            { id: 5, code: "ITM005", name: "USB Cable Type-C", category: "Accessories", quantity: 250, unit: "pcs", location: "Warehouse C" },
            { id: 6, code: "ITM006", name: "HDMI Cable 2m", category: "Accessories", quantity: 180, unit: "pcs", location: "Warehouse B" },
            { id: 7, code: "ITM007", name: "Webcam Logitech C920", category: "Electronics", quantity: 25, unit: "pcs", location: "Warehouse C" },
            { id: 8, code: "ITM008", name: "Headset Razer Kraken", category: "Electronics", quantity: 40, unit: "pcs", location: "Warehouse A" },
            { id: 9, code: "ITM009", name: "SSD Samsung 1TB", category: "Storage", quantity: 60, unit: "pcs", location: "Warehouse B" },
            { id: 10, code: "ITM010", name: "RAM DDR4 16GB", category: "Components", quantity: 35, unit: "pcs", location: "Warehouse C" },
            { id: 11, code: "ITM011", name: "GPU RTX 4080", category: "Components", quantity: 5, unit: "pcs", location: "Warehouse A" },
            { id: 12, code: "ITM012", name: "CPU Intel i9-13900K", category: "Components", quantity: 8, unit: "pcs", location: "Warehouse B" },
            { id: 13, code: "ITM013", name: "Motherboard Z790", category: "Components", quantity: 12, unit: "pcs", location: "Warehouse C" },
            { id: 14, code: "ITM014", name: "PSU 850W Gold", category: "Components", quantity: 20, unit: "pcs", location: "Warehouse A" },
            { id: 15, code: "ITM015", name: "Case ATX Mid Tower", category: "Components", quantity: 15, unit: "pcs", location: "Warehouse B" },
        ];
        
        // Apply client-side search filter
        let filteredItems = allItems;
        if (searchQuery.value) {
            const q = searchQuery.value.toLowerCase();
            filteredItems = allItems.filter(item => 
                item.name.toLowerCase().includes(q) || 
                item.code.toLowerCase().includes(q)
            );
        }
        
        total.value = filteredItems.length;
        
        // Apply client-side pagination
        const start = (currentPage.value - 1) * perPage.value;
        items.value = filteredItems.slice(start, start + perPage.value);
        
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
            :show-pagination="true"
            :current-page="currentPage"
            :last-page="lastPage"
            :total="total"
            @sort="handleSort"
            @row-click="handleRowClick"
            @page-change="handlePageChange"
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
        </DataTable>
    </div>
</template>
