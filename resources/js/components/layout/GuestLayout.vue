<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: String,
});

const pageTitle = computed(() => props.title || 'Inventory Management System');
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-primary-50/30">
    <div class="container mx-auto px-4">
      
      <main class="pb-12">
        <router-view />
      </main>
      
      <footer class="py-6 text-center text-sm text-slate-600">
        <p>&copy; {{ new Date().getFullYear() }} Inventory Management System. All rights reserved.</p>
      </footer>
    </div>
  </div>
</template>
