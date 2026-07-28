<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import axios from "@/lib/axios";
import { showError } from "@/lib/swal";
import { useDateFormat } from "@/composables/useDateFormat";
import { useToast } from "@/composables/useToast";
import {
    Search,
    Plus,
    Pencil,
    Trash2,
    Download,
    Upload,
    RefreshCw,
} from "lucide-vue-next";
import BaseButton from "@/components/ui/BaseButton.vue";
import BaseCard from "@/components/ui/BaseCard.vue";
import DataTable from "@/components/ui/DataTable.vue";
import BaseBadge from "@/components/ui/BaseBadge.vue";
import BaseModal from "@/components/ui/BaseModal.vue";
import ExportModal from "@/components/ui/ExportModal.vue";
import ImportModal from "@/components/ui/ImportModal.vue";

const router = useRouter();
const { formatDate } = useDateFormat();
const { toastSuccess } = useToast();

const categories = ref([]);
const loading = ref(false);
const searchQuery = ref("");
const searchDebounceTimer = ref(null);
const sortField = ref("");
const sortDirection = ref("");
const showDeleteModal = ref(false);
const categoryToDelete = ref(null);
const showExportModal = ref(false);
const showImportModal = ref(false);

const currentPage = ref(1);
const perPage = ref(10);
const lastPage = ref(1);
const total = ref(0);

const fetchCategories = async () => {
    try {
        loading.value = true;
        const params = { page: currentPage.value, per_page: perPage.value };
        if (searchQuery.value) {
            params.search = searchQuery.value;
        }
        if (sortField.value && sortDirection.value) {
            params.sort = sortField.value;
            params.order = sortDirection.value;
        }
        const response = await axios.get("/categories", { params });

        if (response.data && response.data.data) {
            categories.value = response.data.data;
            currentPage.value = response.data.current_page || 1;
            lastPage.value = response.data.last_page || 1;
            total.value = response.data.total || 0;
        } else {
            categories.value = response.data;
            currentPage.value = 1;
            lastPage.value = 1;
            total.value = categories.value.length;
        }
    } catch (error) {
        console.error("Failed to fetch categories:", error);
    } finally {
        loading.value = false;
    }
};

const handlePageChange = (page) => {
    currentPage.value = page;
    fetchCategories();
};

const columns = [
    { key: "name", label: "Nama Kategori", sortable: true },
    { key: "code", label: "Kode", sortable: true },
    { key: "items_count", label: "Jumlah Item", sortable: true },
    { key: "created_at", label: "Tanggal Dibuat", sortable: true },
];

const filteredCategories = computed(() => categories.value);

const handleEdit = (category) => {
    router.push({ name: "CategoryEdit", params: { id: category.id } });
};

const openDeleteModal = (category) => {
    categoryToDelete.value = category;
    showDeleteModal.value = true;
};

const confirmDelete = async () => {
    if (!categoryToDelete.value) return;

    try {
        loading.value = true;
        await axios.delete(`/categories/${categoryToDelete.value.id}`);

        toastSuccess("Kategori Dihapus", "Kategori berhasil dihapus");

        showDeleteModal.value = false;
        categoryToDelete.value = null;

        fetchCategories();
    } catch (error) {
        console.error("Failed to delete category:", error);
        showError(
            error.response?.data?.message ||
                "Gagal menghapus kategori. Silakan coba lagi.",
        );
    } finally {
        loading.value = false;
    }
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    categoryToDelete.value = null;
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
    fetchCategories();
    router.push({ name: "ExportImportHistory" });
};

const handleSearchInput = () => {
    if (searchDebounceTimer.value) {
        clearTimeout(searchDebounceTimer.value);
    }
    
    searchDebounceTimer.value = setTimeout(() => {
        currentPage.value = 1;
        fetchCategories();
    }, 500);
};

const handleSort = (sortData) => {
    sortField.value = sortData.key;
    sortDirection.value = sortData.direction;
    currentPage.value = 1;
    fetchCategories();
};

const handleRefresh = () => {
    fetchCategories();
};

