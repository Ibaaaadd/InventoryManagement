<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { Package, Clock, AlertTriangle, Users, TrendingUp, TrendingDown, CheckCircle, Eye } from 'lucide-vue-next';
import { useAuthStore } from '@/stores/auth';
import { useStatusBadge } from '@/composables/useStatusBadge';
import { useDateFormat } from '@/composables/useDateFormat';
import axios from 'axios';
import BaseCard from '@/components/ui/BaseCard.vue';
import BaseBadge from '@/components/ui/BaseBadge.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import DataTable from '@/components/ui/DataTable.vue';
import LineChart from '@/components/charts/LineChart.vue';
import DonutChart from '@/components/charts/DonutChart.vue';

const router = useRouter();
const authStore = useAuthStore();
const { getVariant, formatStatus } = useStatusBadge();
const { formatDate, formatDateTime } = useDateFormat();

const overview = ref(null);
const trendData = ref(null);
const loading = ref(false);
const trendLoading = ref(false);
const selectedMonth = ref(new Date().getMonth() + 1);
const selectedYear = ref(new Date().getFullYear());
const availableYears = ref([]);

const monthNames = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

const currentDate = computed(() => {
  const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
  return new Date().toLocaleDateString('id-ID', options);
});

const lineChartData = computed(() => {
  if (!trendData.value) return null;
  return {
    labels: trendData.value.labels,
    fullDates: trendData.value.full_dates,
    datasets: [
      {
        label: 'Stock In',
        data: trendData.value.stock_in,
        borderColor: '#10B981',
        backgroundColor: 'rgba(16, 185, 129, 0.08)',
        tension: 0.4,
        fill: true,
        borderWidth: 2,
        pointRadius: 0,
        pointHoverRadius: 4,
        pointBackgroundColor: '#10B981',
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
      },
      {
        label: 'Stock Out',
        data: trendData.value.stock_out,
        borderColor: '#F43F5E',
        backgroundColor: 'rgba(244, 63, 94, 0.08)',
        tension: 0.4,
        fill: true,
        borderWidth: 2,
        pointRadius: 0,
        pointHoverRadius: 4,
        pointBackgroundColor: '#F43F5E',
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
      },
    ],
  };
});

const lineChartMaxValue = computed(() => {
  if (!trendData.value) return 10;
  const allValues = [...trendData.value.stock_in, ...trendData.value.stock_out];
  return Math.max(...allValues, 0);
});

const donutChartData = computed(() => {
  if (!overview.value) return null;
  const breakdown = overview.value.mutation_status_breakdown;
  return {
    labels: ['Pending', 'Approved', 'Rejected'],
    datasets: [
      {
        data: [breakdown.pending, breakdown.approved, breakdown.rejected],
        backgroundColor: ['#F59E0B', '#10B981', '#F43F5E'],
        borderWidth: 0,
        hoverOffset: 8,
      },
    ],
  };
});

const fetchOverview = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/dashboard/overview');
    overview.value = response.data;
  } catch (error) {
    console.error('Failed to fetch dashboard overview:', error);
  } finally {
    loading.value = false;
  }
};

const fetchYears = async () => {
  try {
    const response = await axios.get('/api/dashboard/mutation-years');
    availableYears.value = response.data;
    
    if (!availableYears.value.includes(selectedYear.value)) {
      selectedYear.value = availableYears.value[0] || new Date().getFullYear();
    }
  } catch (error) {
    console.error('Failed to fetch available years:', error);
    availableYears.value = [new Date().getFullYear()];
  }
};

const fetchTrend = async () => {
  trendLoading.value = true;
  try {
    const response = await axios.get('/api/dashboard/mutation-trend', {
      params: { 
        month: selectedMonth.value,
        year: selectedYear.value
      }
    });
    trendData.value = response.data;
  } catch (error) {
    console.error('Failed to fetch mutation trend:', error);
  } finally {
    trendLoading.value = false;
  }
};

const onMonthChange = () => {
  fetchTrend();
};

const onYearChange = () => {
  fetchTrend();
};

const navigateTo = (path) => {
  router.push(path);
};

