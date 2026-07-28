<script setup>
import { ref, computed } from 'vue';
import { useToast } from '@/composables/useToast';
import BaseModal from './BaseModal.vue';
import BaseButton from './BaseButton.vue';
import BaseSelect from './BaseSelect.vue';

const props = defineProps({
  modelValue: Boolean,
  model: {
    type: String,
    required: true,
    validator: (value) => ['role', 'user', 'category', 'item'].includes(value),
  },
});

const emit = defineEmits(['update:modelValue', 'close', 'imported']);

const { showToast } = useToast();

const step = ref(1);
const file = ref(null);
const tempFilePath = ref('');
const headers = ref([]);
const mapping = ref({});
const availableFields = ref([]);
const loading = ref(false);
const uploading = ref(false);
const confirming = ref(false);

const canProceed = computed(() => {
  if (step.value === 1) {
    return file.value !== null;
  }
  return Object.values(mapping.value).some(field => field !== null && field !== '');
});

const close = () => {
  resetModal();
  emit('update:modelValue', false);
  emit('close');
};

const getCookie = (name) => {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop().split(';').shift();
  return null;
};

const resetModal = () => {
  step.value = 1;
  file.value = null;
  tempFilePath.value = '';
  headers.value = [];
  mapping.value = {};
  availableFields.value = [];
  loading.value = false;
  uploading.value = false;
  confirming.value = false;
};

const handleFileChange = (event) => {
  const selectedFile = event.target.files[0];
  if (selectedFile) {
    file.value = selectedFile;
  }
};

const handleUpload = async () => {
  if (!file.value) return;

  uploading.value = true;
  const formData = new FormData();
  formData.append('file', file.value);

  try {
    const csrfToken = getCookie('XSRF-TOKEN');
    const requestHeaders = {};
    
    if (csrfToken) {
      requestHeaders['X-XSRF-TOKEN'] = decodeURIComponent(csrfToken);
    }

    const response = await fetch(`/api/import/${props.model}/preview`, {
      method: 'POST',
      credentials: 'include',
      headers: requestHeaders,
      body: formData,
    });

    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Upload gagal');
    }

    tempFilePath.value = data.temp_file_path;
    headers.value = data.headers;
    mapping.value = data.auto_mapping;
    availableFields.value = Object.values(data.available_fields);
    
    step.value = 2;
    showToast('File berhasil diupload', 'success');
  } catch (error) {
    showToast(error.message, 'error');
  } finally {
    uploading.value = false;
  }
};

const handleConfirm = async () => {
  confirming.value = true;
  try {
    const csrfToken = getCookie('XSRF-TOKEN');
    const requestHeaders = {
      'Content-Type': 'application/json',
    };
    
    if (csrfToken) {
      requestHeaders['X-XSRF-TOKEN'] = decodeURIComponent(csrfToken);
    }

    const response = await fetch(`/api/import/${props.model}/confirm`, {
      method: 'POST',
      headers: requestHeaders,
      credentials: 'include',
      body: JSON.stringify({
        temp_file_path: tempFilePath.value,
        mapping: mapping.value,
      }),
    });

    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Import gagal');
    }

    showToast('Import sedang diproses', 'success');
    emit('imported', data.job_id);
    close();
  } catch (error) {
    showToast(error.message, 'error');
  } finally {
    confirming.value = false;
  }
};

const getFieldOptions = () => {
  return [
    { value: '', label: '-- Tidak diimport --' },
    ...availableFields.value.map(field => ({
      value: field.key,
      label: field.label,
    })),
  ];
};
</script>

<template>
  <BaseModal 
    :model-value="modelValue" 
    :title="`Import ${model === 'role' ? 'Role' : 'User'}`" 
    size="lg" 
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <div v-if="step === 1" class="space-y-4">
      <div class="text-sm text-slate-600">
        Upload file Excel (.xlsx, .xls, atau .csv) yang berisi data {{ model === 'role' ? 'role' : 'user' }}.
      </div>

      <div class="border-2 border-dashed border-slate-300 rounded-lg p-8 text-center hover:border-blue-400 transition-colors">
        <input
          type="file"
          accept=".xlsx,.xls,.csv"
          @change="handleFileChange"
          class="hidden"
          id="file-upload"
        />
        <label for="file-upload" class="cursor-pointer">
          <div class="text-slate-600 mb-2">
            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
          </div>
          <div class="text-sm font-medium text-slate-900">
            {{ file ? file.name : 'Klik untuk pilih file' }}
          </div>
          <div class="text-xs text-slate-500 mt-1">
            XLSX, XLS, atau CSV (max 10MB)
          </div>
        </label>
      </div>
    </div>

    <div v-else-if="step === 2" class="space-y-4">
      <div class="text-sm text-slate-600">
        Petakan kolom dari file Excel ke field database. Sistem sudah mencocokkan otomatis, silakan koreksi jika perlu.
      </div>

      <div class="max-h-96 overflow-y-auto border border-slate-200 rounded-lg">
        <table class="min-w-full divide-y divide-slate-200">
          <thead class="bg-slate-50 sticky top-0">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                Kolom Excel
              </th>
              <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                Field Database
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-slate-200">
            <tr v-for="header in headers" :key="header">
              <td class="px-4 py-3 text-sm font-medium text-slate-900">
                {{ header }}
              </td>
              <td class="px-4 py-3">
                <select
                  v-model="mapping[header]"
                  class="block w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                >
                  <option value="">-- Tidak diimport --</option>
                  <option v-for="field in availableFields" :key="field.key" :value="field.key">
                    {{ field.label }}
                  </option>
                </select>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <template #footer>
      <BaseButton v-if="step === 2" @click="step = 1" variant="secondary" :disabled="confirming">
        Kembali
      </BaseButton>
      <BaseButton @click="close" variant="secondary" :disabled="uploading || confirming">
        Batal
      </BaseButton>
      <BaseButton 
        v-if="step === 1"
        @click="handleUpload" 
        :disabled="!canProceed || uploading"
      >
        {{ uploading ? 'Mengupload...' : 'Lanjut' }}
      </BaseButton>
      <BaseButton 
        v-else
        @click="handleConfirm" 
        :disabled="!canProceed || confirming"
      >
        {{ confirming ? 'Memproses...' : 'Konfirmasi Import' }}
      </BaseButton>
    </template>
  </BaseModal>
</template>
