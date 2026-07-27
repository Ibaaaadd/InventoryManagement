<script setup>
import { computed } from 'vue';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

const props = defineProps({
  currentPage: {
    type: Number,
    required: true,
  },
  totalPages: {
    type: Number,
    required: true,
  },
  perPage: {
    type: Number,
    default: 10,
  },
  total: {
    type: Number,
    required: true,
  },
});

const emit = defineEmits(['page-change']);

const changePage = (page) => {
  if (page >= 1 && page <= props.totalPages) {
    emit('page-change', page);
  }
};

const visiblePages = computed(() => {
  const pages = [];
  const delta = 2;
  const left = Math.max(2, props.currentPage - delta);
  const right = Math.min(props.totalPages - 1, props.currentPage + delta);

  pages.push(1);

  if (left > 2) {
    pages.push('...');
  }

  for (let i = left; i <= right; i++) {
    pages.push(i);
  }

  if (right < props.totalPages - 1) {
    pages.push('...');
  }

  if (props.totalPages > 1) {
    pages.push(props.totalPages);
  }

  return pages;
});

const rangeStart = computed(() => (props.currentPage - 1) * props.perPage + 1);
const rangeEnd = computed(() => Math.min(props.currentPage * props.perPage, props.total));
</script>

<template>
  <div class="flex items-center justify-between border-slate-200 bg-white px-4 py-3 sm:px-6 rounded-b-lg">
    <div class="flex flex-1 justify-between sm:hidden">
      <button
        @click="changePage(currentPage - 1)"
        :disabled="currentPage === 1"
        class="relative inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
      >
        <ChevronLeft :size="16" />
        Previous
      </button>
      <button
        @click="changePage(currentPage + 1)"
        :disabled="currentPage === totalPages"
        class="relative inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
      >
        Next
        <ChevronRight :size="16" />
      </button>
    </div>
    
    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
      <div>
        <p class="text-sm text-slate-600">
          Showing <span class="font-semibold text-slate-900">{{ total === 0 ? 0 : rangeStart }}</span> to
          <span class="font-semibold text-slate-900">{{ rangeEnd }}</span> of
          <span class="font-semibold text-slate-900">{{ total }}</span> results
        </p>
      </div>
      
      <nav class="isolate inline-flex gap-1">
        <button
          @click="changePage(currentPage - 1)"
          :disabled="currentPage === 1"
          class="relative inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-600 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
        >
          <ChevronLeft :size="18" />
        </button>
        
        <button
          v-for="(page, index) in visiblePages"
          :key="index"
          @click="page !== '...' && changePage(page)"
          :disabled="page === '...'"
          class="relative inline-flex items-center justify-center min-w-[36px] h-9 px-3 text-sm font-medium rounded-lg transition-all duration-200"
          :class="{
            'bg-primary-600 text-white shadow-sm hover:bg-primary-700': page === currentPage,
            'text-slate-700 hover:bg-slate-100': page !== currentPage && page !== '...',
            'text-slate-400 cursor-default': page === '...',
          }"
        >
          {{ page }}
        </button>
        
        <button
          @click="changePage(currentPage + 1)"
          :disabled="currentPage === totalPages"
          class="relative inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-600 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
        >
          <ChevronRight :size="18" />
        </button>
      </nav>
    </div>
  </div>
</template>
