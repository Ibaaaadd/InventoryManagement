<script setup>
import { ref, onMounted } from 'vue';
import axios from '@/lib/axios';
import BaseBadge from '@/components/ui/BaseBadge.vue';

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

const auditLogs = ref([]);
const loading = ref(false);

onMounted(async () => {
  loading.value = true;
  try {
    auditLogs.value = [
      {
        id: 1,
        user: 'John Doe',
        action: 'Created',
        changes: 'Initial record created',
        timestamp: '2026-07-25 10:30:00',
      },
      {
        id: 2,
        user: 'Jane Smith',
        action: 'Updated',
        changes: 'Changed quantity from 100 to 150',
        timestamp: '2026-07-25 11:15:00',
      },
      {
        id: 3,
        user: 'Manager User',
        action: 'Approved',
        changes: 'Mutation approved',
        timestamp: '2026-07-25 12:00:00',
      },
    ];
  } catch (error) {
    console.error('Failed to fetch audit logs:', error);
  } finally {
    loading.value = false;
  }
});

const getActionVariant = (action) => {
  const variants = {
    Created: 'info',
    Updated: 'warning',
    Approved: 'success',
    Rejected: 'danger',
  };
  return variants[action] || 'default';
};
</script>

<template>
  <div class="space-y-4">
    <h3 class="text-lg font-semibold text-gray-900">Audit Trail</h3>
    
    <div v-if="loading" class="text-center py-8 text-gray-500">
      Loading audit trail...
    </div>
    
    <div v-else-if="auditLogs.length === 0" class="text-center py-8 text-gray-500">
      No audit logs available
    </div>
    
    <div v-else class="flow-root">
      <ul class="-mb-8">
        <li v-for="(log, index) in auditLogs" :key="log.id">
          <div class="relative pb-8">
            <span
              v-if="index !== auditLogs.length - 1"
              class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200"
            ></span>
            <div class="relative flex space-x-3">
              <div>
                <span
                  class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white"
                  :class="{
                    'bg-blue-500': log.action === 'Created',
                    'bg-yellow-500': log.action === 'Updated',
                    'bg-green-500': log.action === 'Approved',
                    'bg-red-500': log.action === 'Rejected',
                  }"
                >
                  <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                </span>
              </div>
              <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                <div>
                  <p class="text-sm text-gray-500">
                    <span class="font-medium text-gray-900">{{ log.user }}</span>
                    {{ log.action.toLowerCase() }} this record
                  </p>
                  <p class="mt-1 text-sm text-gray-600">{{ log.changes }}</p>
                </div>
                <div class="whitespace-nowrap text-right text-sm text-gray-500">
                  <time>{{ log.timestamp }}</time>
                </div>
              </div>
            </div>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>
