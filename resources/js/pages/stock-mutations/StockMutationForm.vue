<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { Save, X, ArrowLeft, Info } from 'lucide-vue-next';
import { useAuth } from '@/composables/useAuth';
import { useToast } from '@/composables/useToast';
import axios from '@/lib/axios';
import BaseCard from '@/components/ui/BaseCard.vue';
import BaseInput from '@/components/ui/BaseInput.vue';
import BaseSearchableSelect from '@/components/ui/BaseSearchableSelect.vue';
import BaseTextarea from '@/components/ui/BaseTextarea.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import FileUploadPdf from '@/components/ui/FileUploadPdf.vue';

const router = useRouter();
const route = useRoute();
const { user } = useAuth();
const { toastSuccess, toastError } = useToast();

const isEdit = computed(() => !!route.params.id);
const pageTitle = computed(() => isEdit.value ? 'Edit Stock Mutation' : 'Create New Stock Mutation');

const form = ref({
  item_id: '',
  type: '',
  quantity: 0,
  notes: '',
  attachment: null,
  transaction_date: new Date().toISOString().split('T')[0],
});

const errors = ref({});
const loading = ref(false);
const submitting = ref(false);
const hasApprover = ref(true);

const itemOptions = ref([]);

const typeOptions = [
  { value: 'in', label: 'Stock In (Incoming)' },
  { value: 'out', label: 'Stock Out (Outgoing)' },
];

const selectedItem = computed(() => {
  return itemOptions.value.find(opt => opt.value === form.value.item_id);
});

onMounted(async () => {
  if (!user.value?.approver_id) {
    hasApprover.value = false;
    toastError('You do not have an assigned approver. Please contact administrator to set up your approver.');
    router.push('/stock-mutations');
    return;
  }
  
  await fetchItems();
  if (isEdit.value) {
    await fetchMutation();
  }
});

const fetchItems = async () => {
  try {
    const response = await axios.get('/items?all=true');
    const items = response.data.data || response.data;
    itemOptions.value = items.map(item => ({
      value: item.id,
      label: `${item.sku} - ${item.name}`,
      stock: item.stock_quantity,
      unit: item.unit || 'pcs'
    }));
  } catch (error) {
    console.error('Failed to fetch items:', error);
  }
};

const fetchMutation = async () => {
  loading.value = true;
  try {
    const response = await axios.get(`/stock-mutations/${route.params.id}`);
    const mutation = response.data;
    
    form.value = {
      item_id: mutation.item_id,
      type: mutation.type,
      quantity: mutation.quantity,
      notes: mutation.notes || '',
      attachment: null,
      transaction_date: mutation.transaction_date?.split('T')[0] || new Date().toISOString().split('T')[0],
    };

    if (!itemOptions.value.find(opt => opt.value === mutation.item_id)) {
      if (mutation.item) {
        itemOptions.value.unshift({
          value: mutation.item.id,
          label: `${mutation.item.sku} - ${mutation.item.name}`,
        });
      } else if (mutation.item_sku_snapshot && mutation.item_name_snapshot) {
        itemOptions.value.unshift({
          value: mutation.item_id,
          label: `${mutation.item_sku_snapshot} - ${mutation.item_name_snapshot} (Deleted)`,
        });
      }
    }
  } catch (error) {
    console.error('Failed to fetch mutation:', error);
    toastError('Failed to load mutation data');
    router.push('/stock-mutations');
  } finally {
    loading.value = false;
  }
};

const validateForm = () => {
  errors.value = {};

  if (!form.value.item_id) {
    errors.value.item_id = 'Please select an item';
  }

  if (!form.value.type) {
    errors.value.type = 'Please select mutation type';
  }

  if (!form.value.quantity || form.value.quantity <= 0) {
    errors.value.quantity = 'Quantity must be greater than 0';
  }

  if (!form.value.transaction_date) {
    errors.value.transaction_date = 'Transaction date is required';
  }

  if (!isEdit.value && !form.value.attachment) {
    errors.value.attachment = 'Supporting document is required for new mutations';
  }

  return Object.keys(errors.value).length === 0;
};

