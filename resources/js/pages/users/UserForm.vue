<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuth } from '@/composables/useAuth';
import BaseCard from '@/components/ui/BaseCard.vue';
import BaseInput from '@/components/ui/BaseInput.vue';
import BaseSelect from '@/components/ui/BaseSelect.vue';
import BaseButton from '@/components/ui/BaseButton.vue';

const router = useRouter();
const route = useRoute();
const { isAdministrator } = useAuth();

const isEdit = computed(() => !!route.params.id);
const pageTitle = computed(() => isEdit.value ? 'Edit User' : 'Create New User');

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: '',
  status: 'active',
});

const errors = ref({});
const loading = ref(false);
const submitting = ref(false);

const roleOptions = [
  { value: 'Administrator', label: 'Administrator' },
  { value: 'Manager', label: 'Manager' },
  { value: 'Staff', label: 'Staff' },
];

const statusOptions = [
  { value: 'active', label: 'Active' },
  { value: 'inactive', label: 'Inactive' },
];

onMounted(async () => {
  if (!isAdministrator.value) {
    router.push('/dashboard');
    return;
  }
  if (isEdit.value) {
    await fetchUser();
  }
});

const fetchUser = async () => {
  loading.value = true;
  try {
    form.value = {
      name: 'John Doe',
      email: 'john@example.com',
      role: 'Administrator',
      status: 'active',
      password: '',
      password_confirmation: '',
    };
  } catch (error) {
    console.error('Failed to fetch user:', error);
  } finally {
    loading.value = false;
  }
};

const validateForm = () => {
  errors.value = {};

  if (!form.value.name) {
    errors.value.name = 'Name is required';
  }

  if (!form.value.email) {
    errors.value.email = 'Email is required';
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
    errors.value.email = 'Please enter a valid email';
  }

  if (!form.value.role) {
    errors.value.role = 'Role is required';
  }

  if (!isEdit.value || form.value.password) {
    if (!form.value.password) {
      errors.value.password = 'Password is required';
    } else if (form.value.password.length < 8) {
      errors.value.password = 'Password must be at least 8 characters';
    }

    if (form.value.password !== form.value.password_confirmation) {
      errors.value.password_confirmation = 'Passwords do not match';
    }
  }

  return Object.keys(errors.value).length === 0;
};

const handleSubmit = async () => {
  if (!validateForm()) return;

  submitting.value = true;
  try {
    console.log('Submitting user:', form.value);
    await new Promise(resolve => setTimeout(resolve, 1000));
    router.push('/users');
  } catch (error) {
    console.error('Failed to save user:', error);
    errors.value.submit = 'Failed to save user. Please try again.';
  } finally {
    submitting.value = false;
  }
};

const handleCancel = () => {
  router.push('/users');
};
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">{{ pageTitle }}</h1>
      <p class="mt-1 text-sm text-gray-600">{{ isEdit ? 'Update user information' : 'Add a new user to the system' }}</p>
    </div>

    <BaseCard :padding="true" :shadow="true">
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <div v-if="errors.submit" class="p-3 bg-red-50 border border-red-200 rounded-lg">
          <p class="text-sm text-red-600">{{ errors.submit }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <BaseInput
            v-model="form.name"
            label="Full Name"
            placeholder="Enter full name"
            :error="errors.name"
            :disabled="loading"
            required
          />

          <BaseInput
            v-model="form.email"
            type="email"
            label="Email Address"
            placeholder="Enter email address"
            :error="errors.email"
            :disabled="loading"
            required
          />

          <BaseSelect
            v-model="form.role"
            label="Role"
            placeholder="Select role"
            :options="roleOptions"
            :error="errors.role"
            :disabled="loading"
            required
          />

          <BaseSelect
            v-model="form.status"
            label="Status"
            placeholder="Select status"
            :options="statusOptions"
            :disabled="loading"
            required
          />
        </div>

        <div class="border-t pt-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            {{ isEdit ? 'Change Password (leave blank to keep current)' : 'Set Password' }}
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <BaseInput
              v-model="form.password"
              type="password"
              label="Password"
              placeholder="Enter password"
              :error="errors.password"
              :disabled="loading"
              :required="!isEdit"
            />

            <BaseInput
              v-model="form.password_confirmation"
              type="password"
              label="Confirm Password"
              placeholder="Confirm password"
              :error="errors.password_confirmation"
              :disabled="loading"
              :required="!isEdit && !!form.password"
            />
          </div>
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
            {{ isEdit ? 'Update User' : 'Create User' }}
          </BaseButton>
        </div>
      </form>
    </BaseCard>
  </div>
</template>
