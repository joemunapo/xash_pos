import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import Swal from 'sweetalert2';

export function useFlashMessages() {
    const page = usePage();

    // Watch for flash messages
    watch(
        () => page.props.flash,
        (flash) => {
            if (!flash) return;

            // Success message
            if (flash.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: flash.success,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    background: '#10b981',
                    color: '#ffffff',
                    iconColor: '#ffffff',
                    customClass: {
                        popup: 'colored-toast',
                    },
                });
            }

            // Error message
            if (flash.error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: flash.error,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true,
                    background: '#ef4444',
                    color: '#ffffff',
                    iconColor: '#ffffff',
                    customClass: {
                        popup: 'colored-toast',
                    },
                });
            }

            // Warning message
            if (flash.warning) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning!',
                    text: flash.warning,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3500,
                    timerProgressBar: true,
                    background: '#f59e0b',
                    color: '#ffffff',
                    iconColor: '#ffffff',
                    customClass: {
                        popup: 'colored-toast',
                    },
                });
            }

            // Info message
            if (flash.info) {
                Swal.fire({
                    icon: 'info',
                    title: 'Info',
                    text: flash.info,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    background: '#3b82f6',
                    color: '#ffffff',
                    iconColor: '#ffffff',
                    customClass: {
                        popup: 'colored-toast',
                    },
                });
            }
        },
        { deep: true, immediate: true }
    );
}

// Function to show custom alerts
export function showAlert(options) {
    const defaults = {
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
    };

    return Swal.fire({ ...defaults, ...options });
}

// Confirmation dialog
export function confirmAction(options) {
    const defaults = {
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#ef4444',
        confirmButtonText: 'Yes, proceed!',
        cancelButtonText: 'Cancel',
    };

    return Swal.fire({ ...defaults, ...options });
}
