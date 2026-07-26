<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from '@/lib/axios';
import { useDateFormat } from '@/composables/useDateFormat';
import { PlusCircle, Pencil, CheckCircle2, XCircle, ArrowRight, History } from 'lucide-vue-next';
import BasePagination from '@/components/ui/BasePagination.vue';

const props = defineProps({
  auditableType: {
    type: String,
    required: true,
  },
  auditableId: {
    type: [String, Number],
    required: true,
  },
  page: {
    type: Number,
    default: 1,
  },
  perPage: {
    type: Number,
    default: 5,
  },
});

const { formatDateTime, formatRelative } = useDateFormat();

const auditLogs = ref([]);
const loading = ref(false);
const currentPage = ref(props.page);
const total = ref(0);

const totalPages = computed(() => Math.ceil(total.value / props.perPage));

const fetchAudits = async () => {
  console.log('[AuditTrailPanel] Fetching audits...', {
    auditableType: props.auditableType,
    auditableId: props.auditableId,
    page: currentPage.value,
    perPage: props.perPage
  });
  
  loading.value = true;
  try {
    const response = await axios.get('/audits', {
      params: {
        auditable_type: props.auditableType,
        auditable_id: props.auditableId,
        page: currentPage.value,
        per_page: props.perPage,
      },
    });
    
    console.log('[AuditTrailPanel] Response received:', response.data);
    
    auditLogs.value = response.data.data || [];
    total.value = response.data.total || 0;
    
    console.log('[AuditTrailPanel] Audits loaded:', auditLogs.value.length, 'Total:', total.value);
  } catch (error) {
    console.error('[AuditTrailPanel] Failed to fetch audit logs:', error);
    console.error('[AuditTrailPanel] Error response:', error.response);
    auditLogs.value = [];
    total.value = 0;
  } finally {
    loading.value = false;
    console.log('[AuditTrailPanel] Loading finished');
  }
};

onMounted(() => {
  fetchAudits();
});

const handlePageChange = (page) => {
  currentPage.value = page;
  fetchAudits();
};

const getEventIcon = (event) => {
  const icons = {
    created: PlusCircle,
    updated: Pencil,
    deleted: XCircle,
    approved: CheckCircle2,
    rejected: XCircle,
  };
  return icons[event.toLowerCase()] || Pencil;
};

const getEventColor = (event) => {
  const colors = {
    created: 'text-success-600 bg-success-100',
    updated: 'text-primary-600 bg-primary-100',
    deleted: 'text-danger-600 bg-danger-100',
    approved: 'text-success-600 bg-success-100',
    rejected: 'text-danger-600 bg-danger-100',
  };
  return colors[event.toLowerCase()] || 'text-slate-600 bg-slate-100';
};

const getEventLabel = (event) => {
  const labels = {
    created: 'Dibuat',
    updated: 'Diperbarui',
    deleted: 'Dihapus',
    approved: 'Disetujui',
    rejected: 'Ditolak',
  };
  return labels[event.toLowerCase()] || event;
};

const getFieldLabel = (field) => {
  const labels = {
    name: 'Nama',
    email: 'Email',
    role_id: 'Role',
    status: 'Status',
    is_active: 'Status Aktif',
    description: 'Deskripsi',
    quantity: 'Jumlah',
    price: 'Harga',
    sku: 'SKU',
    category: 'Kategori',
  };
  return labels[field] || field;
};

const getUserInitials = (name) => {
  if (!name) return '?';
  const parts = name.trim().split(' ');
  if (parts.length >= 2) {
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
  }
  return name.substring(0, 2).toUpperCase();
};

const parseChanges = (oldValues, newValues) => {
  const changes = [];
  const old = typeof oldValues === 'string' ? JSON.parse(oldValues) : (oldValues || {});
  const newVals = typeof newValues === 'string' ? JSON.parse(newValues) : (newValues || {});
  
  const excludeFields = ['id', 'created_at', 'updated_at', 'deleted_at'];
  const allKeys = new Set([...Object.keys(old), ...Object.keys(newVals)]);
  
  allKeys.forEach(key => {
    if (excludeFields.includes(key)) return;
    
    const oldVal = old[key];
    const newVal = newVals[key];
    
    if (oldVal !== newVal) {
      changes.push({
        field: key,
        label: getFieldLabel(key),
        oldValue: oldVal ?? '-',
        newValue: newVal ?? '-',
      });
    }
  });
  
  return changes;
};
</script>

