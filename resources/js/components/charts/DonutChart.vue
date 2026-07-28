<script setup>
import { computed } from 'vue';
import { Doughnut } from 'vue-chartjs';
import {
  Chart as ChartJS,
  ArcElement,
  Tooltip
} from 'chart.js';

ChartJS.register(ArcElement, Tooltip);

const props = defineProps({
  chartData: {
    type: Object,
    required: true
  },
  showTotal: {
    type: Boolean,
    default: true
  }
});

const total = computed(() => {
  if (!props.chartData?.datasets?.[0]?.data) return 0;
  return props.chartData.datasets[0].data.reduce((sum, val) => sum + val, 0);
});

const legendItems = computed(() => {
  if (!props.chartData?.labels || !props.chartData?.datasets?.[0]) return [];
  
  const data = props.chartData.datasets[0].data;
  const colors = props.chartData.datasets[0].backgroundColor;
  const totalValue = total.value;
  
  return props.chartData.labels.map((label, index) => ({
    label,
    value: data[index],
    color: Array.isArray(colors) ? colors[index] : colors,
    percentage: totalValue > 0 ? Math.round((data[index] / totalValue) * 100) : 0
  }));
});

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  cutout: '72%',
  plugins: {
    legend: {
      display: false
    },
    tooltip: {
      enabled: true,
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      padding: 12,
      cornerRadius: 8,
      titleColor: '#fff',
      bodyColor: '#fff',
      borderColor: 'rgba(255, 255, 255, 0.1)',
      borderWidth: 1,
      displayColors: true,
      callbacks: {
        label: function(context) {
          const label = context.label || '';
          const value = context.parsed;
          const total = context.dataset.data.reduce((a, b) => a + b, 0);
          const percentage = Math.round((value / total) * 100);
          return `${label}: ${value} (${percentage}%)`;
        }
      }
    }
  }
};
</script>

<template>
  <div class="w-full h-full flex flex-col">
    <div class="relative flex-1" style="min-height: 200px;">
      <Doughnut :data="chartData" :options="chartOptions" />
      
      <div v-if="showTotal" class="absolute inset-0 flex items-center justify-center pointer-events-none">
        <div class="text-center">
          <div class="text-3xl font-bold text-[#18181B]">{{ total }}</div>
          <div class="text-xs font-medium text-[#71717A] uppercase tracking-wide mt-1">Total</div>
        </div>
      </div>
    </div>
    
    <div class="mt-4 space-y-2 overflow-hidden">
      <div
        v-for="item in legendItems"
        :key="item.label"
        class="flex items-center justify-between py-2 px-1 rounded-lg hover:bg-slate-50 transition-colors"
      >
        <div class="flex items-center gap-2 flex-1 min-w-0 mr-2">
          <div
            class="w-3 h-3 rounded-full flex-shrink-0"
            :style="{ backgroundColor: item.color }"
          ></div>
          <span class="text-sm font-medium text-[#18181B] truncate">{{ item.label }}</span>
        </div>
        <div class="flex items-center gap-1.5 flex-shrink-0">
          <span class="text-sm font-semibold text-[#18181B] tabular-nums">{{ item.value }}</span>
          <span class="text-[11px] font-medium text-[#71717A] tabular-nums w-[2.25rem] text-right">{{ item.percentage }}%</span>
        </div>
      </div>
    </div>
  </div>
</template>
