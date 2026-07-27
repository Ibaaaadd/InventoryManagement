import axios from 'axios';
import { useToast } from '@/composables/useToast';
import router from '@/router';

const { toastError } = useToast();

const instance = axios.create({
    baseURL: '/api',
    withCredentials: true,
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    },
});

export const getCsrfCookie = async () => {
    const csrfInstance = axios.create({
        baseURL: '/',
        withCredentials: true,
    });
    return await csrfInstance.get('/sanctum/csrf-cookie');
};

instance.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response) {
            const status = error.response.status;
            const message = error.response.data?.message || 'An error occurred';

            switch (status) {
                case 401:
                    if (!error.config?.skipAuthErrorHandling) {
                        toastError('Authentication Required', 'Your session has expired. Please log in again.');
                        setTimeout(() => {
                            router.push({ name: 'Login' });
                        }, 2000);
                    }
                    break;

                case 403:
                    toastError('Access Denied', 'You do not have permission to perform this action.');
                    break;

                case 419:
                    toastError('CSRF Token Mismatch', 'Your session has expired. Please refresh the page and try again.');
                    break;

                case 422:
                    break;

                case 500:
                case 502:
                case 503:
                case 504:
                    toastError('Server Error', 'A server error occurred. Please try again later.');
                    break;

                default:
                    if (status >= 400) {
                        toastError('Error', message);
                    }
            }
        } else if (error.request) {
            toastError('Network Error', 'Unable to connect to the server. Please check your internet connection.');
        }

        return Promise.reject(error);
    }
);

export default instance;
