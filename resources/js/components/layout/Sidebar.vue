<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { useAuth } from '@/composables/useAuth';

const route = useRoute();
const { isAdministrator, isManager, isStaff } = useAuth();

const navigation = computed(() => {
  const items = [
    {
      name: 'Dashboard',
      to: '/dashboard',
      icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
      roles: ['Administrator', 'Manager', 'Staff'],
    },
    {
      name: 'Items',
      to: '/items',
      icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
      roles: ['Administrator', 'Manager', 'Staff'],
    },
    {
      name: 'Stock Mutations',
      to: '/stock-mutations',
      icon: 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
      roles: ['Administrator', 'Manager', 'Staff'],
    },
    {
      name: 'Users',
      to: '/users',
      icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
      roles: ['Administrator'],
    },
  ];

  return items;
});

const isActive = (path) => {
  return route.path === path || route.path.startsWith(path + '/');
};
</script>

<template>
  <aside class="w-64 bg-gray-900 min-h-screen">
    <div class="px-6 py-8">
      <h2 class="text-xl font-bold text-white">IMS</h2>
      <p class="text-sm text-gray-400 mt-1">Inventory Management</p>
    </div>

    <nav class="px-4 space-y-2">
      <router-link
        v-for="item in navigation"
        :key="item.name"
        :to="item.to"
        class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-colors"
        :class="{
          'bg-primary-600 text-white': isActive(item.to),
          'text-gray-300 hover:bg-gray-800 hover:text-white': !isActive(item.to),
        }"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
        </svg>
        {{ item.name }}
      </router-link>
    </nav>

    <div class="px-4 mt-8">
      <div class="px-4 py-3 bg-gray-800 rounded-lg">
        <p class="text-xs text-gray-400 mb-2">Quick Actions</p>
        <router-link
          to="/stock-mutations/create"
          class="flex items-center gap-2 text-sm text-gray-300 hover:text-white transition-colors"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          New Mutation
        </router-link>
        <router-link
          to="/items/create"
          class="flex items-center gap-2 mt-2 text-sm text-gray-300 hover:text-white transition-colors"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          New Item
        </router-link>
        <router-link
          to="/users/create"
          class="flex items-center gap-2 mt-2 text-sm text-gray-300 hover:text-white transition-colors"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          New User
        </router-link>
      </div>
    </div>

    <div class="absolute bottom-0 left-0 right-0 px-4 py-6">
      <div class="px-4 py-3 bg-gray-800 rounded-lg">
        <p class="text-xs text-gray-400">System Status</p>
        <div class="flex items-center gap-2 mt-2">
          <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
          <span class="text-sm text-gray-300">All Systems Operational</span>
        </div>
      </div>
    </div>
  </aside>
</template>
