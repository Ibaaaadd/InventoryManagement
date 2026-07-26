import { ref } from 'vue';

const toasts = ref([]);

let nextId = 1;

export function useToast() {
  const showToast = ({ type = 'info', title, message = '', duration = 3000 }) => {
    const id = nextId++;
    const toast = {
      id,
      type,
      title,
      message,
      duration,
      createdAt: Date.now(),
    };

    toasts.value.push(toast);

    if (duration > 0) {
      setTimeout(() => {
        dismissToast(id);
      }, duration);
    }

    return id;
  };

  const dismissToast = (id) => {
    const index = toasts.value.findIndex(t => t.id === id);
    if (index > -1) {
      toasts.value.splice(index, 1);
    }
  };

  const toastSuccess = (title, message = '', duration = 3000) => {
    return showToast({ type: 'success', title, message, duration });
  };

  const toastError = (title, message = '', duration = 4000) => {
    return showToast({ type: 'error', title, message, duration });
  };

  const toastWarning = (title, message = '', duration = 3500) => {
    return showToast({ type: 'warning', title, message, duration });
  };

  const toastInfo = (title, message = '', duration = 3000) => {
    return showToast({ type: 'info', title, message, duration });
  };

  return {
    toasts,
    showToast,
    dismissToast,
    toastSuccess,
    toastError,
    toastWarning,
    toastInfo,
  };
}
