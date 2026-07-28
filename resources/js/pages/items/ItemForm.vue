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
import BaseTextarea from '@/components/ui/BaseTextarea.vue';
import BaseSearchableSelect from '@/components/ui/BaseSearchableSelect.vue';
import BaseToggle from '@/components/ui/BaseToggle.vue';
import MetadataBuilder from '@/components/items/MetadataBuilder.vue';
import AuditTrailPanel from '@/components/ui/AuditTrailPanel.vue';

const router = useRouter();
const route = useRoute();
const { toastSuccess } = useToast();

const isEditMode = computed(() => !!route.params.id);
const pageTitle = computed(() => isEditMode.value ? 'Edit Item' : 'Buat Item Baru');

const form = ref({
  name: '',
  category_id: '',
  description: '',
  price: 0,
  stock_quantity: 0,
  metadata: null,
  is_active: true,
});

const errors = ref({});
const loading = ref(false);
const submitting = ref(false);
const itemSku = ref('');
const categories = ref([]);
const loadingCategories = ref(false);

const fetchCategories = async (search = '') => {
  try {
    loadingCategories.value = true;
    const params = {};
    if (search) params.search = search;
    const response = await axios.get('/categories', { params });
    const categoryData = response.data.data || response.data;
    categories.value = categoryData.map(cat => ({
      label: cat.name,
      value: cat.id,
      code: cat.code
    }));
  } catch (error) {
    console.error('Failed to fetch categories:', error);
  } finally {
    loadingCategories.value = false;
  }
};

onMounted(async () => {
  await fetchCategories();
  
  if (isEditMode.value) {
    try {
      loading.value = true;
      const response = await axios.get(`/items/${route.params.id}`);
      const itemData = response.data;
      
      itemSku.value = itemData.sku;
      form.value.name = itemData.name;
      form.value.category_id = itemData.category_id;
      form.value.description = itemData.description;
      form.value.price = itemData.price;
      form.value.stock_quantity = itemData.stock_quantity;
      form.value.metadata = itemData.metadata;
      form.value.is_active = itemData.is_active;
    } catch (error) {
      console.error('Failed to fetch item:', error);
      showError(error.response?.status === 404 ? 'Item tidak ditemukan' : 'Gagal memuat data item');
      router.push({ name: 'ItemList' });
    } finally {
      loading.value = false;
    }
  }
});

const validateForm = () => {
  let isValid = true;
  errors.value = {};

  if (!form.value.name.trim()) {
    errors.value.name = 'Nama item wajib diisi';
    isValid = false;
  }
  
  if (!form.value.category_id) {
    errors.value.category_id = 'Kategori wajib dipilih';
    isValid = false;
  }
  
  if (form.value.price < 0) {
    errors.value.price = 'Harga tidak boleh negatif';
    isValid = false;
  }
  
  if (form.value.stock_quantity < 0) {
    errors.value.stock_quantity = 'Stok tidak boleh negatif';
    isValid = false;
  }

  return isValid;
};

const handleSubmit = async () => {
  if (!validateForm()) return;

  try {
    submitting.value = true;
    
    if (isEditMode.value) {
      await axios.put(`/items/${route.params.id}`, form.value);
      toastSuccess('Item Diperbarui', 'Item berhasil diperbarui');
    } else {
      await axios.post('/items', form.value);
      toastSuccess('Item Dibuat', 'Item baru berhasil dibuat');
    }
    
    router.push({ name: 'ItemList' });
  } catch (error) {
    console.error('Failed to submit item:', error);
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      showError(error.response?.data?.message || 'Gagal menyimpan item. Silakan coba lagi.');
    }
  } finally {
    submitting.value = false;
  }
};

const handleCancel = () => {
  router.push({ name: 'ItemList' });
};
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center gap-4">
      <button @click="handleCancel" class="p-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors">
        <ArrowLeft :size="20" />
      </button>
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ pageTitle }}</h1>
        <p class="text-sm text-slate-600 mt-1">
          {{ isEditMode ? 'Edit informasi item' : 'Buat item baru untuk inventori' }}
        </p>
      </div>
    </div>

    <BaseCard>
      <div v-if="loading" class="space-y-4">
        <div class="animate-pulse space-y-4">
          <div class="h-10 bg-slate-200 rounded"></div>
          <div class="h-10 bg-slate-200 rounded"></div>
          <div class="h-24 bg-slate-200 rounded"></div>
        </div>
      </div>

      <form v-else @submit.prevent="handleSubmit" class="space-y-6">
        <BaseInput v-model="form.name" label="Nama Item" type="text" placeholder="Masukkan nama item" :error="errors.name" :required="true" :disabled="submitting" />

        <BaseSearchableSelect
          v-model="form.category_id"
          label="Kategori"
          placeholder="Pilih kategori"
          :options="categories"
          :loading="loadingCategories"
          :error="errors.category_id"
          :required="true"
          :disabled="submitting || isEditMode"
          :hint="isEditMode ? 'Kategori tidak bisa diubah setelah item dibuat' : ''"
        />

        <BaseInput v-if="isEditMode" :model-value="itemSku" label="SKU" type="text" :disabled="true" hint="SKU di-generate otomatis oleh sistem" />
        <div v-else class="rounded-lg border border-slate-200 p-4 bg-slate-50">
          <p class="text-sm text-slate-600"><strong>SKU:</strong> Akan di-generate otomatis setelah kategori dipilih</p>
        </div>

        <BaseTextarea v-model="form.description" label="Deskripsi" :rows="3" placeholder="Deskripsi item (opsional)" :error="errors.description" :disabled="submitting" />

        <div class="grid grid-cols-2 gap-4">
          <BaseInput v-model.number="form.price" label="Harga" type="number" step="0.01" min="0" placeholder="0.00" :error="errors.price" :required="true" :disabled="submitting" />
          <BaseInput v-model.number="form.stock_quantity" label="Stok" type="number" min="0" placeholder="0" :error="errors.stock_quantity" :required="true" :disabled="submitting" />
        </div>

        <BaseToggle
          v-model="form.is_active"
          label="Status Aktif"
          :disabled="submitting"
          :show-status="true"
        />

        <MetadataBuilder v-model="form.metadata" :disabled="submitting" />

        <div class="flex items-center gap-3 pt-4 border-t border-slate-200">
          <BaseButton type="submit" :loading="submitting" :disabled="submitting">
            <Save :size="18" />
            {{ isEditMode ? 'Update Item' : 'Buat Item' }}
          </BaseButton>
          <BaseButton type="button" variant="ghost" @click="handleCancel" :disabled="submitting">
            <X :size="18" />
            Batal
          </BaseButton>
        </div>
      </form>
    </BaseCard>

    <BaseCard v-if="isEditMode" :padding="true">
      <AuditTrailPanel auditable-type="App\Models\Item" :auditable-id="route.params.id" />
    </BaseCard>
  </div>
</template>
