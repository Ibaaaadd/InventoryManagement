<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { Save, X, ArrowLeft } from 'lucide-vue-next';
import { useAuth } from '@/composables/useAuth';
import BaseCard from '@/components/ui/BaseCard.vue';
import BaseInput from '@/components/ui/BaseInput.vue';
import BaseSearchableSelect from '@/components/ui/BaseSearchableSelect.vue';
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
  // TEMPORARY: role check disabled for UI development phase
  // Backend auth is not active yet, so isAdministrator always returns false
  // Re-enable this check after backend authentication is implemented
  // if (!isAdministrator.value) {
  //   router.push('/dashboard');
  //   return;
  // }
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
          {{ isEdit ? 'Update user information' : 'Add a new user to the system' }}
        </p>
      </div>
    </div>

    <BaseCard>
      <div v-if="loading" class="space-y-4">
        <div class="animate-pulse space-y-4">
          <div class="h-10 bg-slate-200 rounded"></div>
          <div class="h-10 bg-slate-200 rounded"></div>
          <div class="h-10 bg-slate-200 rounded"></div>
        </div>
      </div>

      <form v-else @submit.prevent="handleSubmit" class="space-y-6">
        <div v-if="errors.submit" class="p-3 bg-danger-50 border border-danger-200 rounded-lg">
          <p class="text-sm text-danger-600">{{ errors.submit }}</p>
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

          <BaseSearchableSelect
            v-model="form.role"
            label="Role"
            placeholder="Select role"
            :options="roleOptions"
            :error="errors.role"
            :disabled="loading"
            required
          />

          <BaseSearchableSelect
            v-model="form.status"
            label="Status"
            placeholder="Select status"
            :options="statusOptions"
            :disabled="loading"
            required
          />
        </div>

        <div class="border-t border-slate-200 pt-6">
          <h3 class="text-lg font-medium text-slate-900 mb-4">
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

        <div class="flex items-center gap-3 pt-4 border-t border-slate-200">
          <BaseButton
            type="submit"
            :loading="submitting"
            :disabled="submitting"
          >
            <Save :size="18" />
            {{ isEdit ? 'Update User' : 'Create User' }}
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
      </form>
    </BaseCard>
  </div>
</template>
