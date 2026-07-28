<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from '@/lib/axios';
import { showError } from '@/lib/swal';
import { useToast } from '@/composables/useToast';
import { Save, X, ArrowLeft } from 'lucide-vue-next';
import BaseButton from '@/components/ui/BaseButton.vue';
import BaseCard from '@/components/ui/BaseCard.vue';
import BaseInput from '@/components/ui/BaseInput.vue';
import AuditTrailPanel from '@/components/ui/AuditTrailPanel.vue';

const router = useRouter();
const route = useRoute();
const { toastSuccess } = useToast();

const isEditMode = computed(() => !!route.params.id);
const pageTitle = computed(() => isEditMode.value ? 'Edit Kategori' : 'Buat Kategori Baru');

const form = ref({
  name: '',
  code: '',
});

const errors = ref({
  name: '',
  code: '',
});

const loading = ref(false);
const submitting = ref(false);
const categoryName = ref('');
const itemsCount = ref(0);

const isCodeLocked = computed(() => isEditMode.value && itemsCount.value > 0);

onMounted(async () => {
  if (isEditMode.value) {
    try {
      loading.value = true;
      const response = await axios.get(`/categories/${route.params.id}`);
      const categoryData = response.data;
      
      categoryName.value = categoryData.name;
      itemsCount.value = categoryData.items_count || 0;
      
      form.value.name = categoryData.name;
      form.value.code = categoryData.code;
  } catch (error) {
    console.error('Failed to fetch category:', error);
    if (error.response?.status === 404) {
      showError('Kategori tidak ditemukan');
    } else {
      showError('Gagal memuat data kategori');
    }
    router.push({ name: 'CategoryList' });
    } finally {
      loading.value = false;
    }
  }
});

const validateForm = () => {
  let isValid = true;
  errors.value = { name: '', code: '' };

  if (!form.value.name.trim()) {
    errors.value.name = 'Nama kategori wajib diisi';
    isValid = false;
  }

  if (!form.value.code.trim()) {
    errors.value.code = 'Kode kategori wajib diisi';
    isValid = false;
  } else if (!/^[A-Z0-9]+$/.test(form.value.code)) {
    errors.value.code = 'Kode hanya boleh huruf besar dan angka';
    isValid = false;
  }

  return isValid;
};

const handleCodeInput = (event) => {
  form.value.code = event.target.value.toUpperCase();
};

const handleSubmit = async () => {
  if (!validateForm()) return;

  try {
    submitting.value = true;
    
    if (isEditMode.value) {
      await axios.put(`/categories/${route.params.id}`, form.value);
      toastSuccess('Kategori Diperbarui', 'Kategori berhasil diperbarui');
    } else {
      await axios.post('/categories', form.value);
      toastSuccess('Kategori Dibuat', 'Kategori baru berhasil dibuat');
    }
    
    router.push({ name: 'CategoryList' });
  } catch (error) {
    console.error('Failed to submit category:', error);
    
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      showError(error.response?.data?.message || 'Gagal menyimpan kategori. Silakan coba lagi.');
    }
  } finally {
    submitting.value = false;
  }
};

const handleCancel = () => {
  router.push({ name: 'CategoryList' });
};
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center gap-4">
      <button
        @click="handleCancel"
        class="p-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors"
      >
        <ArrowLeft :size="20" />
      </button>
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ pageTitle }}</h1>
        <p class="text-sm text-slate-600 mt-1">
          {{ isEditMode ? 'Edit informasi kategori' : 'Buat kategori baru untuk item' }}
        </p>
      </div>
    </div>

    <BaseCard>
      <div v-if="loading" class="space-y-4">
        <div class="animate-pulse space-y-4">
          <div class="h-10 bg-slate-200 rounded"></div>
          <div class="h-10 bg-slate-200 rounded"></div>
        </div>
      </div>

      <form v-else @submit.prevent="handleSubmit" class="space-y-6">
        <BaseInput
          v-model="form.name"
          label="Nama Kategori"
          type="text"
          placeholder="Masukkan nama kategori (e.g., Elektronik, Furniture)"
          :error="errors.name"
          :required="true"
          :disabled="submitting"
        />

        <div>
          <BaseInput
            :model-value="form.code"
            @input="handleCodeInput"
            label="Kode Kategori"
            type="text"
            placeholder="Masukkan kode kategori (e.g., IT, FRN)"
            :error="errors.code"
            :required="true"
            :disabled="submitting || isCodeLocked"
            :hint="isCodeLocked ? 'Kode tidak bisa diubah karena sudah digunakan oleh item' : 'Hanya huruf besar dan angka, tanpa spasi'"
          />
          <div v-if="isCodeLocked" class="mt-2 p-3 bg-warning-50 border border-warning-200 rounded-lg">
            <p class="text-sm text-warning-800">
              <strong>Perhatian:</strong> Kode kategori tidak bisa diubah karena sudah digunakan oleh {{ itemsCount }} item.
            </p>
          </div>
        </div>

        <div class="flex items-center gap-3 pt-4 border-t border-slate-200">
          <BaseButton
            type="submit"
            :loading="submitting"
            :disabled="submitting"
          >
            <Save :size="18" />
            {{ isEditMode ? 'Update Kategori' : 'Buat Kategori' }}
          </BaseButton>
          <BaseButton
            type="button"
            variant="ghost"
            @click="handleCancel"
            :disabled="submitting"
          >
            <X :size="18" />
            Batal
          </BaseButton>
        </div>
      </form>
    </BaseCard>

    <BaseCard v-if="isEditMode" :padding="true">
      <AuditTrailPanel
        auditable-type="App\Models\Category"
        :auditable-id="route.params.id"
      />
    </BaseCard>
  </div>
</template>
