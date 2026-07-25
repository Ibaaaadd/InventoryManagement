import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';

export function useAuth() {
    const authStore = useAuthStore();

    const user = computed(() => authStore.user);
    const isAuthenticated = computed(() => authStore.isAuthenticated);
    const role = computed(() => authStore.user?.role);

    const hasRole = (roles) => {
        if (!authStore.user) return false;
        if (Array.isArray(roles)) {
            return roles.includes(authStore.user.role);
        }
        return authStore.user.role === roles;
    };

    const isAdministrator = computed(() => authStore.user?.role === 'Administrator');
    const isManager = computed(() => authStore.user?.role === 'Manager');
    const isStaff = computed(() => authStore.user?.role === 'Staff');

    const canAccess = (requiredRoles) => {
        if (!requiredRoles) return true;
        if (!authStore.user) return false;
        return hasRole(requiredRoles);
    };

    return {
        user,
        isAuthenticated,
        role,
        hasRole,
        isAdministrator,
        isManager,
        isStaff,
        canAccess,
    };
}