const handleSubmit = async () => {
  if (!validateForm()) return;

  submitting.value = true;
  try {
    const formData = new FormData();
    formData.append('item_id', form.value.item_id);
    formData.append('type', form.value.type);
    formData.append('quantity', form.value.quantity);
    formData.append('transaction_date', form.value.transaction_date);
    if (form.value.notes) {
      formData.append('notes', form.value.notes);
    }
    if (form.value.attachment) {
      formData.append('attachment', form.value.attachment);
    }

    if (isEdit.value) {
      formData.append('_method', 'PUT');
      await axios.post(`/stock-mutations/${route.params.id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      toastSuccess('Stock mutation updated successfully');
    } else {
      await axios.post('/stock-mutations', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      toastSuccess('Stock mutation created successfully');
    }
    
    router.push('/stock-mutations');
  } catch (error) {
    console.error('Failed to save mutation:', error);
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else if (error.response?.data?.message) {
      errors.value.submit = error.response.data.message;
    } else {
      errors.value.submit = 'Failed to save mutation. Please try again.';
    }
  } finally {
    submitting.value = false;
  }
};

const handleCancel = () => {
  router.push('/stock-mutations');
};

const handleDocumentError = (error) => {
  errors.value.attachment = error;
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
          {{ isEdit ? 'Update stock mutation information' : 'Record a new stock movement with supporting documentation' }}
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
        <div v-if="errors.submit" class="p-3 bg-danger-50 border border-danger-200 rounded-lg">
          <p class="text-sm text-danger-600">{{ errors.submit }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="md:col-span-2">
            <BaseSearchableSelect
              v-model="form.item_id"
              label="Item"
              placeholder="Select an item"
              :options="itemOptions"
              :error="errors.item_id"
              :disabled="loading || isEdit"
              required
            />
            <div v-if="selectedItem && selectedItem.stock !== undefined" class="mt-2 p-3 bg-slate-50 border border-slate-200 rounded-lg">
              <div class="flex items-center justify-between">
                <span class="text-sm text-slate-600">Current Stock</span>
                <span class="text-sm font-semibold" :class="{
                  'text-success-600': selectedItem.stock > 10,
                  'text-warning-600': selectedItem.stock > 0 && selectedItem.stock <= 10,
                  'text-danger-600': selectedItem.stock === 0
                }">
                  {{ selectedItem.stock }} {{ selectedItem.unit || 'pcs' }}
                </span>
              </div>
            </div>
            <p v-else class="mt-1 text-xs text-slate-500">Select the item for this stock mutation</p>
          </div>

          <BaseSearchableSelect
            v-model="form.type"
            label="Mutation Type"
            placeholder="Select type"
            :options="typeOptions"
            :error="errors.type"
            :disabled="loading"
            required
          />

          <BaseInput
            v-model.number="form.quantity"
            type="number"
            label="Quantity"
            placeholder="Enter quantity"
            :error="errors.quantity"
            :disabled="loading"
            required
          />

          <BaseInput
            v-model="form.transaction_date"
            type="date"
            label="Transaction Date"
            :error="errors.transaction_date"
            :disabled="loading"
            required
          />
        </div>

        <BaseTextarea
          v-model="form.notes"
          label="Notes"
          :rows="4"
          placeholder="Enter any additional notes or remarks about this mutation..."
          :disabled="loading"
          hint="Provide context, supplier information, or other relevant details"
        />

        <div class="border-t border-slate-200 pt-6">
          <h3 class="text-lg font-medium text-slate-900 mb-4">Supporting Document</h3>
          <FileUploadPdf
            v-model="form.attachment"
            label="Upload PDF Document"
            :error="errors.attachment"
            :disabled="loading"
            :required="!isEdit"
            :min-size="100"
            :max-size="500"
            @error="handleDocumentError"
          />
          <p class="mt-2 text-xs text-slate-500">
            Upload supporting documentation such as delivery notes, invoices, or authorization forms (PDF only, 100KB-500KB)
          </p>
        </div>

        <div class="flex items-center gap-3 pt-4 border-t border-slate-200">
          <BaseButton
            type="submit"
            :loading="submitting"
            :disabled="submitting"
          >
            <Save :size="18" />
            {{ isEdit ? 'Update Mutation' : 'Create Mutation' }}
          </BaseButton>
          <BaseButton
            type="button"
            variant="ghost"
            @click="handleCancel"
            :disabled="submitting"
          >
            <X :size="18" />
            Cancel
          </BaseButton>
        </div>

        <div v-if="!isEdit" class="bg-info-50 border border-info-200 rounded-lg p-4">
          <div class="flex">
            <Info :size="20" class="text-info-600 mr-3 mt-0.5 flex-shrink-0" />
            <div>
              <h4 class="text-sm font-medium text-info-900">Important Information</h4>
              <p class="mt-1 text-sm text-info-700">
                Once submitted, this mutation will be sent to managers for approval. Ensure all information is accurate and complete before submitting.
              </p>
            </div>
          </div>
        </div>
      </form>
    </BaseCard>
  </div>
</template>
