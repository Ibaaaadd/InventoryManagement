import Swal from 'sweetalert2';

const MySwal = Swal.mixin({
    customClass: {
        popup: 'swal-custom-popup',
        title: 'swal-custom-title',
        htmlContainer: 'swal-custom-text',
        confirmButton: 'swal-custom-confirm',
        cancelButton: 'swal-custom-cancel',
        icon: 'swal-custom-icon',
    },
    buttonsStyling: false,
    showClass: {
        popup: 'swal-show',
        backdrop: 'swal-backdrop-show'
    },
    hideClass: {
        popup: 'swal-hide',
        backdrop: 'swal-backdrop-hide'
    },
});

export const showSuccess = async (message, title = 'Success') => {
    return MySwal.fire({
        icon: 'success',
        title: title,
        html: message,
        confirmButtonText: 'OK',
        timer: 3000,
        timerProgressBar: true,
    });
};

export const showError = async (message, title = 'Error') => {
    return MySwal.fire({
        icon: 'error',
        title: title,
        html: message,
        confirmButtonText: 'OK',
        customClass: {
            confirmButton: 'swal-custom-confirm-danger',
        },
    });
};

export const showConfirm = async (message, title = 'Are you sure?', options = {}) => {
    const result = await MySwal.fire({
        icon: 'warning',
        title: title,
        html: message,
        showCancelButton: true,
        confirmButtonText: options.confirmText || 'Yes, proceed',
        cancelButtonText: options.cancelText || 'Cancel',
        customClass: {
            confirmButton: 'swal-custom-confirm-danger',
            cancelButton: 'swal-custom-cancel',
        },
        reverseButtons: true,
    });
    return result.isConfirmed;
};

export const showToast = (message, type = 'success') => {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        customClass: {
            popup: 'swal-toast-popup',
            title: 'swal-toast-title',
        },
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    return Toast.fire({
        icon: type,
        title: message,
    });
};

export const showWarning = async (message, title = 'Warning') => {
    return MySwal.fire({
        icon: 'warning',
        title: title,
        html: message,
        confirmButtonText: 'OK',
    });
};

export const showInfo = async (message, title = 'Info') => {
    return MySwal.fire({
        icon: 'info',
        title: title,
        html: message,
        confirmButtonText: 'OK',
    });
};

export default MySwal;
