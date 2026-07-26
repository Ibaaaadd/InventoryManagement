<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from '@/lib/axios';
import { Clock, User } from 'lucide-vue-next';

const props = defineProps({
  auditableType: {
    type: String,
    required: true,
  },
  auditableId: {
    type: [String, Number],
    required: true,
  },
});

const audits = ref([]);
const loading = ref(false);

const fetchAudits = async () => {
  if (!props.auditableId) return;

  loading.value = true;
  try {
    const response = await axios.get('/audits', {
      params: {
        auditable_type: props.auditableType,
        auditable_id: props.auditableId,
      },
    });
    audits.value = response.data || [];
  } catch (error) {
    console.error('Failed to fetch audits:', error);
    audits.value = [];
  } finally {
    loading.value = false;
  }
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const getEventLabel = (event) => {
  const labels = {
    created: 'Created',
    updated: 'Updated',
    deleted: 'Deleted',
  };
  return labels[event] || event;
};

const getEventColor = (event) => {
  const colors = {
    created: 'text-success-600 bg-success-50',
    updated: 'text-primary-600 bg-primary-50',
    deleted: 'text-danger-600 bg-danger-50',
  };
  return colors[event] || 'text-slate-600 bg-slate-50';
};

onMounted(() => {
  fetchAudits();
});

watch(() => [props.auditableType, props.auditableId], () => {
  fetchAudits();
});
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h3 class="text-lg font-medium text-slate-900">Audit Trail</h3>
      <button
        v-if="!loading"
        @click="fetchAudits"
        class="text-sm text-primary-600 hover:text-primary-700"
      >
        Refresh
      </button>
    </div>

    <div v-if="loading" class="space-y-3">
      <div v-for="i in 3" :key="i" class="animate-pulse">
        <div class="h-20 bg-slate-100 rounded-lg"></div>
      </div>
    </div>

    <div v-else-if="audits.length === 0" class="text-center py-8">
      <p class="text-sm text-slate-500">No audit trail available</p>
    </div>

    <div v-else class="space-y-3">
      <div
        v-for="audit in audits"
        :key="audit.id"
        class="border border-slate-200 rounded-lg p-4 hover:border-slate-300 transition-colors"
      >
        <div class="flex items-start justify-between mb-2">
          <div class="flex items-center gap-2">
            <span
              :class="getEventColor(audit.event)"
              class="px-2 py-1 text-xs font-medium rounded"
            >
              {{ getEventLabel(audit.event) }}
            </span>
            <span class="text-sm text-slate-600 flex items-center gap-1">
              <User :size="14" />
              {{ audit.user_name }}
            </span>
          </div>
          <span class="text-xs text-slate-500 flex items-center gap-1">
            <Clock :size="14" />
            {{ formatDate(audit.created_at) }}
          </span>
        </div>

        <div v-if="audit.changes && audit.changes.length > 0" class="mt-3">
          <div class="space-y-2">
            <div
              v-for="(change, idx) in audit.changes"
              :key="idx"
              class="text-sm"
            >
              <span class="font-medium text-slate-700">{{ change.field }}:</span>
              <span class="text-slate-500 ml-1">
                <span v-if="change.old_value" class="line-through">{{ change.old_value }}</span>
                <span v-if="change.old_value && change.new_value" class="mx-1">→</span>
                <span class="text-slate-900 font-medium">{{ change.new_value }}</span>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
