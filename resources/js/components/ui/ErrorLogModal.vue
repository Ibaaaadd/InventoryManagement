<script setup>
import { ref, computed, watch } from 'vue';
import { FileText, AlertTriangle, ChevronDown, ChevronUp, AlertCircle, XCircle, Copy, Check, Clipboard } from 'lucide-vue-next';
import BaseModal from './BaseModal.vue';
import BaseButton from './BaseButton.vue';
import BaseBadge from './BaseBadge.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
  modelValue: Boolean,
  job: {
    type: Object,
    required: false,
    default: null,
  },
});

const emit = defineEmits(['update:modelValue', 'close']);
const { showToast } = useToast();

const expandedRows = ref({});
const copiedIndex = ref(null);

const close = () => {
  emit('update:modelValue', false);
  emit('close');
};

const toggleRow = (index) => {
  expandedRows.value[index] = !expandedRows.value[index];
};

const copyToClipboard = async (text, index) => {
  try {
    await navigator.clipboard.writeText(text);
    copiedIndex.value = index;
    showToast('Disalin ke clipboard', 'success');
    setTimeout(() => { copiedIndex.value = null; }, 2000);
  } catch {
    showToast('Gagal menyalin', 'error');
  }
};

const getStatusBadge = (status) => {
  const variants = { pending: 'warning', processing: 'info', completed: 'success', failed: 'danger' };
  return variants[status] || 'default';
};

const getStatusLabel = (status) => {
  const labels = { pending: 'Menunggu', processing: 'Memproses', completed: 'Selesai', failed: 'Gagal' };
  return labels[status] || status;
};

const getTypeLabel = (type) => type === 'export' ? 'Export' : 'Import';
const getModelLabel = (model) => model === 'role' ? 'Role' : 'User';

const isImportJob = computed(() => props.job?.type === 'import');
const hasFailedRows = computed(() => (props.job?.failed_rows ?? 0) > 0);
const isFailedJob = computed(() => props.job?.status === 'failed');
const errorLog = computed(() => props.job?.error_log || []);

const formatError = (error) => {
  if (typeof error === 'string') return error;
  if (error?.message) return error.message;
  return JSON.stringify(error);
};

const getRowErrors = (errorEntry) => {
  if (errorEntry.errors && typeof errorEntry.errors === 'object') {
    return Object.entries(errorEntry.errors).map(([field, messages]) => ({
      field,
      messages: Array.isArray(messages) ? messages : [messages],
    }));
  }
  return [];
};

const isCatastrophicError = (errorEntry) =>
  errorEntry && typeof errorEntry === 'object' && errorEntry.message && !errorEntry.row;

const getErrorTypeClass = (entry) => {
  if (isCatastrophicError(entry)) return 'fatal';
  if (getRowErrors(entry).length > 0) return 'validation';
  return 'general';
};
</script>