const getStockClass = (quantity) => {
  if (quantity <= 5) return 'text-red-600 font-semibold';
  if (quantity <= 10) return 'text-amber-600 font-medium';
  return 'text-slate-900';
};

onMounted(async () => {
  fetchOverview();
  await fetchYears();
  fetchTrend();
});
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
      <p class="mt-1 text-sm text-slate-600">Selamat datang, {{ authStore.user?.name }}</p>
    </div>

    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <BaseCard v-for="i in 4" :key="i" :padding="true" :shadow="true">
        <div class="animate-pulse space-y-3">
          <div class="h-4 bg-slate-200 rounded w-2/3"></div>
          <div class="h-8 bg-slate-200 rounded w-1/2"></div>
        </div>
      </BaseCard>
    </div>

    <div v-else-if="overview" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="bg-white rounded-lg shadow-sm border-l-2 border-slate-300 p-6">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <p class="text-[10px] font-semibold text-[#71717A] uppercase tracking-wide mb-3">Total Items</p>
            <p class="text-3xl font-bold text-[#18181B] tabular-nums">{{ overview.summary.total_items }}</p>
          </div>
          <Package :size="20" class="text-slate-300 mt-1" />
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border-l-2 border-[#F59E0B] p-6">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <p class="text-[10px] font-semibold text-[#71717A] uppercase tracking-wide mb-3">Pending Mutations</p>
            <p class="text-3xl font-bold text-[#18181B] tabular-nums">{{ overview.summary.pending_mutations }}</p>
          </div>
          <Clock :size="20" class="text-[#F59E0B] opacity-60 mt-1" />
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border-l-2 border-[#F43F5E] p-6">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <p class="text-[10px] font-semibold text-[#71717A] uppercase tracking-wide mb-3">Low Stock Items</p>
            <p class="text-3xl font-bold text-[#18181B] tabular-nums">{{ overview.summary.low_stock_items }}</p>
          </div>
          <AlertTriangle :size="20" class="text-[#F43F5E] opacity-60 mt-1" />
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border-l-2 border-slate-300 p-6">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <p class="text-[10px] font-semibold text-[#71717A] uppercase tracking-wide mb-3">Total Users</p>
            <p class="text-3xl font-bold text-[#18181B] tabular-nums">{{ overview.summary.total_users }}</p>
          </div>
          <Users :size="20" class="text-slate-300 mt-1" />
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="bg-white rounded-lg shadow-sm p-6 lg:col-span-2">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-base font-semibold text-[#18181B]">Tren Mutasi Stok</h3>
          
          <div class="flex items-center gap-6">
            <div class="flex items-center gap-3">
              <div class="flex items-center gap-1.5">
                <div class="w-2.5 h-2.5 rounded-full bg-[#10B981]"></div>
                <span class="text-xs font-medium text-[#71717A]">Stock In</span>
              </div>
              <div class="flex items-center gap-1.5">
                <div class="w-2.5 h-2.5 rounded-full bg-[#F43F5E]"></div>
                <span class="text-xs font-medium text-[#71717A]">Stock Out</span>
              </div>
            </div>
            
            <div class="flex items-center gap-2">
              <select
                v-model="selectedMonth"
                @change="onMonthChange"
                class="px-3 py-1.5 text-xs font-medium text-[#18181B] bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-200 transition-all"
              >
                <option v-for="(name, index) in monthNames" :key="index" :value="index + 1">
                  {{ name }}
                </option>
              </select>
              
              <select
                v-model="selectedYear"
                @change="onYearChange"
                class="px-3 py-1.5 text-xs font-medium text-[#18181B] bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-200 transition-all"
              >
                <option v-for="year in availableYears" :key="year" :value="year">
                  {{ year }}
                </option>
              </select>
            </div>
          </div>
        </div>
        
        <div v-if="trendLoading" class="h-72 flex items-center justify-center">
          <div class="animate-spin rounded-full h-8 w-8 border-2 border-slate-200 border-t-[#18181B]"></div>
        </div>
        <div v-else-if="lineChartData" class="h-72">
          <LineChart :chart-data="lineChartData" :max-value="lineChartMaxValue" />
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-base font-semibold text-[#18181B] mb-6">Status Mutasi</h3>
        
        <div v-if="loading" class="h-72 flex items-center justify-center">
          <div class="animate-spin rounded-full h-8 w-8 border-2 border-slate-200 border-t-[#18181B]"></div>
        </div>
        <div v-else-if="donutChartData" class="h-72">
          <DonutChart :chart-data="donutChartData" />
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-base font-semibold text-[#18181B] mb-6">Menunggu Approval Kamu</h3>
        
        <div v-if="loading" class="space-y-3">
          <div v-for="i in 3" :key="i" class="animate-pulse">
            <div class="h-16 bg-slate-100 rounded"></div>
          </div>
        </div>
        
        <div v-else-if="overview && overview.pending_approvals_for_me.length > 0" class="divide-y divide-slate-100">
          <div
            v-for="(mutation, index) in overview.pending_approvals_for_me"
            :key="mutation.id"
            class="py-3 first:pt-0 last:pb-0 hover:bg-slate-50 -mx-3 px-3 rounded-lg transition-colors cursor-pointer group"
            @click="navigateTo(`/stock-mutations/${mutation.id}`)"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-[#18181B] mb-1.5 truncate">{{ mutation.item_name_snapshot }}</p>
                <div class="flex items-center gap-2 mb-1">
                  <span 
                    :class="[
                      'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold',
                      mutation.type === 'in' 
                        ? 'bg-emerald-50 text-emerald-700' 
                        : 'bg-rose-50 text-rose-700'
                    ]"
                  >
                    <TrendingUp v-if="mutation.type === 'in'" :size="10" class="mr-1" />
                    <TrendingDown v-else :size="10" class="mr-1" />
                    {{ mutation.type === 'in' ? 'IN' : 'OUT' }}
                  </span>
                  <span class="text-xs font-medium text-[#18181B]">{{ mutation.quantity }} unit</span>
                </div>
                <p class="text-[11px] text-[#71717A]">
                  {{ mutation.user_name }} • {{ formatDateTime(mutation.created_at) }}
                </p>
              </div>
              <Eye :size="14" class="text-slate-300 group-hover:text-slate-400 transition-colors mt-1 ml-2 flex-shrink-0" />
            </div>
          </div>
        </div>
        
        <div v-else class="text-center py-12">
          <CheckCircle :size="40" class="mx-auto mb-3 text-slate-200" />
          <p class="text-sm text-[#71717A]">Tidak ada yang perlu direview saat ini</p>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-base font-semibold text-[#18181B] mb-6">Item Stok Menipis</h3>
        
        <div v-if="loading" class="space-y-3">
          <div v-for="i in 3" :key="i" class="animate-pulse">
            <div class="h-16 bg-slate-100 rounded"></div>
          </div>
        </div>
        
        <div v-else-if="overview && overview.low_stock_items.length > 0" class="divide-y divide-slate-100">
          <div
            v-for="item in overview.low_stock_items"
            :key="item.id"
            class="py-3 first:pt-0 last:pb-0 hover:bg-slate-50 -mx-3 px-3 rounded-lg transition-colors"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-[#18181B] mb-1 truncate">{{ item.name }}</p>
                <p class="text-[11px] text-[#71717A]">{{ item.sku }} • {{ item.category_name }}</p>
              </div>
              <div class="text-right ml-3 flex-shrink-0">
                <p 
                  :class="[
                    'text-xl font-bold tabular-nums',
                    item.stock_quantity <= 5 ? 'text-[#F43F5E]' : 'text-[#F59E0B]'
                  ]"
                >
                  {{ item.stock_quantity }}
                </p>
                <p class="text-[10px] text-[#71717A] uppercase tracking-wide font-medium">unit</p>
              </div>
            </div>
          </div>
        </div>
        
        <div v-else class="text-center py-12">
          <Package :size="40" class="mx-auto mb-3 text-slate-200" />
          <p class="text-sm text-[#71717A]">Semua item memiliki stok yang cukup</p>
        </div>
      </div>
    </div>
  </div>
</template>
