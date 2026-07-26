<script setup>
import { ref, computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useUIStore } from '@/stores/ui';
import { useRouter, useRoute } from 'vue-router';
import { useClickOutside } from '@/composables/useClickOutside';
import { LogOut, ChevronDown, Menu } from 'lucide-vue-next';

const authStore = useAuthStore();
const uiStore = useUIStore();
const router = useRouter();
const route = useRoute();
const isDropdownOpen = ref(false);
const dropdownRef = ref(null);

const breadcrumbs = computed(() => {
  const paths = route.path.split('/').filter(Boolean);
  const crumbs = [{ name: 'Home', to: '/dashboard' }];
  
  let currentPath = '';
  paths.forEach((path, index) => {
    currentPath += `/${path}`;
    
    const routeName = route.matched[index + 1]?.name;
    let name = path.charAt(0).toUpperCase() + path.slice(1);
    
    if (path === 'stock-mutations') name = 'Stock Mutations';
    else if (path === 'create') name = 'Create';
    else if (path === 'edit') name = 'Edit';
    else if (!isNaN(path)) return;
    
    crumbs.push({ name, to: currentPath });
  });
  
  return crumbs;
});

const userInitials = computed(() => {
  if (!authStore.user?.name) return 'U';
  return authStore.user.name
    .split(' ')
    .map(n => n.charAt(0))
    .slice(0, 2)
    .join('')
    .toUpperCase();
});

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};

const toggleDropdown = () => {
  isDropdownOpen.value = !isDropdownOpen.value;
};

const closeDropdown = () => {
  isDropdownOpen.value = false;
};

useClickOutside(dropdownRef, closeDropdown);
</script>

<template>
  <nav class="bg-white border-b border-slate-200 shadow-sm sticky top-0 z-40">
    <div class="px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <div class="flex items-center gap-4">
          <button
            @click="uiStore.toggleSidebar"
            class="p-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors"
            title="Toggle Sidebar"
          >
            <Menu :size="20" />
          </button>
          
          <nav class="flex items-center gap-2 text-sm">
            <router-link
              v-for="(crumb, index) in breadcrumbs"
              :key="crumb.to"
              :to="crumb.to"
              class="transition-colors"
              :class="[
                index === breadcrumbs.length - 1 
                  ? 'text-slate-900 font-medium' 
                  : 'text-slate-500 hover:text-slate-700'
              ]"
            >
              <span v-if="index > 0" class="text-slate-400 mx-2">/</span>
              {{ crumb.name }}
            </router-link>
          </nav>
        </div>
        
        <div class="flex items-center gap-4">
          <div class="relative" ref="dropdownRef">
            <button
              @click="toggleDropdown"
              class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-50 transition-colors group"
            >
              <div class="text-right hidden sm:block">
                <p class="text-sm font-medium text-slate-900">
                  {{ authStore.user?.name || 'User' }}
                </p>
                <p class="text-xs text-slate-500">
                  {{ authStore.user?.role || 'Guest' }}
                </p>
              </div>
              
              <div class="relative">
                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center shadow-md">
                  <span class="text-white font-semibold text-sm">
                    {{ userInitials }}
                  </span>
                </div>
                <div 
                  v-if="authStore.user?.role === 'Administrator'"
                  class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-warning-500 border-2 border-white rounded-full"
                  title="Administrator"
                />
              </div>
              
              <ChevronDown 
                :size="16" 
                class="text-slate-400 transition-transform"
                :class="{ 'rotate-180': isDropdownOpen }"
              />
            </button>
            
            <transition
              enter-active-class="transition ease-out duration-100"
              enter-from-class="transform opacity-0 scale-95"
              enter-to-class="transform opacity-100 scale-100"
              leave-active-class="transition ease-in duration-75"
              leave-from-class="transform opacity-100 scale-100"
              leave-to-class="transform opacity-0 scale-95"
            >
              <div
                v-if="isDropdownOpen"
                class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-slate-200 py-1"
              >
                <div class="px-4 py-3 border-b border-slate-100">
                  <p class="text-sm font-medium text-slate-900">
                    {{ authStore.user?.name || 'User' }}
                  </p>
                  <p class="text-xs text-slate-500 mt-0.5">
                    {{ authStore.user?.email || 'user@example.com' }}
                  </p>
                </div>
                
                <button
                  @click="handleLogout"
                  class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors"
                >
                  <LogOut :size="16" class="text-slate-400" />
                  <span>Logout</span>
                </button>
              </div>
            </transition>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>
