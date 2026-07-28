<script setup>
import { computed } from 'vue';
import { Line } from 'vue-chartjs';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Filler
} from 'chart.js';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Filler
);

const props = defineProps({
  chartData: {
    type: Object,
    required: true
  },
  maxValue: {
    type: Number,
    default: null
  }
});

const computedOptions = computed(() => {
  const maxDataValue = props.maxValue || Math.max(
    ...(props.chartData?.datasets?.flatMap(d => d.data) || [0])
  );
  
  const suggestedMax = maxDataValue > 0 ? Math.ceil(maxDataValue * 1.2) : 10;
  
  const monthNames = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
  ];
  
  const formatIndonesianDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const day = date.getDate();
    const month = monthNames[date.getMonth()];
    const year = date.getFullYear();
    return `${day} ${month} ${year}`;
  };
  
  return {
    responsive: true,
    maintainAspectRatio: false,
    layout: {
      padding: {
        left: 8,
        right: 8,
        top: 8,
        bottom: 8
      }
    },
    interaction: {
      mode: 'index',
      intersect: false,
    },
    plugins: {
      legend: {
        display: false
      },
      tooltip: {
        enabled: true,
        position: 'nearest',
        backgroundColor: 'rgba(0, 0, 0, 0.8)',
        padding: 10,
        cornerRadius: 6,
        titleColor: '#fff',
        bodyColor: '#fff',
        borderColor: 'rgba(255, 255, 255, 0.1)',
        borderWidth: 1,
        displayColors: true,
        titleFont: {
          size: 12,
          weight: '600'
        },
        bodyFont: {
          size: 11
        },
        callbacks: {
          title: function(tooltipItems) {
            if (!tooltipItems || tooltipItems.length === 0) return '';
            const index = tooltipItems[0].dataIndex;
            const fullDates = props.chartData?.fullDates;
            if (fullDates && fullDates[index]) {
              return formatIndonesianDate(fullDates[index]);
            }
            return tooltipItems[0].label;
          }
        }
      }
    },
    scales: {
      x: {
        grid: {
          display: false
        },
        ticks: {
          color: '#71717A',
          font: {
            size: 11
          },
          maxRotation: 0,
          autoSkip: true,
          autoSkipPadding: 20,
          maxTicksLimit: 12
        },
        border: {
          display: false
        }
      },
      y: {
        beginAtZero: true,
        suggestedMax: suggestedMax,
        grid: {
          color: 'rgba(226, 232, 240, 0.5)',
          drawBorder: false,
          lineWidth: 1,
          borderDash: [3, 3]
        },
        ticks: {
          color: '#71717A',
          font: {
            size: 11
          },
          precision: 0,
          maxTicksLimit: 5,
          padding: 8
        },
        border: {
          display: false
        }
      }
    }
  };
});
</script>

<template>
  <div class="w-full h-full">
    <Line :data="chartData" :options="computedOptions" />
  </div>
</template>
