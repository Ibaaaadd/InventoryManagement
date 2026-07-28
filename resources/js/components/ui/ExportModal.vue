<script setup>
import { ref, watch, onMounted } from 'vue';
import { useToast } from '@/composables/useToast';
import BaseModal from './BaseModal.vue';
import BaseButton from './BaseButton.vue';

const props = defineProps({
  modelValue: Boolean,
  model: {
    type: String,
    required: true,
    validator: (value) => ['role', 'user', 'category', 'item', 'stock-mutation'].includes(value),
  },
});

const emit = defineEmits(['update:modelValue', 'close', 'exported']);

const { showToast, toastSuccess } = useToast();

const availableFields = ref([]);
const selectedFields = ref([]);
const loading = ref(false);
const exporting = ref(false);

const close = () => {
  emit('update:modelValue', false);
  emit('close');
};

const getCookie = (name) => {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop().split(';').shift();
  return null;
};

const fetchFields = async () => {
  loading.value = true;
  try {
    const response = await fetch(`/api/export/${props.model}/fields`, {
      credentials: 'include',
    });
    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Gagal memuat field');
    }
    
    availableFields.value = Object.values(data.fields);
    selectedFields.value = availableFields.value.map(f => f.key);
  } catch (error) {
    showToast(error.message, 'error');
  } finally {
    loading.value = false;
  }
};

const toggleField = (fieldKey) => {
  const index = selectedFields.value.indexOf(fieldKey);
  if (index > -1) {
    selectedFields.value.splice(index, 1);
  } else {
    selectedFields.value.push(fieldKey);
  }
};

const selectAll = () => {
  selectedFields.value = availableFields.value.map(f => f.key);
};

const deselectAll = () => {
  selectedFields.value = [];
};

const handleExport = async () => {
  if (selectedFields.value.length === 0) {
    showToast('Pilih minimal 1 field untuk export', 'error');
    return;
  }

  exporting.value = true;
  try {
    const csrfToken = getCookie('XSRF-TOKEN');
    const headers = {
      'Content-Type': 'application/json',
    };
    
    if (csrfToken) {
      headers['X-XSRF-TOKEN'] = decodeURIComponent(csrfToken);
    }

    const response = await fetch(`/api/export/${props.model}`, {
      method: 'POST',
      headers,
      credentials: 'include',
      body: JSON.stringify({ fields: selectedFields.value }),
    });

    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message || 'Export gagal');
    }

    const data = await response.json();
    toastSuccess('Export sedang diproses');
    emit('exported', data.job_id);
    close();
  } catch (error) {
    showToast(error.message, 'error');
  } finally {
    exporting.value = false;
  }
};

onMounted(() => {
  if (props.modelValue) {
    fetchFields();
  }
});

watch(() => props.modelValue, (newValue) => {
  if (newValue && availableFields.value.length === 0) {
    fetchFields();
  }
});
</script>

<template>
  <BaseModal :model-value="modelValue" :title="`Export ${model === 'role' ? 'Role' : 'User'}`" size="md" @update:model-value="$emit('update:modelValue', $event)">
    <div v-if="loading" class="text-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
      <p class="text-slate-600 mt-2">Memuat field...</p>
    </div>

    <div v-else>
      <div class="mb-4 flex gap-2">
        <BaseButton @click="selectAll" size="sm" variant="secondary">Pilih Semua</BaseButton>
        <BaseButton @click="deselectAll" size="sm" variant="secondary">Hapus Semua</BaseButton>
      </div>

      <div class="space-y-2 max-h-96 overflow-y-auto">
        <label
          v-for="field in availableFields"
          :key="field.key"
          class="flex items-center p-3 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer transition-colors"
        >
          <input
            type="checkbox"
            :checked="selectedFields.includes(field.key)"
            @change="toggleField(field.key)"
            class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500"
          />
          <div class="ml-3">
            <div class="text-sm font-medium text-slate-900">{{ field.label }}</div>
          </div>
        </label>
      </div>

      <div class="mt-4 text-sm text-slate-600">
        {{ selectedFields.length }} field dipilih
      </div>
    </div>

    <template #footer>
      <BaseButton @click="close" variant="secondary" :disabled="exporting">Batal</BaseButton>
      <BaseButton @click="handleExport" :disabled="loading || exporting || selectedFields.length === 0">
        {{ exporting ? 'Memproses...' : 'Export' }}
      </BaseButton>
    </template>
  </BaseModal>
</template>
