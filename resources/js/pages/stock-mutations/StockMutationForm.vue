<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import BaseCard from '@/components/ui/BaseCard.vue';
import BaseInput from '@/components/ui/BaseInput.vue';
import BaseSelect from '@/components/ui/BaseSelect.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import FileUploadPdf from '@/components/ui/FileUploadPdf.vue';

const router = useRouter();
const route = useRoute();

const isEdit = computed(() => !!route.params.id);
const pageTitle = computed(() => isEdit.value ? 'Edit Stock Mutation' : 'Create New Stock Mutation');

const form = ref({
  item_id: '',
  type: '',
  quantity: 0,
  notes: '',
  document: null,
  mutation_date: new Date().toISOString().split('T')[0],
});

const errors = ref({});
const loading = ref(false);
const submitting = ref(false);

const itemOptions = ref([
  { value: 1, label: 'ITM001 - Laptop Dell XPS 15' },
  { value: 2, label: 'ITM002 - Mouse Wireless Logitech' },
  { value: 3, label: 'ITM003 - Keyboard Mechanical' },
  { value: 4, label: 'ITM004 - Monitor 27 inch' },
  { value: 5, label: 'ITM005 - USB Cable Type-C' },
]);

const typeOptions = [
  { value: 'IN', label: 'Stock In (Incoming)' },
  { value: 'OUT', label: 'Stock Out (Outgoing)' },
];

onMounted(async () => {
  if (isEdit.value) {
    await fetchMutation();
  }
});

const fetchMutation = async () => {
  loading.value = true;
  try {
    form.value = {
      item_id: 1,
      type: 'IN',
      quantity: 10,
      notes: 'New stock arrival from supplier',
      document: null,
      mutation_date: '2026-07-25',
    };
  } catch (error) {
    console.error('Failed to fetch mutation:', error);
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

  if (!form.value.mutation_date) {
    errors.value.mutation_date = 'Mutation date is required';
  }

  if (!isEdit.value && !form.value.document) {
    errors.value.document = 'Supporting document is required for new mutations';
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
    formData.append('notes', form.value.notes);
    formData.append('mutation_date', form.value.mutation_date);
    if (form.value.document) {
      formData.append('document', form.value.document);
    }

    console.log('Submitting mutation:', form.value);
    await new Promise(resolve => setTimeout(resolve, 1500));
    router.push('/stock-mutations');
  } catch (error) {
    console.error('Failed to save mutation:', error);
    errors.value.submit = 'Failed to save mutation. Please try again.';
  } finally {
    submitting.value = false;
  }
};

const handleCancel = () => {
  router.push('/stock-mutations');
};

const handleDocumentError = (error) => {
  errors.value.document = error;
};
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">{{ pageTitle }}</h1>
      <p class="mt-1 text-sm text-gray-600">
        {{ isEdit ? 'Update stock mutation information' : 'Record a new stock movement with supporting documentation' }}
      </p>
    </div>

    <BaseCard :padding="true" :shadow="true">
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <div v-if="errors.submit" class="p-3 bg-red-50 border border-red-200 rounded-lg">
          <p class="text-sm text-red-600">{{ errors.submit }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="md:col-span-2">
            <BaseSelect
              v-model="form.item_id"
              label="Item"
              placeholder="Select an item"
              :options="itemOptions"
              :error="errors.item_id"
              :disabled="loading || isEdit"
              required
            />
            <p class="mt-1 text-xs text-gray-500">Select the item for this stock mutation</p>
          </div>

          <BaseSelect
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
            v-model="form.mutation_date"
            type="date"
            label="Mutation Date"
            :error="errors.mutation_date"
            :disabled="loading"
            required
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Notes
            <span class="text-gray-500 font-normal">(Optional)</span>
          </label>
          <textarea
            v-model="form.notes"
            rows="4"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            placeholder="Enter any additional notes or remarks about this mutation..."
            :disabled="loading"
          ></textarea>
          <p class="mt-1 text-xs text-gray-500">Provide context, supplier information, or other relevant details</p>
        </div>

        <div class="border-t pt-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Supporting Document</h3>
          <FileUploadPdf
            v-model="form.document"
            label="Upload PDF Document"
            :error="errors.document"
            :disabled="loading"
            :required="!isEdit"
            :min-size="100"
            :max-size="500"
            @error="handleDocumentError"
          />
          <p class="mt-2 text-xs text-gray-500">
            Upload supporting documentation such as delivery notes, invoices, or authorization forms (PDF only, 100KB-500KB)
          </p>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t">
          <BaseButton
            type="button"
            variant="secondary"
            @click="handleCancel"
            :disabled="submitting"
          >
            Cancel
          </BaseButton>
          <BaseButton
            type="submit"
            variant="primary"
            :loading="submitting"
            :disabled="loading || submitting"
          >
            {{ isEdit ? 'Update Mutation' : 'Create Mutation' }}
          </BaseButton>
        </div>

        <div v-if="!isEdit" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div class="flex">
            <svg class="w-5 h-5 text-blue-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
              <h4 class="text-sm font-medium text-blue-900">Important Information</h4>
              <p class="mt-1 text-sm text-blue-700">
                Once submitted, this mutation will be sent to managers for approval. Ensure all information is accurate and complete before submitting.
              </p>
            </div>
          </div>
        </div>
      </form>
    </BaseCard>
  </div>
</template>
