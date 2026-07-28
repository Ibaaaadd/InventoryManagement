<script setup>
import { computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useUIStore } from '@/stores/ui';
import { useAuth } from '@/composables/useAuth';
import { 
  LayoutDashboard, 
  Package, 
  ArrowRightLeft, 
  Users,
  ShieldCheck,
  FolderSync,
  FolderTree,
} from 'lucide-vue-next';

const route = useRoute();
const uiStore = useUIStore();
const { isAdministrator } = useAuth();

const logoUrl = '/image/logo.png';

const navigation = computed(() => [
  {
    section: 'MASTER DATA',
    items: [
      {
        name: 'Dashboard',
        to: '/dashboard',
        icon: LayoutDashboard,
      },
      {
        name: 'Categories',
        to: '/categories',
        icon: FolderTree,
      },
      {
        name: 'Items',
        to: '/items',
        icon: Package,
      },
    ],
  },
  {
    section: 'TRANSAKSI',
    items: [
      {
        name: 'Stock Mutations',
        to: '/stock-mutations',
        icon: ArrowRightLeft,
      },
    ],
  },
{
      section: 'ADMINISTRASI',
      items: [
        {
          name: 'Role Management',
          to: '/roles',
          icon: ShieldCheck,
        },
        {
          name: 'Users',
          to: '/users',
          icon: Users,
          adminOnly: true,
        },
      ],
    },
  {
    section: 'HISTORY',
    items: [
      {
        name: 'Export Import',
        to: '/export-import-history',
        icon: FolderSync,
      },
    ],
  },
]);

const visibleItems = computed(() => {
  return navigation.value.map(group => ({
    ...group,
    items: group.items.filter(item => !item.adminOnly || isAdministrator.value)
  })).filter(group => group.items.length > 0);
});

const isActive = (path) => {
  return route.path === path || route.path.startsWith(path + '/');
};

onMounted(() => {
  uiStore.initSidebarState();
});
</script>

<template>
  <aside 
    class="fixed top-0 left-0 bg-slate-900 h-screen flex flex-col transition-all duration-300 ease-in-out z-40"
    :class="uiStore.sidebarCollapsed ? 'w-20' : 'w-64'"
  >
    <div class="px-4 py-4">
      <div v-show="!uiStore.sidebarCollapsed" class="flex items-center gap-3">
        <img :src="logoUrl" alt="Logo" class="h-10 w-auto flex-shrink-0" />
        <div class="min-w-0 flex-1">
          <h2 class="text-lg font-bold text-white tracking-tight truncate">IMS</h2>
          <p class="text-xs font-bold text-slate-400 truncate">Inventory Management</p>
        </div>
      </div>
      <div v-show="uiStore.sidebarCollapsed" class="flex justify-center">
        <img :src="logoUrl" alt="Logo" class="h-10 w-auto" />
      </div>
    </div>

    <nav class="flex-1 px-3 space-y-6 overflow-y-auto">
      <div v-for="group in visibleItems" :key="group.section" class="space-y-1">
        <div 
          v-show="!uiStore.sidebarCollapsed" 
          class="px-3 py-2 text-[10px] font-semibold text-slate-500 uppercase tracking-wider"
        >
          {{ group.section }}
        </div>
        
        <router-link
          v-for="item in group.items"
          :key="item.name"
          :to="item.to"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 group relative"
          :class="{
            'bg-primary-600 text-white shadow-lg shadow-primary-600/50': isActive(item.to),
            'text-slate-300 hover:bg-slate-800 hover:text-white': !isActive(item.to),
          }"
        >
          <div 
            v-if="isActive(item.to)" 
            class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-white rounded-r-full"
          />
          <component :is="item.icon" :size="20" class="flex-shrink-0" />
          <span 
            v-show="!uiStore.sidebarCollapsed" 
            class="transition-opacity duration-200"
          >
            {{ item.name }}
          </span>
          
          <div 
            v-if="uiStore.sidebarCollapsed"
            class="absolute left-full ml-2 px-2 py-1 bg-slate-800 text-white text-xs rounded shadow-lg opacity-0 pointer-events-none group-hover:opacity-100 transition-opacity whitespace-nowrap z-50"
          >
            {{ item.name }}
          </div>
        </router-link>
      </div>
    </nav>
  </aside>
</template>
