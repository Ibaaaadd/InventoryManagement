import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import AppLayout from '@/components/layout/AppLayout.vue';
import GuestLayout from '@/components/layout/GuestLayout.vue';

const routes = [
  {
    path: '/',
    component: GuestLayout,
    children: [
      {
        path: '',
        name: 'Landing',
        component: () => import('@/pages/Landing.vue'),
        meta: { requiresAuth: false },
      },
      {
        path: 'login',
        name: 'Login',
        component: () => import('@/pages/auth/Login.vue'),
        meta: { requiresAuth: false },
      },
    ],
  },
  {
    path: '/',
    component: AppLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('@/pages/dashboard/Dashboard.vue'),
      },
      {
        path: 'categories',
        name: 'CategoryList',
        component: () => import('@/pages/categories/CategoryList.vue'),
      },
      {
        path: 'categories/create',
        name: 'CategoryCreate',
        component: () => import('@/pages/categories/CategoryForm.vue'),
      },
      {
        path: 'categories/:id/edit',
        name: 'CategoryEdit',
        component: () => import('@/pages/categories/CategoryForm.vue'),
      },
      {
        path: 'items',
        name: 'ItemList',
        component: () => import('@/pages/items/ItemList.vue'),
      },
      {
        path: 'items/create',
        name: 'ItemCreate',
        component: () => import('@/pages/items/ItemForm.vue'),
      },
      {
        path: 'items/:id/edit',
        name: 'ItemEdit',
        component: () => import('@/pages/items/ItemForm.vue'),
      },
      {
        path: 'stock-mutations',
        name: 'StockMutationList',
        component: () => import('@/pages/stock-mutations/StockMutationList.vue'),
      },
      {
        path: 'stock-mutations/create',
        name: 'StockMutationCreate',
        component: () => import('@/pages/stock-mutations/StockMutationForm.vue'),
      },
      {
        path: 'stock-mutations/:id/edit',
        name: 'StockMutationEdit',
        component: () => import('@/pages/stock-mutations/StockMutationForm.vue'),
      },
      {
        path: 'stock-mutations/:id',
        name: 'StockMutationDetail',
        component: () => import('@/pages/stock-mutations/StockMutationDetail.vue'),
      },
      {
        path: 'users',
        name: 'UserList',
        component: () => import('@/pages/users/UserList.vue'),
        meta: { requiredRoles: ['Administrator'] },
      },
      {
        path: 'users/create',
        name: 'UserCreate',
        component: () => import('@/pages/users/UserForm.vue'),
        meta: { requiredRoles: ['Administrator'] },
      },
      {
        path: 'users/:id/edit',
        name: 'UserEdit',
        component: () => import('@/pages/users/UserForm.vue'),
        meta: { requiredRoles: ['Administrator'] },
      },
      {
        path: 'roles',
        name: 'RoleList',
        component: () => import('@/pages/roles/RoleList.vue'),
      },
      {
        path: 'roles/create',
        name: 'RoleCreate',
        component: () => import('@/pages/roles/RoleForm.vue'),
      },
      {
        path: 'roles/:id/edit',
        name: 'RoleEdit',
        component: () => import('@/pages/roles/RoleForm.vue'),
      },
      {
        path: 'export-import-history',
        name: 'ExportImportHistory',
        component: () => import('@/pages/ExportImportHistory.vue'),
      },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();

  if (!authStore.isAuthenticated && authStore.user === null) {
    try {
      await authStore.fetchUser();
    } catch (error) {
      // User not authenticated
    }
  }

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    if (to.name !== 'Login') {
      next({ name: 'Login', query: { redirect: to.fullPath } });
      return;
    }
  }

  if (to.name === 'Login' && authStore.isAuthenticated) {
    next({ name: 'Dashboard' });
    return;
  }

  if (to.meta.requiredRoles) {
    const hasRequiredRole = to.meta.requiredRoles.includes(authStore.user?.role?.name);
    if (!hasRequiredRole) {
      next({ name: 'Dashboard' });
      return;
    }
  }

  next();
});

export default router;