<template>
<BaseModal 
    :model-value="modelValue" 
    :title="job ? `Detail Error: ${getTypeLabel(job.type)} ${getModelLabel(job.model)}` : 'Detail Error'" 
    size="xl"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <template v-if="job">
      <div class="space-y-5">
        <!-- Header Info -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 p-3 bg-slate-50 rounded-lg">
          <div>
            <p class="text-xs text-slate-500 uppercase tracking-wide">ID Job</p>
            <p class="font-mono text-sm text-slate-900 truncate">{{ job.id }}</p>
          </div>
          <div>
            <p class="text-xs text-slate-500 uppercase tracking-wide">Tipe</p>
            <BaseBadge :variant="job.type === 'export' ? 'info' : 'success'" class="mt-1">
              {{ getTypeLabel(job.type) }}
            </BaseBadge>
          </div>
          <div>
            <p class="text-xs text-slate-500 uppercase tracking-wide">Model</p>
            <p class="font-medium text-sm text-slate-900 mt-1 capitalize">{{ getModelLabel(job.model) }}</p>
          </div>
          <div>
            <p class="text-xs text-slate-500 uppercase tracking-wide">Status</p>
            <BaseBadge :variant="getStatusBadge(job.status)" class="mt-1">
              {{ getStatusLabel(job.status) }}
            </BaseBadge>
          </div>
        </div>

        <!-- Progress Info -->
        <div v-if="job.total_rows" class="flex items-center gap-4 text-sm p-3 bg-slate-50 rounded-lg">
          <div class="flex items-center gap-1">
            <span class="text-slate-500">Progress:</span>
            <span class="font-medium text-slate-900">{{ job.processed_rows || 0 }} / {{ job.total_rows }}</span>
          </div>
          <div v-if="job.failed_rows > 0" class="flex items-center gap-1 text-red-600">
            <XCircle :size="14" />
            <span class="font-medium">{{ job.failed_rows }} gagal</span>
          </div>
          <div v-else-if="job.status === 'completed'" class="flex items-center gap-1 text-green-600">
            <Check :size="14" />
            <span class="font-medium">Semua berhasil</span>
          </div>
        </div>

        <div class="pt-2 border-t border-slate-200">
          <h4 class="font-medium text-slate-900 mb-3 flex items-center gap-2">
            <AlertTriangle :size="18" class="text-amber-600" />
            Detail Error
          </h4>

          <!-- Import dengan baris gagal -->
          <div v-if="isImportJob && hasFailedRows && errorLog.length > 0" class="space-y-2">
            <div class="border border-slate-200 rounded-lg overflow-hidden">
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                  <thead class="bg-slate-50">
                    <tr>
                      <th class="px-4 py-2.5 text-left text-xs font-medium text-slate-500 uppercase tracking-wider w-8">#</th>
                      <th class="px-4 py-2.5 text-left text-xs font-medium text-slate-500 uppercase tracking-wider w-24">Baris Excel</th>
                      <th class="px-4 py-2.5 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Error Validasi</th>
                      <th class="px-4 py-2.5 text-left text-xs font-medium text-slate-500 uppercase tracking-wider w-28">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-slate-200">
                    <template v-for="(entry, index) in errorLog" :key="index">
                      <tr :class="expandedRows[index] ? 'bg-amber-50' : 'hover:bg-slate-50/50'">
                        <td class="px-4 py-2.5 text-sm text-slate-900">{{ index + 1 }}</td>
                        <td class="px-4 py-2.5 text-sm font-mono text-slate-900">{{ entry.row }}</td>
                        <td class="px-4 py-2.5 text-sm">
                          <div v-if="getRowErrors(entry).length > 0" class="space-y-1.5">
                            <div 
                              v-for="(fieldError, fIndex) in getRowErrors(entry)" 
                              :key="fIndex"
                              class="flex items-start gap-2 p-2 bg-red-50 border border-red-100 rounded"
                            >
                              <XCircle :size="14" class="text-red-500 mt-0.5 flex-shrink-0" />
                              <div class="flex-1 min-w-0">
                                <span class="font-medium text-red-700">{{ fieldError.field }}:</span>
                                <span class="text-red-600 ml-1 truncate block">{{ fieldError.messages.join(', ') }}</span>
                              </div>
                              <BaseButton
                                variant="ghost"
                                size="sm"
                                @click="copyToClipboard(fieldError.field + ': ' + fieldError.messages.join(', '), 'field-' + index + '-' + fIndex)"
                                class="p-1 text-red-500 hover:bg-red-100"
                              >
                                <Copy :size="12" v-if="copiedIndex !== 'field-' + index + '-' + fIndex" />
                                <Check :size="12" v-else class="text-green-500" />
                              </BaseButton>
                            </div>
                          </div>
                          <div v-else class="flex items-center gap-2 p-2 bg-red-50 border border-red-100 rounded">
                            <XCircle :size="14" class="text-red-500" />
                            <span class="text-red-600">{{ formatError(entry.errors) }}</span>
                            <BaseButton
                              variant="ghost"
                              size="sm"
                              @click="copyToClipboard(formatError(entry.errors), 'error-' + index)"
                              class="ml-auto p-1 text-red-500 hover:bg-red-100"
                            >
                              <Copy :size="12" v-if="copiedIndex !== 'error-' + index" />
                              <Check :size="12" v-else class="text-green-500" />
                            </BaseButton>
                          </div>
                        </td>
                        <td class="px-4 py-2.5 text-right">
                          <BaseButton
                            variant="ghost"
                            size="sm"
                            @click="toggleRow(index)"
                            class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100"
                            :title="expandedRows[index] ? 'Tutup detail' : 'Buka detail'"
                          >
                            <ChevronDown v-if="!expandedRows[index]" :size="16" />
                            <ChevronUp v-else :size="16" class="text-amber-600" />
                          </BaseButton>
                        </td>
                      </tr>
                      
                      <tr v-if="expandedRows[index]" class="bg-amber-50">
                        <td colspan="4" class="px-4 py-3 border-t border-slate-200">
                          <div class="flex items-center justify-between mb-2">
                            <span class="font-medium text-sm text-slate-700">Data Baris Lengkap (JSON)</span>
                            <BaseButton
                              variant="ghost"
                              size="sm"
                              @click="copyToClipboard(JSON.stringify(entry, null, 2), 'json-' + index)"
                              class="p-1.5 text-slate-500 hover:bg-slate-100"
                            >
                              <Copy :size="14" v-if="copiedIndex !== 'json-' + index" />
                              <Check :size="14" v-else class="text-green-500" />
                            </BaseButton>
                          </div>
                          <pre class="bg-slate-100 p-3 rounded text-xs overflow-x-auto text-slate-700 font-mono max-h-64">{{ JSON.stringify(entry, null, 2) }}</pre>
                        </td>
                      </tr>
                    </template>
                  </tbody>
                </table>
              </div>
            </div>
            
            <p class="text-xs text-slate-500 text-center">
              Total {{ errorLog.length }} baris error dari {{ job.total_rows }} baris data
            </p>
          </div>

          <!-- Job gagal total (catastrophic) -->
          <div v-else-if="isFailedJob && errorLog.length > 0" class="space-y-3">
            <div v-for="(entry, index) in errorLog" :key="index" class="border border-red-200 bg-red-50 rounded-lg overflow-hidden">
              <div class="p-4 border-b border-red-100">
                <div class="flex items-center gap-2 text-sm">
                  <AlertCircle :size="16" class="text-red-500" />
                  <span class="font-medium text-red-800">
                    {{ isCatastrophicError(entry) ? 'Error Fatal (System)' : 'Error Baris #' + (entry.row ?? 'N/A') }}
                  </span>
                </div>
              </div>
              
              <div class="p-4">
                <template v-if="isCatastrophicError(entry)">
                  <div class="space-y-3">
                    <div>
                      <label class="text-xs font-medium text-red-700 mb-1 block">Pesan Error</label>
                      <div class="flex items-start gap-2 p-3 bg-red-100 rounded text-sm text-red-800 font-mono">
                        {{ entry.message }}
                        <BaseButton
                          variant="ghost"
                          size="sm"
                          @click="copyToClipboard(entry.message, 'fatal-msg-' + index)"
                          class="ml-auto p-1 text-red-500 hover:bg-red-200"
                        >
                          <Copy :size="12" v-if="copiedIndex !== 'fatal-msg-' + index" />
                          <Check :size="12" v-else class="text-green-500" />
                        </BaseButton>
                      </div>
                    </div>
                    
                    <div v-if="entry.trace">
                      <label class="text-xs font-medium text-red-700 mb-1 block">Stack Trace</label>
                      <details class="group">
                        <summary class="cursor-pointer text-sm font-medium text-red-700 flex items-center gap-2 p-2 bg-red-100 rounded">
                          <ChevronDown :size="12" class="transition-transform group-open:rotate-180 text-red-500" />
                          Tampilkan Stack Trace
                        </summary>
                        <div class="mt-2 p-3 bg-red-100 rounded text-xs overflow-x-auto text-red-800 font-mono max-h-80 relative">
                          <pre>{{ entry.trace }}</pre>
                          <BaseButton
                            variant="ghost"
                            size="sm"
                            @click="copyToClipboard(entry.trace, 'fatal-trace-' + index)"
                            class="absolute top-2 right-2 p-1 text-red-500 hover:bg-red-200"
                          >
                            <Copy :size="12" v-if="copiedIndex !== 'fatal-trace-' + index" />
                            <Check :size="12" v-else class="text-green-500" />
                          </BaseButton>
                        </div>
                      </details>
                    </div>
                  </div>
                </template>
                
                <template v-else>
                  <div v-if="getRowErrors(entry).length > 0" class="space-y-2">
                    <div 
                      v-for="(fieldError, fIndex) in getRowErrors(entry)" 
                      :key="fIndex"
                      class="flex items-start gap-2 p-2 bg-white border border-red-100 rounded"
                    >
                      <XCircle :size="14" class="text-red-500 mt-0.5 flex-shrink-0" />
                      <div class="flex-1 min-w-0">
                        <span class="font-medium text-red-800">{{ fieldError.field }}:</span>
                        <span class="text-red-700 ml-1">{{ fieldError.messages.join(', ') }}</span>
                      </div>
                      <BaseButton
                        variant="ghost"
                        size="sm"
                        @click="copyToClipboard(fieldError.field + ': ' + fieldError.messages.join(', '), 'fatal-field-' + index + '-' + fIndex)"
                        class="p-1 text-red-500 hover:bg-red-100"
                      >
                        <Copy :size="12" v-if="copiedIndex !== 'fatal-field-' + index + '-' + fIndex" />
                        <Check :size="12" v-else class="text-green-500" />
                      </BaseButton>
                    </div>
                  </div>
                  <div v-else class="p-3 bg-red-100 rounded text-sm text-red-800 font-mono">
                    {{ formatError(entry) }}
                    <BaseButton
                      variant="ghost"
                      size="sm"
                      @click="copyToClipboard(formatError(entry), 'fatal-err-' + index)"
                      class="ml-2 p-1 text-red-500 hover:bg-red-200"
                    >
                      <Copy :size="12" v-if="copiedIndex !== 'fatal-err-' + index" />
                      <Check :size="12" v-else class="text-green-500" />
                    </BaseButton>
                  </div>
                </template>
              </div>
            </div>
          </div>

          <!-- Error log umum -->
          <div v-else-if="errorLog.length > 0" class="border border-slate-200 rounded-lg p-4">
            <div class="flex items-center justify-between mb-3">
              <span class="text-sm text-slate-600">Error log (status: {{ getStatusLabel(job.status) }})</span>
              <BaseButton
                variant="ghost"
                size="sm"
                @click="copyToClipboard(JSON.stringify(errorLog, null, 2), 'all-errors')"
                class="p-1.5 text-slate-500 hover:bg-slate-100"
              >
                <Copy :size="14" v-if="copiedIndex !== 'all-errors'" />
                <Check :size="14" v-else class="text-green-500" />
              </BaseButton>
            </div>
            <pre class="bg-slate-100 p-4 rounded text-xs overflow-x-auto text-slate-700 font-mono max-h-96">{{ JSON.stringify(errorLog, null, 2) }}</pre>
          </div>

          <!-- Empty state -->
          <div v-else class="text-center py-10 text-slate-500">
            <FileText :size="56" class="mx-auto text-slate-300 mb-3" />
            <p class="text-sm">Tidak ada detail error untuk job ini</p>
            <p class="text-xs text-slate-400 mt-1">Job berstatus {{ getStatusLabel(job.status) }} tanpa error log</p>
          </div>
        </div>
      </div>
    </template>

    <template #footer>
      <BaseButton @click="close" variant="secondary">
        Tutup
      </BaseButton>
    </template>
  </BaseModal>
</template>