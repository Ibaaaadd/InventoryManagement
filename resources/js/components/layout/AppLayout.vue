<script setup>
import Navbar from '@/components/layout/Navbar.vue';
import Sidebar from '@/components/layout/Sidebar.vue';
</script>

<template>
  <div class="flex min-h-screen bg-slate-50">
    <Sidebar />
    
    <div class="flex-1 flex flex-col min-w-0">
      <Navbar />
      
      <main class="flex-1 overflow-y-auto">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
          <router-view v-slot="{ Component, route }">
            <transition
              mode="out-in"
              enter-active-class="transition-opacity duration-150"
              enter-from-class="opacity-0"
              enter-to-class="opacity-100"
              leave-active-class="transition-opacity duration-100"
              leave-from-class="opacity-100"
              leave-to-class="opacity-0"
            >
              <component :is="Component" :key="route.fullPath" />
            </transition>
          </router-view>
        </div>
      </main>
    </div>
  </div>
</template>
