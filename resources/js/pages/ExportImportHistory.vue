<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useToast } from '@/composables/useToast';
import BaseCard from '@/components/ui/BaseCard.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import BaseBadge from '@/components/ui/BaseBadge.vue';
import BasePagination from '@/components/ui/BasePagination.vue';

const { showToast } = useToast();

const jobs = ref([]);
const loading = ref(false);
const currentPage = ref(1);
const lastPage = ref(1);
const total = ref(0);
const pollingInterval = ref(null);

const fetchJobs = async () => {
  loading.value = true;
  try {
    const response = await fetch(`/api/export-import-jobs?page=${currentPage.value}`, {
      credentials: 'include',
    });

    if (!response.ok) {
      throw new Error('Gagal memuat data');
    }

    const data = await response.json();
    jobs.value = data.data;
    currentPage.value = data.current_page;
    lastPage.value = data.last_page;
    total.value = data.total;
  } catch (error) {
    showToast(error.message, 'error');
  } finally {
    loading.value = false;
  }
};

const handlePageChange = (page) => {
  currentPage.value = page;
  fetchJobs();
};

const hasProcessingJobs = computed(() => {
  return jobs.value.some(job => ['pending', 'processing'].includes(job.status));
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
    const response = await fetch(`/api/export-import-jobs/${job.id}/download`, {
      credentials: 'include',
    });

    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message || 'Download gagal');
    }

    const blob = await response.blob();
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = job.file_path.split('/').pop();
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);
  } catch (error) {
    showToast(error.message, 'error');
  }
};

const getStatusBadge = (status) => {
  const variants = {
    pending: 'warning',
    processing: 'info',
    completed: 'success',
    failed: 'danger',
  };
  return variants[status] || 'default';
};

const getStatusLabel = (status) => {
  const labels = {
    pending: 'Menunggu',
    processing: 'Memproses',
    completed: 'Selesai',
    failed: 'Gagal',
  };
  return labels[status] || status;
};

const getTypeLabel = (type) => {
  return type === 'export' ? 'Export' : 'Import';
};

const getModelLabel = (model) => {
  return model === 'role' ? 'Role' : 'User';
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
        <h1 class="text-2xl font-bold text-slate-900">Riwayat Export/Import</h1>
        <p class="text-slate-600 mt-1">Pantau status proses export dan import data</p>
      </div>
    </div>

    <BaseCard>
      <div v-if="loading && jobs.length === 0" class="text-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
        <p class="text-slate-600 mt-4">Memuat data...</p>
      </div>

      <div v-else-if="jobs.length === 0" class="text-center py-12">
        <div class="text-slate-400 mb-4">
          <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>
        <p class="text-slate-600">Belum ada riwayat export/import</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
          <thead class="bg-slate-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Tipe</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Model</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Progress</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Tanggal</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-slate-200">
            <tr v-for="job in jobs" :key="job.id" class="hover:bg-slate-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="text-sm font-medium text-slate-900">{{ getTypeLabel(job.type) }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="text-sm text-slate-600">{{ getModelLabel(job.model) }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <BaseBadge :variant="getStatusBadge(job.status)">
                  {{ getStatusLabel(job.status) }}
                </BaseBadge>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-slate-600">
                  <template v-if="job.total_rows">
                    {{ job.processed_rows || 0 }} / {{ job.total_rows }}
                    <span v-if="job.failed_rows > 0" class="text-red-600 ml-1">
                      ({{ job.failed_rows }} gagal)
                    </span>
                  </template>
                  <template v-else>-</template>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="text-sm text-slate-600">{{ new Date(job.created_at).toLocaleString('id-ID') }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <div class="flex gap-2">
                  <BaseButton
                    v-if="job.type === 'export' && job.status === 'completed'"
                    @click="handleDownload(job)"
                    size="sm"
                    variant="secondary"
                  >
                    Download
                  </BaseButton>
                  <span v-if="job.status === 'failed' && job.error_log" class="text-red-600 text-xs">
                    Error
                  </span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-if="lastPage > 1" class="mt-4">
          <BasePagination
            :current-page="currentPage"
            :last-page="lastPage"
            :total="total"
            @page-change="handlePageChange"
          />
        </div>
      </div>
    </BaseCard>
  </div>
</template>
