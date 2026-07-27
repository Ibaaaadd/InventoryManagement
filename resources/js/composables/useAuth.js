import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';

export function useAuth() {
    const authStore = useAuthStore();

    const user = computed(() => authStore.user);
    const isAuthenticated = computed(() => authStore.isAuthenticated);
    const role = computed(() => authStore.user?.role?.name);

    const hasRole = (roles) => {
        if (!authStore.user) return false;
        const userRole = authStore.user.role?.name;
        if (Array.isArray(roles)) {
            return roles.includes(userRole);
        }
        return userRole === roles;
    };

    const isAdministrator = computed(() => authStore.user?.role?.name === 'Administrator');
    const isManager = computed(() => authStore.user?.role?.name === 'Manager');
    const isStaff = computed(() => authStore.user?.role?.name === 'Staff');

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
