<script setup>
import Navbar from '@/components/layout/Navbar.vue';
import Sidebar from '@/components/layout/Sidebar.vue';
</script>

<template>
  <div class="flex min-h-screen bg-gray-50">
    <Sidebar />
    
    <div class="flex-1 flex flex-col">
      <Navbar />
      
      <main class="flex-1 overflow-y-auto">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
          <router-view />
        </div>
      </main>
    </div>
  </div>
</template>
