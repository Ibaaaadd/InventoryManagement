<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import BaseCard from '@/components/ui/BaseCard.vue';
import BaseInput from '@/components/ui/BaseInput.vue';
import BaseSelect from '@/components/ui/BaseSelect.vue';
import BaseButton from '@/components/ui/BaseButton.vue';

const router = useRouter();
const route = useRoute();

const isEdit = computed(() => !!route.params.id);
const pageTitle = computed(() => isEdit.value ? 'Edit Item' : 'Create New Item');

const form = ref({
  code: '',
  name: '',
  category: '',
  quantity: 0,
  unit: '',
  location: '',
  description: '',
});

const errors = ref({});
const loading = ref(false);
const submitting = ref(false);

const categoryOptions = [
  { value: 'Electronics', label: 'Electronics' },
  { value: 'Accessories', label: 'Accessories' },
  { value: 'Furniture', label: 'Furniture' },
  { value: 'Office Supplies', label: 'Office Supplies' },
  { value: 'Other', label: 'Other' },
];

const unitOptions = [
  { value: 'pcs', label: 'Pieces (pcs)' },
  { value: 'box', label: 'Box' },
  { value: 'kg', label: 'Kilogram (kg)' },
  { value: 'liter', label: 'Liter' },
  { value: 'meter', label: 'Meter' },
];

const locationOptions = [
  { value: 'Warehouse A', label: 'Warehouse A' },
  { value: 'Warehouse B', label: 'Warehouse B' },
  { value: 'Warehouse C', label: 'Warehouse C' },
  { value: 'Storage Room 1', label: 'Storage Room 1' },
  { value: 'Storage Room 2', label: 'Storage Room 2' },
];

onMounted(async () => {
  if (isEdit.value) {
    await fetchItem();
  }
});

const fetchItem = async () => {
  loading.value = true;
  try {
    form.value = {
      code: 'ITM001',
      name: 'Laptop Dell XPS 15',
      category: 'Electronics',
      quantity: 45,
      unit: 'pcs',
      location: 'Warehouse A',
      description: 'High-performance laptop for professional use',
    };
  } catch (error) {
    console.error('Failed to fetch item:', error);
  } finally {
    loading.value = false;
  }
};

const validateForm = () => {
  errors.value = {};

  if (!form.value.code) {
    errors.value.code = 'Item code is required';
  }

  if (!form.value.name) {
    errors.value.name = 'Item name is required';
  }

  if (!form.value.category) {
    errors.value.category = 'Category is required';
  }

  if (!form.value.unit) {
    errors.value.unit = 'Unit is required';
  }

  if (!form.value.location) {
    errors.value.location = 'Location is required';
  }

  if (form.value.quantity < 0) {
    errors.value.quantity = 'Quantity cannot be negative';
  }

  return Object.keys(errors.value).length === 0;
};

const handleSubmit = async () => {
  if (!validateForm()) return;

  submitting.value = true;
  try {
    console.log('Submitting item:', form.value);
    await new Promise(resolve => setTimeout(resolve, 1000));
    router.push('/items');
  } catch (error) {
    console.error('Failed to save item:', error);
    errors.value.submit = 'Failed to save item. Please try again.';
  } finally {
    submitting.value = false;
  }
};

const handleCancel = () => {
  router.push('/items');
};
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">{{ pageTitle }}</h1>
      <p class="mt-1 text-sm text-gray-600">{{ isEdit ? 'Update item information' : 'Add a new item to inventory' }}</p>
    </div>

    <BaseCard :padding="true" :shadow="true">
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <div v-if="errors.submit" class="p-3 bg-red-50 border border-red-200 rounded-lg">
          <p class="text-sm text-red-600">{{ errors.submit }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <BaseInput
            v-model="form.code"
            label="Item Code"
            placeholder="e.g., ITM001"
            :error="errors.code"
            :disabled="loading || isEdit"
            required
          />

          <BaseInput
            v-model="form.name"
            label="Item Name"
            placeholder="Enter item name"
            :error="errors.name"
            :disabled="loading"
            required
          />

          <BaseSelect
            v-model="form.category"
            label="Category"
            placeholder="Select category"
            :options="categoryOptions"
            :error="errors.category"
            :disabled="loading"
            required
          />

          <BaseSelect
            v-model="form.unit"
            label="Unit"
            placeholder="Select unit"
            :options="unitOptions"
            :error="errors.unit"
            :disabled="loading"
            required
          />

          <BaseInput
            v-model.number="form.quantity"
            type="number"
            label="Initial Quantity"
            placeholder="0"
            :error="errors.quantity"
            :disabled="loading"
          />

          <BaseSelect
            v-model="form.location"
            label="Storage Location"
            placeholder="Select location"
            :options="locationOptions"
            :error="errors.location"
            :disabled="loading"
            required
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Description
          </label>
          <textarea
            v-model="form.description"
            rows="4"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            placeholder="Enter item description (optional)"
            :disabled="loading"
          ></textarea>
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
            {{ isEdit ? 'Update Item' : 'Create Item' }}
          </BaseButton>
        </div>
      </form>
    </BaseCard>
  </div>
</template>
