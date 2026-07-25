import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const routes = [
  {
    path: '/',
    name: 'Landing',
    component: () => import('@/pages/Landing.vue'),
    meta: { requiresAuth: false, layout: 'guest' },
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/pages/auth/Login.vue'),
    meta: { requiresAuth: false, layout: 'guest' },
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: () => import('@/pages/dashboard/Dashboard.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/items',
    name: 'ItemList',
    component: () => import('@/pages/items/ItemList.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/items/create',
    name: 'ItemCreate',
    component: () => import('@/pages/items/ItemForm.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/items/:id/edit',
    name: 'ItemEdit',
    component: () => import('@/pages/items/ItemForm.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/stock-mutations',
    name: 'StockMutationList',
    component: () => import('@/pages/stock-mutations/StockMutationList.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/stock-mutations/create',
    name: 'StockMutationCreate',
    component: () => import('@/pages/stock-mutations/StockMutationForm.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/stock-mutations/:id',
    name: 'StockMutationDetail',
    component: () => import('@/pages/stock-mutations/StockMutationDetail.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/users',
    name: 'UserList',
    component: () => import('@/pages/users/UserList.vue'),
    meta: { requiresAuth: true, requiredRoles: ['Administrator'] },
  },
  {
    path: '/users/create',
    name: 'UserCreate',
    component: () => import('@/pages/users/UserForm.vue'),
    meta: { requiresAuth: true, requiredRoles: ['Administrator'] },
  },
  {
    path: '/users/:id/edit',
    name: 'UserEdit',
    component: () => import('@/pages/users/UserForm.vue'),
    meta: { requiresAuth: true, requiredRoles: ['Administrator'] },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to, from, next) => {
  // TEMPORARY: Auth guard dinonaktifkan sementara untuk tahap development UI
  // Aktifkan kembali setelah backend auth (Sanctum) selesai dibuat
  // Uncomment logic di bawah ini untuk mengaktifkan kembali auth guard:
  
  // const authStore = useAuthStore();

  // if (to.meta.requiresAuth && !authStore.isAuthenticated) {
  //   if (to.name !== 'Login') {
  //     next({ name: 'Login', query: { redirect: to.fullPath } });
  //     return;
  //   }
  // }

  // if (to.name === 'Login' && authStore.isAuthenticated) {
  //   next({ name: 'Dashboard' });
  //   return;
  // }

  // if (to.meta.requiredRoles) {
  //   const hasRequiredRole = to.meta.requiredRoles.includes(authStore.user?.role);
  //   if (!hasRequiredRole) {
  //     next({ name: 'Dashboard' });
  //     return;
  //   }
  // }

  next();
});

export default router;