const canDeleteCategory = (category) => {
    return category.items_count === 0;
};

onMounted(() => {
    fetchCategories();
});
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                    Manajemen Kategori
                </h1>
                <p class="text-sm text-slate-600 mt-1">
                    Kelola kategori untuk item inventori
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
                <BaseButton @click="router.push({ name: 'CategoryCreate' })">
                    <Plus :size="18" />
                    Tambah Kategori
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
                        placeholder="Cari kategori..."
                        class="w-full pl-10 pr-4 py-2.5 text-sm border focus:outline-none border-slate-300 rounded-lg focus:ring-1/2 focus:ring-primary-500 focus:border-primary-500 transition-all"
                    />
                </div>
                <button
                    @click="handleRefresh"
                    :disabled="loading"
                    class="p-2.5 text-slate-600 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                    title="Refresh"
                >
                    <RefreshCw :size="20" :class="{ 'animate-spin': loading }" />
                </button>
            </div>
        </BaseCard>

        <DataTable
            :columns="columns"
            :data="filteredCategories"
            :loading="loading"
            empty-message="Tidak ada kategori ditemukan"
            :show-pagination="true"
            :current-page="currentPage"
            :last-page="lastPage"
            :total="total"
            @sort="handleSort"
            @page-change="handlePageChange"
        >
            <template #cell-name="{ row }">
                <div class="font-medium text-slate-900">{{ row.name }}</div>
            </template>

            <template #cell-code="{ row }">
                <BaseBadge variant="secondary" size="sm">
                    {{ row.code }}
                </BaseBadge>
            </template>

            <template #cell-items_count="{ row }">
                <BaseBadge variant="info" size="sm">
                    {{ row.items_count }}
                    {{ row.items_count === 1 ? "item" : "items" }}
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
                        title="Edit Kategori"
                    >
                        <Pencil :size="16" />
                    </button>
                    <button
                        @click="openDeleteModal(row)"
                        :disabled="!canDeleteCategory(row)"
                        :class="[
                            'p-2 rounded-lg transition-colors',
                            canDeleteCategory(row)
                                ? 'text-slate-600 hover:text-danger-600 hover:bg-danger-50'
                                : 'text-slate-300 cursor-not-allowed'
                        ]"
                        :title="canDeleteCategory(row) ? 'Hapus Kategori' : 'Tidak bisa dihapus karena masih digunakan'"
                    >
                        <Trash2 :size="16" />
                    </button>
                </div>
            </template>
        </DataTable>

        <BaseModal v-model="showDeleteModal" title="Hapus Kategori" size="sm">
            <div class="space-y-4">
                <p class="text-sm text-slate-600">
                    Apakah Anda yakin ingin menghapus kategori
                    <strong>{{ categoryToDelete?.name }}</strong
                    >? Aksi ini tidak bisa dibatalkan.
                </p>
                <div
                    v-if="categoryToDelete?.items_count > 0"
                    class="p-3 bg-danger-50 border border-danger-200 rounded-lg"
                >
                    <p class="text-sm text-danger-800">
                        <strong>Perhatian:</strong> Kategori ini masih digunakan oleh
                        {{ categoryToDelete.items_count }} item. Anda tidak bisa menghapus kategori ini.
                    </p>
                </div>
            </div>

            <template #footer>
                <div class="flex items-center gap-2 justify-end">
                    <BaseButton
                        @click="cancelDelete"
                        variant="ghost"
                        :disabled="loading"
                    >
                        Batal
                    </BaseButton>
                    <BaseButton
                        @click="confirmDelete"
                        variant="danger"
                        :loading="loading"
                        :disabled="loading || categoryToDelete?.items_count > 0"
                    >
                        Hapus Kategori
                    </BaseButton>
                </div>
            </template>
        </BaseModal>

        <ExportModal
            v-model="showExportModal"
            model="category"
            @exported="handleExported"
        />

        <ImportModal
            v-model="showImportModal"
            model="category"
            @imported="handleImported"
        />
    </div>
</template>
