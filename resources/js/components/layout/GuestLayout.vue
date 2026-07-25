<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: String,
});

const pageTitle = computed(() => props.title || 'Inventory Management System');
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <div class="container mx-auto px-4">
      <header class="py-8 text-center">
        <h1 class="text-3xl font-bold text-gray-900">
          {{ pageTitle }}
        </h1>
      </header>
      
      <main class="pb-12">
        <slot />
      </main>
      
      <footer class="py-6 text-center text-sm text-gray-600">
        <p>&copy; {{ new Date().getFullYear() }} Inventory Management System. All rights reserved.</p>
      </footer>
    </div>
  </div>
</template>
