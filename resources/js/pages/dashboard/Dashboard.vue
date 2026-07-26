<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { Package, Clock, CheckCircle, Users, Eye } from 'lucide-vue-next';
import { useAuthStore } from '@/stores/auth';
import { useStatusBadge } from '@/composables/useStatusBadge';
import BaseCard from '@/components/ui/BaseCard.vue';
import BaseBadge from '@/components/ui/BaseBadge.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import DataTable from '@/components/ui/DataTable.vue';

const router = useRouter();
const authStore = useAuthStore();
const { getVariant, formatStatus } = useStatusBadge();

const stats = ref({
  totalItems: 0,
  pendingMutations: 0,
  approvedToday: 0,
  activeUsers: 0,
});

const recentMutations = ref([]);
const loading = ref(false);

onMounted(async () => {
  loading.value = true;
  try {
    stats.value = {
      totalItems: 245,
      pendingMutations: 12,
      approvedToday: 8,
      activeUsers: 15,
    };

    recentMutations.value = [
      { id: 1, item: 'Laptop Dell XPS 15', type: 'IN', quantity: 10, status: 'pending', date: '2026-07-25' },
      { id: 2, item: 'Mouse Wireless', type: 'OUT', quantity: 5, status: 'approved', date: '2026-07-25' },
      { id: 3, item: 'Keyboard Mechanical', type: 'IN', quantity: 8, status: 'pending', date: '2026-07-24' },
    ];
  } catch (error) {
    console.error('Failed to fetch dashboard data:', error);
  } finally {
    loading.value = false;
  }
});

const navigateTo = (path) => {
  router.push(path);
};

const tableColumns = [
  { key: 'item', label: 'Item', sortable: true },
  { key: 'type', label: 'Type', sortable: true },
  { key: 'quantity', label: 'Quantity', sortable: true },
  { key: 'status', label: 'Status', sortable: true },
  { key: 'date', label: 'Date', sortable: true },
];
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
      <p class="mt-1 text-sm text-slate-600">Welcome back, {{ authStore.user?.name }}</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <BaseCard :padding="true" :shadow="true">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-slate-600">Total Items</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ stats.totalItems }}</p>
          </div>
          <div class="p-3 bg-primary-50 rounded-full">
            <Package :size="24" class="text-primary-600" />
          </div>
        </div>
      </BaseCard>

      <BaseCard :padding="true" :shadow="true">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-slate-600">Pending Mutations</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ stats.pendingMutations }}</p>
          </div>
          <div class="p-3 bg-warning-50 rounded-full">
            <Clock :size="24" class="text-warning-600" />
          </div>
        </div>
      </BaseCard>

      <BaseCard :padding="true" :shadow="true">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-slate-600">Approved Today</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ stats.approvedToday }}</p>
          </div>
          <div class="p-3 bg-success-50 rounded-full">
            <CheckCircle :size="24" class="text-success-600" />
          </div>
        </div>
      </BaseCard>

      <BaseCard :padding="true" :shadow="true">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-slate-600">Active Users</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ stats.activeUsers }}</p>
          </div>
          <div class="p-3 bg-info-50 rounded-full">
            <Users :size="24" class="text-info-600" />
          </div>
        </div>
      </BaseCard>
    </div>

    <BaseCard title="Recent Stock Mutations" :padding="true" :shadow="true">
      <DataTable
        :columns="tableColumns"
        :data="recentMutations"
        :loading="loading"
        empty-message="No recent mutations"
      >
        <template #cell-type="{ row }">
          <BaseBadge :variant="getVariant(row.type)">
            {{ row.type }}
          </BaseBadge>
        </template>
        <template #cell-status="{ row }">
          <BaseBadge :variant="getVariant(row.status)">
            {{ formatStatus(row.status) }}
          </BaseBadge>
        </template>
        <template #actions="{ row }">
          <button
            @click.stop="navigateTo(`/stock-mutations/${row.id}`)"
            class="p-2 text-slate-600 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors"
            title="View Details"
          >
            <Eye :size="16" />
          </button>
        </template>
      </DataTable>
    </BaseCard>
  </div>
</template>
