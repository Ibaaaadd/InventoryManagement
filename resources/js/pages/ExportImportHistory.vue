<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";
import { useToast } from "@/composables/useToast";
import { Download, AlertCircle, RefreshCw, AlertTriangle, Eye } from "lucide-vue-next";
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
const lastPage = ref(1);
const total = ref(0);
const pollingInterval = ref(null);

const errorLogModal = ref(false);
const selectedJob = ref(null);

const columns = [
    { key: "type", label: "Tipe" },
    { key: "model", label: "Model" },
    { key: "status", label: "Status" },
    { key: "progress", label: "Progress", sortable: false },
    { key: "created_at", label: "Tanggal" },
];

const fetchJobs = async () => {
    loading.value = true;
    try {
        const response = await fetch(
            `/api/export-import-jobs?page=${currentPage.value}`,
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
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">
                    Riwayat Export/Import
                </h1>
                <p class="text-slate-600 mt-1">
                    Pantau status proses export dan import data
                </p>
            </div>
            <BaseButton
                @click="handleRefresh"
                variant="secondary"
                :disabled="loading"
                class="flex items-center gap-2"
            >
                <RefreshCw :size="16" :class="{ 'animate-spin': loading }" />
                Refresh
            </BaseButton>
        </div>

        <DataTable
            :columns="columns"
            :data="jobs"
            :loading="loading"
            empty-message="Belum ada riwayat export/import"
            :show-pagination="true"
            :current-page="currentPage"
            :last-page="lastPage"
            :total="total"
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