<template>
  <div class="rounded-lg border border-slate-200 bg-white">
    <div class="p-6 border-b border-slate-200">
      <h3 class="text-lg font-semibold text-slate-900">Riwayat Perubahan</h3>
      <p class="text-sm text-slate-600 mt-1">Audit trail perubahan data</p>
    </div>

    <div class="p-6">
      <div v-if="loading" class="space-y-4">
        <div v-for="i in 3" :key="i" class="animate-pulse flex gap-4">
          <div class="w-10 h-10 bg-slate-200 rounded-full flex-shrink-0"></div>
          <div class="flex-1 space-y-2">
            <div class="h-4 bg-slate-200 rounded w-3/4"></div>
            <div class="h-3 bg-slate-200 rounded w-1/2"></div>
          </div>
        </div>
      </div>

      <div v-else-if="auditLogs.length === 0" class="text-center py-12">
        <History :size="48" class="mx-auto text-slate-300 mb-3" />
        <p class="text-sm text-slate-500">Belum ada riwayat perubahan</p>
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="(audit, index) in auditLogs"
          :key="audit.id"
          class="relative border border-slate-200 rounded-lg p-6 hover:border-slate-300 hover:shadow-sm transition-all duration-200"
        >
          <div class="flex gap-4">
            <div class="flex-shrink-0 relative">
              <div
                class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold bg-primary-100 text-primary-700"
                :title="audit.user?.name || 'System'"
              >
                {{ getUserInitials(audit.user?.name || 'System') }}
              </div>
              <div
                class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full flex items-center justify-center ring-2 ring-white"
                :class="getEventColor(audit.event)"
              >
                <component :is="getEventIcon(audit.event)" :size="14" />
              </div>
            </div>

            <div class="flex-1 min-w-0">
              <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 mb-3">
                <div>
                  <p class="text-sm text-slate-900">
                    <span class="font-semibold">{{ audit.user?.name || 'System' }}</span>
                    <span class="text-slate-600 ml-1">{{ getEventLabel(audit.event).toLowerCase() }}</span>
                  </p>
                  <p class="text-xs text-slate-500 mt-1">
                    {{ formatDateTime(audit.created_at) }}
                    <span class="mx-1">•</span>
                    <span class="italic">{{ formatRelative(audit.created_at) }}</span>
                  </p>
                </div>
              </div>

              <div v-if="audit.event === 'created'" class="text-sm text-slate-600 italic">
                Data dibuat pertama kali
              </div>

              <div v-else-if="audit.old_values && audit.new_values" class="space-y-2">
                <div v-if="parseChanges(audit.old_values, audit.new_values).length > 0" class="border-t border-slate-100 pt-3">
                  <div
                    v-for="change in parseChanges(audit.old_values, audit.new_values)"
                    :key="change.field"
                    class="bg-slate-50 rounded-md px-3 py-2.5 text-sm mb-2 last:mb-0"
                  >
                    <p class="font-medium text-slate-700 mb-1.5 text-xs uppercase tracking-wide">{{ change.label }}</p>
                    <div class="flex items-center gap-2">
                      <span class="text-slate-500 line-through text-sm">{{ change.oldValue }}</span>
                      <ArrowRight :size="14" class="text-slate-400 flex-shrink-0" />
                      <span class="text-slate-900 font-medium text-sm">{{ change.newValue }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="!loading && auditLogs.length > 0 && totalPages > 1" class="mt-6 pt-6 border-t border-slate-200">
        <BasePagination
          :current-page="currentPage"
          :total-pages="totalPages"
          :per-page="perPage"
          :total="total"
          @page-change="handlePageChange"
        />
      </div>
    </div>
  </div>
</template>
