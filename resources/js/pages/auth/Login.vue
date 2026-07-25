<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import GuestLayout from '@/components/layout/GuestLayout.vue';
import BaseInput from '@/components/ui/BaseInput.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import BaseCard from '@/components/ui/BaseCard.vue';

const router = useRouter();
const authStore = useAuthStore();

const form = ref({
  email: '',
  password: '',
});

const errors = ref({});
const loading = ref(false);
const errorMessage = ref('');

const validateForm = () => {
  errors.value = {};
  
  if (!form.value.email) {
    errors.value.email = 'Email is required';
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
    errors.value.email = 'Please enter a valid email';
  }
  
  if (!form.value.password) {
    errors.value.password = 'Password is required';
  } else if (form.value.password.length < 6) {
    errors.value.password = 'Password must be at least 6 characters';
  }
  
  return Object.keys(errors.value).length === 0;
};

const handleSubmit = async () => {
  if (!validateForm()) return;
  
  loading.value = true;
  errorMessage.value = '';
  
  try {
    await authStore.login(form.value.email, form.value.password);
    router.push('/dashboard');
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Login failed. Please check your credentials.';
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <GuestLayout title="Login">
    <div class="flex items-center justify-center min-h-[60vh]">
      <div class="w-full max-w-md">
        <BaseCard :padding="true">
          <div class="mb-6 text-center">
            <h2 class="text-2xl font-bold text-gray-900">Welcome Back</h2>
            <p class="mt-2 text-sm text-gray-600">Sign in to your account to continue</p>
          </div>

          <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-sm text-red-600">{{ errorMessage }}</p>
          </div>

          <form @submit.prevent="handleSubmit" class="space-y-4">
            <BaseInput
              v-model="form.email"
              type="email"
              label="Email Address"
              placeholder="Enter your email"
              :error="errors.email"
              :disabled="loading"
              required
            />

            <BaseInput
              v-model="form.password"
              type="password"
              label="Password"
              placeholder="Enter your password"
              :error="errors.password"
              :disabled="loading"
              required
            />

            <div class="flex items-center justify-between">
              <label class="flex items-center">
                <input type="checkbox" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500" />
                <span class="ml-2 text-sm text-gray-600">Remember me</span>
              </label>
              <a href="#" class="text-sm text-primary-600 hover:text-primary-700">
                Forgot password?
              </a>
            </div>

            <BaseButton
              type="submit"
              variant="primary"
              size="lg"
              :loading="loading"
              :disabled="loading"
              class="w-full"
            >
              {{ loading ? 'Signing in...' : 'Sign In' }}
            </BaseButton>
          </form>

          <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
              Don't have an account?
              <a href="#" class="text-primary-600 hover:text-primary-700 font-medium">
                Contact your administrator
              </a>
            </p>
          </div>
        </BaseCard>
      </div>
    </div>
  </GuestLayout>
</template>
