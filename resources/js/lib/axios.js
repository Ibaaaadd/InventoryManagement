import axios from 'axios';
import { showError } from './swal';
import router from '@/router';

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
                        showError('Your session has expired. Please log in again.', 'Authentication Required');
                        setTimeout(() => {
                            router.push({ name: 'Login' });
                        }, 2000);
                    }
                    break;

                case 403:
                    showError('You do not have permission to perform this action.', 'Access Denied');
                    break;

                case 419:
                    showError('Your session has expired. Please refresh the page and try again.', 'CSRF Token Mismatch');
                    break;

                case 422:
                    break;

                case 500:
                case 502:
                case 503:
                case 504:
                    showError('A server error occurred. Please try again later.', 'Server Error');
                    break;

                default:
                    if (status >= 400) {
                        showError(message, 'Error');
                    }
            }
        } else if (error.request) {
            showError('Unable to connect to the server. Please check your internet connection.', 'Network Error');
        }

        return Promise.reject(error);
    }
);

export default instance;
