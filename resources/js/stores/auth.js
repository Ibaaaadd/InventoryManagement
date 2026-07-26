import { defineStore } from 'pinia';
import axios, { getCsrfCookie } from '@/lib/axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        loading: false,
    }),

    getters: {
        isAuthenticated: (state) => !!state.user,
        userRole: (state) => state.user?.role,
    },

    actions: {
        async fetchUser() {
            try {
                this.loading = true;
                const response = await axios.get('/user', { skipAuthErrorHandling: true });
                this.user = response.data.user;
                return response.data.user;
            } catch (error) {
                this.user = null;
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async login(email, password) {
            try {
                this.loading = true;
                await getCsrfCookie();
                const response = await axios.post('/login', { email, password });
                this.user = response.data.user;
                return response.data;
            } catch (error) {
                this.user = null;
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async logout() {
            try {
                this.loading = true;
                await axios.post('/logout');
                this.user = null;
            } catch (error) {
                console.error('Logout error:', error);
            } finally {
                this.loading = false;
            }
        },

        clearUser() {
            this.user = null;
        },
    },
});
