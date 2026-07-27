<script setup>
import { ref } from 'vue';
import { ChevronUp, ChevronDown, Inbox } from 'lucide-vue-next';
import BasePagination from './BasePagination.vue';

const props = defineProps({
  columns: {
    type: Array,
    required: true,
  },
  data: {
    type: Array,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  emptyMessage: {
    type: String,
    default: 'No data available',
  },
  // Pagination props
  showPagination: {
    type: Boolean,
    default: false,
  },
  currentPage: {
    type: Number,
    default: 1,
  },
  lastPage: {
    type: Number,
    default: 1,
  },
  total: {
    type: Number,
    default: 0,
  }
});

const emit = defineEmits(['sort', 'row-click', 'page-change']);

const sortBy = ref('');
const sortDirection = ref('asc');

const handleSort = (column) => {
  if (column.sortable === false) return;

  if (sortBy.value === column.key) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = column.key;
    sortDirection.value = 'asc';
  }

  emit('sort', { key: column.key, direction: sortDirection.value });
};

const handleRowClick = (row) => {
  emit('row-click', row);
};

const handlePageChange = (page) => {
  emit('page-change', page);
};

const getColumnAlign = (align) => {
  if (align === 'right') return 'text-right';
  if (align === 'center') return 'text-center';
  return 'text-left';
};
</script>

<template>
  <div class="overflow-hidden rounded-lg border border-slate-200 bg-white flex flex-col">
    <div class="overflow-x-auto flex-1">
      <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
          <tr>
            <th
              v-for="column in columns"
              :key="column.key"
              @click="handleSort(column)"
              class="px-6 py-4 text-xs font-medium text-slate-500 uppercase tracking-wide"
              :class="[
                getColumnAlign(column.align),
                column.sortable !== false ? 'cursor-pointer hover:bg-slate-100/80 transition-colors select-none' : ''
              ]"
            >
              <div class="flex items-center gap-2" :class="{
                'justify-end': column.align === 'right',
                'justify-center': column.align === 'center',
              }">
                <span>{{ column.label }}</span>
                <span 
                  v-if="column.sortable !== false"
                  class="flex flex-col -space-y-1"
                >
                  <ChevronUp 
                    :size="14" 
                    :class="sortBy === column.key && sortDirection === 'asc' ? 'text-primary-600' : 'text-slate-400'"
                  />
                  <ChevronDown 
                    :size="14" 
                    :class="sortBy === column.key && sortDirection === 'desc' ? 'text-primary-600' : 'text-slate-400'"
                  />
                </span>
              </div>
            </th>
            <th v-if="$slots.actions" class="px-6 py-4 text-right text-xs font-medium text-slate-500 uppercase tracking-wide">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-slate-100">
          <template v-if="loading">
            <tr v-for="i in 5" :key="`loading-${i}`" class="animate-pulse">
              <td v-for="column in columns" :key="`loading-${i}-${column.key}`" class="px-6 py-4">
                <div class="h-4 bg-slate-200 rounded" :class="{
                  'w-20 ml-auto': column.align === 'right',
                  'w-20 mx-auto': column.align === 'center',
                  'w-32': !column.align || column.align === 'left',
                }"></div>
              </td>
              <td v-if="$slots.actions" class="px-6 py-4">
                <div class="h-4 bg-slate-200 rounded w-24 ml-auto"></div>
              </td>
            </tr>
          </template>
          
          <tr v-else-if="data.length === 0">
            <td :colspan="columns.length + ($slots.actions ? 1 : 0)" class="px-6 py-16 text-center">
              <div class="flex flex-col items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center">
                  <Inbox :size="24" class="text-slate-400" />
                </div>
                <div>
                  <p class="text-sm font-medium text-slate-900">{{ emptyMessage }}</p>
                  <p class="text-xs text-slate-500 mt-1">There are no records to display at this time</p>
                </div>
              </div>
            </td>
          </tr>

          <tr
            v-else
            v-for="(row, index) in data"
            :key="row.id || index"
            @click="handleRowClick(row)"
            class="hover:bg-slate-50/50 transition-colors cursor-pointer border-b border-slate-100 last:border-b-0"
          >
            <td
              v-for="column in columns"
              :key="column.key"
              class="px-6 py-4 text-sm text-slate-900"
              :class="getColumnAlign(column.align)"
            >
              <slot :name="`cell-${column.key}`" :row="row" :value="row[column.key]">
                {{ row[column.key] }}
              </slot>
            </td>
            <td v-if="$slots.actions" class="px-6 py-4 text-right text-sm whitespace-nowrap">
              <slot name="actions" :row="row" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Built-in pagination -->
    <div v-if="showPagination" class="border-t border-slate-200 px-0 sm:px-0 py-0 bg-slate-50/50">
      <BasePagination
        :current-page="currentPage"
        :total-pages="lastPage"
        :total="total"
        @page-change="handlePageChange"
      />
    </div>

    <!-- Fallback slot pagination if needed -->
    <div v-else-if="$slots.pagination" class="border-t border-slate-200 px-6 py-4 bg-slate-50/50">
      <slot name="pagination" />
    </div>
  </div>
</template>
