import { defineStore } from 'pinia';
import Swal from 'sweetalert2';

export const useAlertStore = defineStore({
  id: 'alert',
  state: () => ({}), // No need for state since SweetAlert manages the alerts directly
  actions: {
    success(message) {
      this.showToast(message, 'success');
    },
    error(message) {
      this.showToast(message, 'error');
    },
    warning(message) {
      this.showToast(message, 'warning');
    },
    showToast(message, icon) {
      Swal.fire({
        icon: icon, // 'success', 'error', 'info', etc.
        title: message,
        timer: 5000, // Adjust duration as needed
        toast: true, // Enables toast-style alerts
        position: 'top-right', // Adjust position as needed
        showConfirmButton: false,
      });
    },
    clear() {
      // SweetAlert toasts don't persist in state, so nothing is needed here.
    },
  },
});
