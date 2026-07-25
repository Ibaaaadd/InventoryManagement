<script setup>
import { RouterView } from 'vue-router';
import { onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();

onMounted(async () => {
  try {
    await authStore.fetchUser();
  } catch (error) {
    console.log('Not authenticated');
  }
});
</script>

<template>
  <RouterView />
</template>
