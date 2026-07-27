<script setup>
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import { Plus, Eye } from "lucide-vue-next";
import { useStatusBadge } from "@/composables/useStatusBadge";
import { useDateFormat } from "@/composables/useDateFormat";
import BaseCard from "@/components/ui/BaseCard.vue";
import DataTable from "@/components/ui/DataTable.vue";
import BaseButton from "@/components/ui/BaseButton.vue";
import BaseBadge from "@/components/ui/BaseBadge.vue";
import SearchFilterBar from "@/components/ui/SearchFilterBar.vue";

const router = useRouter();
const { getVariant } = useStatusBadge();
const { formatDate } = useDateFormat();

const mutations = ref([]);
const loading = ref(false);
const searchQuery = ref("");
const sortValue = ref("");
const currentPage = ref(1);
const perPage = ref(10);
const total = ref(0);

const lastPage = computed(() => Math.ceil(total.value / perPage.value) || 1);

const sortOptions = [
    { value: "date_desc", label: "Date (Newest First)" },
    { value: "date_asc", label: "Date (Oldest First)" },
    { value: "status_pending", label: "Status (Pending First)" },
    { value: "quantity_desc", label: "Quantity (High to Low)" },
];

const columns = [
    { key: "code", label: "Code", sortable: true },
    { key: "item_name", label: "Item", sortable: true },
    { key: "type", label: "Type", sortable: false },
    { key: "quantity", label: "Quantity", sortable: true },
    { key: "status", label: "Status", sortable: false },
    { key: "created_by", label: "Created By", sortable: false },
    { key: "date", label: "Date", sortable: true },
];

onMounted(() => {
    fetchMutations();
});

const fetchMutations = async () => {
    loading.value = true;
    try {
        // Mock data with more than 10 items to test pagination
        const allMutations = [
            { id: 1, code: "MUT001", item_name: "Laptop Dell XPS 15", type: "IN", quantity: 10, status: "pending", created_by: "John Doe", date: "2026-07-25" },
            { id: 2, code: "MUT002", item_name: "Mouse Wireless", type: "OUT", quantity: 5, status: "approved", created_by: "Jane Smith", date: "2026-07-25" },
            { id: 3, code: "MUT003", item_name: "Keyboard Mechanical", type: "IN", quantity: 8, status: "pending", created_by: "Mike Johnson", date: "2026-07-24" },
            { id: 4, code: "MUT004", item_name: "Monitor 27 inch", type: "OUT", quantity: 3, status: "rejected", created_by: "Sarah Williams", date: "2026-07-24" },
            { id: 5, code: "MUT005", item_name: "USB Cable Type-C", type: "IN", quantity: 50, status: "approved", created_by: "John Doe", date: "2026-07-23" },
            { id: 6, code: "MUT006", item_name: "HDMI Cable 2m", type: "OUT", quantity: 15, status: "approved", created_by: "Mike Johnson", date: "2026-07-23" },
            { id: 7, code: "MUT007", item_name: "Webcam Logitech C920", type: "IN", quantity: 20, status: "pending", created_by: "Sarah Williams", date: "2026-07-22" },
            { id: 8, code: "MUT008", item_name: "Headset Razer Kraken", type: "OUT", quantity: 7, status: "approved", created_by: "John Doe", date: "2026-07-22" },
            { id: 9, code: "MUT009", item_name: "SSD Samsung 1TB", type: "IN", quantity: 25, status: "approved", created_by: "Jane Smith", date: "2026-07-21" },
            { id: 10, code: "MUT010", item_name: "RAM DDR4 16GB", type: "OUT", quantity: 4, status: "rejected", created_by: "Mike Johnson", date: "2026-07-21" },
            { id: 11, code: "MUT011", item_name: "GPU RTX 4080", type: "IN", quantity: 3, status: "pending", created_by: "Sarah Williams", date: "2026-07-20" },
            { id: 12, code: "MUT012", item_name: "CPU Intel i9-13900K", type: "OUT", quantity: 2, status: "approved", created_by: "John Doe", date: "2026-07-20" },
            { id: 13, code: "MUT013", item_name: "Motherboard Z790", type: "IN", quantity: 10, status: "pending", created_by: "Jane Smith", date: "2026-07-19" },
            { id: 14, code: "MUT014", item_name: "PSU 850W Gold", type: "OUT", quantity: 5, status: "approved", created_by: "Mike Johnson", date: "2026-07-19" },
            { id: 15, code: "MUT015", item_name: "Case ATX Mid Tower", type: "IN", quantity: 12, status: "pending", created_by: "Sarah Williams", date: "2026-07-18" },
        ];
        
        // Apply client-side search filter
        let filteredMutations = allMutations;
        if (searchQuery.value) {
            const q = searchQuery.value.toLowerCase();
            filteredMutations = allMutations.filter(m => 
                m.code.toLowerCase().includes(q) || 
                m.item_name.toLowerCase().includes(q)
            );
        }
        
        total.value = filteredMutations.length;
        
        // Apply client-side pagination
        const start = (currentPage.value - 1) * perPage.value;
        mutations.value = filteredMutations.slice(start, start + perPage.value);
        
    } catch (error) {
        console.error("Failed to fetch mutations:", error);
    } finally {
        loading.value = false;
    }
};

const handleSearch = () => {
    currentPage.value = 1;
    fetchMutations();
};

const handleClear = () => {
    searchQuery.value = "";
    sortValue.value = "";
    currentPage.value = 1;
    fetchMutations();
};

const handleSort = (columnKey) => {
    console.log("Sort by:", columnKey);
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
    router.push("/stock-mutations/create");
};

const handleView = (row) => {
    router.push(`/stock-mutations/${row.id}`);
};

const getTypeVariant = (type) => {
    return type === "IN" ? "success" : "warning";
};
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                    Stock Mutations
                </h1>
                <p class="mt-1 text-sm text-slate-600">
                    Track all inventory movements and changes
                </p>
            </div>
            <BaseButton @click="navigateToCreate">
                <Plus :size="18" />
                New Mutation
            </BaseButton>
        </div>

        <BaseCard>
            <SearchFilterBar
                v-model:search-query="searchQuery"
                v-model:sort-value="sortValue"
                :sort-options="sortOptions"
                placeholder="Search mutations by code or item..."
                @search="handleSearch"
                @clear="handleClear"
            />
        </BaseCard>
        <DataTable
            :columns="columns"
            :data="mutations"
            :loading="loading"
            empty-message="No stock mutations found"
            :show-pagination="true"
            :current-page="currentPage"
            :last-page="lastPage"
            :total="total"
            @sort="handleSort"
            @row-click="handleRowClick"
            @page-change="handlePageChange"
        >
            <template #cell-type="{ value }">
                <BaseBadge :variant="getTypeVariant(value)">
                    {{ value }}
                </BaseBadge>
            </template>

            <template #cell-status="{ value }">
                <BaseBadge :variant="getVariant(value)">
                    {{ value }}
                </BaseBadge>
            </template>

            <template #cell-date="{ value }">
                {{ formatDate(value) }}
            </template>

            <template #cell-quantity="{ value, row }">
                <span
                    :class="{
                        'text-success-600 font-semibold': row.type === 'IN',
                        'text-danger-600 font-semibold': row.type === 'OUT',
                    }"
                >
                    {{ row.type === "IN" ? "+" : "-" }}{{ value }}
                </span>
            </template>

            <template #actions="{ row }">
                <button
                    @click.stop="handleView(row)"
                    class="p-2 text-slate-600 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors"
                    title="View Details"
                >
                    <Eye :size="16" />
                </button>
            </template>
        </DataTable>
    </div>
</template>
