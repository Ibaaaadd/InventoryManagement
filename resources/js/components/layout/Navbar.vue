<script setup>
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};
</script>

<template>
  <nav class="bg-white border-b border-gray-200">
    <div class="px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <div class="flex items-center">
          <h1 class="text-xl font-bold text-gray-900">Inventory Management System</h1>
        </div>
        
        <div class="flex items-center gap-4">
          <div class="flex items-center gap-2">
            <div class="text-sm">
              <p class="font-medium text-gray-900">{{ authStore.user?.name }}</p>
              <p class="text-gray-500">{{ authStore.user?.role }}</p>
            </div>
            <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center">
              <span class="text-primary-700 font-medium text-sm">
                {{ authStore.user?.name?.charAt(0).toUpperCase() }}
              </span>
            </div>
          </div>
          
          <button
            @click="handleLogout"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            Logout
          </button>
        </div>
      </div>
    </div>
  </nav>
</template>
