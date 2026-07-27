<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from "vue";
import { useToast } from "@/composables/useToast";
import { Download, AlertCircle, RefreshCw, AlertTriangle, Eye, Filter, X } from "lucide-vue-next";
import BaseCard from "@/components/ui/BaseCard.vue";
import BaseButton from "@/components/ui/BaseButton.vue";
import BaseBadge from "@/components/ui/BaseBadge.vue";
import DataTable from "@/components/ui/DataTable.vue";
import ErrorLogModal from "@/components/ui/ErrorLogModal.vue";
import { useDateFormat } from "@/composables/useDateFormat";

const { showToast } = useToast();
const { formatDate } = useDateFormat();

const jobs = ref([]);
const loading = ref(false);
const currentPage = ref(1);
const perPage = ref(10);
const lastPage = ref(1);
const total = ref(0);
const pollingInterval = ref(null);
const sortField = ref("");
const sortDirection = ref("");

const errorLogModal = ref(false);
const selectedJob = ref(null);

const filterType = ref('');
const filterModel = ref('');

const columns = [
    { key: "type", label: "Tipe", sortable: true },
    { key: "model", label: "Model", sortable: true },
    { key: "status", label: "Status", sortable: true },
    { key: "progress", label: "Progress", sortable: false },
    { key: "created_at", label: "Tanggal", sortable: true },
];

const fetchJobs = async () => {
    loading.value = true;
    try {
        const params = new URLSearchParams({
            page: currentPage.value,
            per_page: perPage.value,
        });
        if (filterType.value) params.append('type', filterType.value);
        if (filterModel.value) params.append('model', filterModel.value);
        if (sortField.value && sortDirection.value) {
            params.append('sort', sortField.value);
            params.append('order', sortDirection.value);
        }

        const response = await fetch(
            `/api/export-import-jobs?${params.toString()}`,
            {
                credentials: "include",
            },
        );

        if (!response.ok) {
            throw new Error("Gagal memuat data");
        }

        const data = await response.json();
        jobs.value = data.data;
        currentPage.value = data.current_page;
        lastPage.value = data.last_page;
        total.value = data.total;
    } catch (error) {
        showToast(error.message, "error");
    } finally {
        loading.value = false;
    }
};

const handleRefresh = () => {
    currentPage.value = 1;
    fetchJobs();
};

const handlePageChange = (page) => {
    currentPage.value = page;
    fetchJobs();
};

const handleFilterChange = () => {
    currentPage.value = 1;
    fetchJobs();
};

const handleSort = (sortData) => {
    sortField.value = sortData.key;
    sortDirection.value = sortData.direction;
    currentPage.value = 1;
    fetchJobs();
};

const clearFilters = () => {
    filterType.value = '';
    filterModel.value = '';
    currentPage.value = 1;
    fetchJobs();
};

const availableModels = computed(() => {
    const models = [...new Set(jobs.value.map(job => job.model).filter(Boolean))];
    return models.sort();
});

const hasProcessingJobs = computed(() => {
    return jobs.value.some((job) =>
        ["pending", "processing"].includes(job.status),
    );
});

const startPolling = () => {
    if (hasProcessingJobs.value) {
        pollingInterval.value = setInterval(() => {
            fetchJobs();
        }, 5000);
    }
};

const stopPolling = () => {
    if (pollingInterval.value) {
        clearInterval(pollingInterval.value);
        pollingInterval.value = null;
    }
};

const handleDownload = async (job) => {
    try {
        const response = await fetch(
            `/api/export-import-jobs/${job.id}/download`,
            {
                credentials: "include",
            },
        );

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message || "Download gagal");
        }

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = job.file_path.split("/").pop();
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
    } catch (error) {
        showToast(error.message, "error");
    }
};

const getStatusBadge = (status) => {
    const variants = {
        pending: "warning",
        processing: "info",
        completed: "success",
        failed: "danger",
    };
    return variants[status] || "default";
};

const getStatusLabel = (status) => {
    const labels = {
        pending: "Menunggu",
        processing: "Memproses",
        completed: "Selesai",
        failed: "Gagal",
    };
    return labels[status] || status;
};

const getTypeLabel = (type) => {
    return type === "export" ? "Export" : "Import";
};

const getModelLabel = (model) => {
    return model === "role" ? "Role" : "User";
};

const openErrorLog = (job) => {
    selectedJob.value = job;
    errorLogModal.value = true;
};

onMounted(() => {
    fetchJobs();
    startPolling();
});

