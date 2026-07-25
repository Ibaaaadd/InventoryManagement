<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import BaseCard from '@/components/ui/BaseCard.vue';
import BaseBadge from '@/components/ui/BaseBadge.vue';
import BaseButton from '@/components/ui/BaseButton.vue';

const router = useRouter();
const authStore = useAuthStore();

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

const getStatusVariant = (status) => {
  const variants = {
    pending: 'pending',
    approved: 'approved',
    rejected: 'rejected',
  };
  return variants[status] || 'default';
};
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
      <p class="mt-1 text-sm text-gray-600">Welcome back, {{ authStore.user?.name }}</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <BaseCard :padding="true" :shadow="true">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Total Items</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ stats.totalItems }}</p>
          </div>
          <div class="p-3 bg-blue-100 rounded-full">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
          </div>
        </div>
      </BaseCard>

      <BaseCard :padding="true" :shadow="true">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Pending Mutations</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ stats.pendingMutations }}</p>
          </div>
          <div class="p-3 bg-yellow-100 rounded-full">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </BaseCard>

      <BaseCard :padding="true" :shadow="true">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Approved Today</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ stats.approvedToday }}</p>
          </div>
          <div class="p-3 bg-green-100 rounded-full">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </BaseCard>

      <BaseCard :padding="true" :shadow="true">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Active Users</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ stats.activeUsers }}</p>
          </div>
          <div class="p-3 bg-purple-100 rounded-full">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
          </div>
        </div>
      </BaseCard>
    </div>

    <BaseCard title="Recent Stock Mutations" :padding="true" :shadow="true">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="loading">
              <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Loading...</td>
            </tr>
            <tr v-else-if="recentMutations.length === 0">
              <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No recent mutations</td>
            </tr>
            <tr v-else v-for="mutation in recentMutations" :key="mutation.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ mutation.item }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <BaseBadge :variant="mutation.type === 'IN' ? 'success' : 'warning'">
                  {{ mutation.type }}
                </BaseBadge>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ mutation.quantity }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <BaseBadge :variant="getStatusVariant(mutation.status)">
                  {{ mutation.status }}
                </BaseBadge>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ mutation.date }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <BaseButton
                  @click="navigateTo(`/stock-mutations/${mutation.id}`)"
                  variant="ghost"
                  size="sm"
                >
                  View
                </BaseButton>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </BaseCard>
  </div>
</template>
