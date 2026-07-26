import { defineStore } from 'pinia';

export const useUIStore = defineStore('ui', {
  state: () => ({
    sidebarCollapsed: false,
  }),

  actions: {
    toggleSidebar() {
      this.sidebarCollapsed = !this.sidebarCollapsed;
      localStorage.setItem('sidebar-collapsed', this.sidebarCollapsed);
    },

    setSidebarCollapsed(value) {
      this.sidebarCollapsed = value;
      localStorage.setItem('sidebar-collapsed', this.sidebarCollapsed);
    },

    initSidebarState() {
      const stored = localStorage.getItem('sidebar-collapsed');
      if (stored !== null) {
        this.sidebarCollapsed = stored === 'true';
      }
    },
  },
});