onUnmounted(() => {
    stopPolling();
});
</script>

<template>
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">
                Riwayat Export/Import
            </h1>
            <p class="text-slate-600 mt-1">
                Pantau status proses export dan import data
            </p>
        </div>

        <BaseCard>
            <div class="flex items-center gap-3">
                <div class="flex-1 min-w-[200px]">
                    <select
                        v-model="filterType"
                        @change="handleFilterChange"
                        class="w-full pl-3 pr-9 py-2.5 text-sm border focus:outline-none border-slate-300 rounded-lg focus:ring-1/2 focus:ring-primary-500 focus:border-primary-500 transition-all bg-white appearance-none bg-[url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27%23666%27 stroke-width=%272%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3e%3cpolyline points=%276 9 12 15 18 9%27%3e%3c/polyline%3e%3c/svg%3e')] bg-[length:20px] bg-[center_right_0.75rem] bg-no-repeat"
                    >
                        <option value="">Semua Tipe</option>
                        <option value="export">Export</option>
                        <option value="import">Import</option>
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <select
                        v-model="filterModel"
                        @change="handleFilterChange"
                        class="w-full pl-3 pr-9 py-2.5 text-sm border focus:outline-none border-slate-300 rounded-lg focus:ring-1/2 focus:ring-primary-500 focus:border-primary-500 transition-all bg-white appearance-none bg-[url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27%23666%27 stroke-width=%272%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3e%3cpolyline points=%276 9 12 15 18 9%27%3e%3c/polyline%3e%3c/svg%3e')] bg-[length:20px] bg-[center_right_0.75rem] bg-no-repeat"
                    >
                        <option value="">Semua Model</option>
                        <option v-for="model in availableModels" :key="model" :value="model">
                            {{ model.charAt(0).toUpperCase() + model.slice(1) }}
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
                    v-if="filterType || filterModel"
                    @click="clearFilters"
                    class="px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors whitespace-nowrap"
                >
                    Clear
                </button>
            </div>
        </BaseCard>

        <DataTable
            :columns="columns"
            :data="jobs"
            :loading="loading"
            empty-message="Belum ada riwayat export/import"
            :show-pagination="true"
            :current-page="currentPage"
            :last-page="lastPage"
            :total="total"
            @sort="handleSort"
            @page-change="handlePageChange"
        >
            <template #cell-type="{ row }">
                <span class="font-medium text-slate-900">{{
                    getTypeLabel(row.type)
                }}</span>
            </template>

            <template #cell-model="{ row }">
                <span class="text-slate-600">{{
                    getModelLabel(row.model)
                }}</span>
            </template>

            <template #cell-status="{ row }">
                <BaseBadge :variant="getStatusBadge(row.status)">
                    {{ getStatusLabel(row.status) }}
                </BaseBadge>
            </template>

            <template #cell-progress="{ row }">
                <div class="text-slate-600">
                    <template v-if="row.total_rows">
                        {{ row.processed_rows || 0 }} / {{ row.total_rows }}
                        <span
                            v-if="row.failed_rows > 0"
                            class="text-red-600 ml-1"
                        >
                            ({{ row.failed_rows }} gagal)
                        </span>
                    </template>
                    <template v-else>-</template>
                </div>
            </template>

            <template #cell-created_at="{ row }">
                <span class="text-slate-600">{{
                    formatDate(row.created_at)
                }}</span>
            </template>

            <template #actions="{ row }">
                <div class="flex justify-end gap-2">
                    <button
                        v-if="
                            row.type === 'export' && row.status === 'completed'
                        "
                        @click.stop="handleDownload(row)"
                        class="p-2 text-primary-600 hover:bg-primary-50 rounded-lg transition-colors"
                        title="Download"
                    >
                        <Download :size="18" />
                    </button>
                    <button
                        v-if="row.error_log && (row.status === 'failed' || row.failed_rows > 0)"
                        @click.stop="openErrorLog(row)"
                        class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition-colors"
                        title="Lihat detail error"
                    >
                        <AlertTriangle :size="18" />
                    </button>
                    <button
                        v-else-if="row.error_log && row.status !== 'failed'"
                        @click.stop="openErrorLog(row)"
                        class="p-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors"
                        title="Lihat detail error"
                    >
                        <Eye :size="18" />
                    </button>
                </div>
            </template>
        </DataTable>

        <ErrorLogModal
            v-model="errorLogModal"
            :job="selectedJob"
        />
    </div>
</template>
