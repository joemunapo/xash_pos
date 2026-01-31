<template>
  <div class="space-y-6">
    <!-- Toast Notification -->
    <Transition
      enter-active-class="transform ease-out duration-300 transition"
      enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
      enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
      leave-active-class="transition ease-in duration-100"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="toast.show"
        class="fixed top-4 right-4 z-[60] max-w-sm w-full rounded-lg p-4"
        :class="toast.type === 'success' ? 'bg-success-light border border-success' : 'bg-danger-light border border-danger'"
      >
        <div class="flex items-start gap-3">
          <div class="flex-shrink-0">
            <i
              :class="toast.type === 'success' ? 'fas fa-check-circle text-success' : 'fas fa-exclamation-circle text-danger'"
              class="text-lg"
            ></i>
          </div>
          <div class="flex-1 min-w-0">
            <p
              class="text-sm font-medium"
              :class="toast.type === 'success' ? 'text-success' : 'text-danger'"
            >
              {{ toast.title }}
            </p>
            <p
              class="text-sm mt-1"
              :class="toast.type === 'success' ? 'text-success' : 'text-danger'"
            >
              {{ toast.message }}
            </p>
          </div>
          <button
            @click="toast.show = false"
            class="flex-shrink-0"
            :class="toast.type === 'success' ? 'text-success hover:text-success' : 'text-danger hover:text-danger'"
          >
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>
    </Transition>

    <!-- Page Header -->
    <div class="flex items-center justify-between gap-2">
      <div>
        <h1 class="text-lg md:text-2xl font-bold text-gray-800">Cashier Management</h1>
        <p class="text-xs md:text-sm text-gray-600">Manage your branch cashiers</p>
      </div>
      <button
        @click="openAddModal"
        class="px-2 md:px-4 py-1.5 md:py-3 bg-primary text-white rounded-lg hover:bg-primary-dark transition-all flex items-center gap-1 md:gap-2 font-medium text-xs md:text-sm flex-shrink-0"
      >
        <i class="fas fa-plus"></i>
        <span class="hidden md:inline">Add</span> Cashier
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <i class="fas fa-spinner fa-spin text-4xl text-primary"></i>
    </div>

    <!-- Empty State -->
    <div v-else-if="cashiers.length === 0" class="bg-white rounded-lg border border-gray-100 p-8 md:p-12 text-center">
      <i class="fas fa-users text-4xl md:text-5xl text-gray-300 mb-4"></i>
      <p class="text-base md:text-lg text-gray-500 mb-4">No cashiers found</p>
      <button
        @click="openAddModal"
        class="px-3 md:px-4 py-1.5 md:py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition inline-flex items-center gap-2 text-xs md:text-sm"
      >
        <i class="fas fa-plus"></i>
        Add Your First Cashier
      </button>
    </div>

    <!-- Cashiers Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
      <div
        v-for="cashier in cashiers"
        :key="cashier.id"
        class="bg-white rounded-lg border border-gray-100 transition p-4 md:p-6"
      >
        <div class="flex items-start gap-4">
          <div class="w-14 h-14 bg-primary-light rounded-full flex items-center justify-center flex-shrink-0">
            <i class="fas fa-user text-primary text-xl"></i>
          </div>
          <div class="flex-1 min-w-0">
            <h3 class="font-semibold text-gray-800 truncate">{{ cashier.name }}</h3>
            <p class="text-sm text-gray-500 truncate">{{ cashier.email }}</p>
            <p class="text-sm text-gray-500">{{ cashier.phone_number }}</p>
          </div>
        </div>

        <div class="mt-4 pt-4 border-t border-gray-100">
          <div class="flex items-center justify-between mb-3">
            <span class="text-sm text-gray-600">Total Sales</span>
            <span class="text-sm font-semibold text-gray-800">{{ cashier.total_sales || 0 }}</span>
          </div>
          <div class="flex items-center justify-between mb-3">
            <span
              class="px-2 py-1 text-xs font-medium rounded-full"
              :class="cashier.is_active ? 'status-success' : 'status-failed'"
            >
              {{ cashier.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>
          <div class="flex items-center gap-2">
            <button
              @click="openEditModal(cashier)"
              class="flex-1 text-xs px-3 py-1.5 rounded-lg transition font-medium bg-primary-light text-primary hover:bg-primary hover:text-white"
            >
              <i class="fas fa-edit mr-1"></i>Edit
            </button>
            <button
              @click="confirmToggleStatus(cashier)"
              class="flex-1 text-xs px-3 py-1.5 rounded-lg transition font-medium"
              :class="cashier.is_active
                ? 'bg-danger-light text-danger hover:bg-danger hover:text-white'
                : 'bg-success-light text-success hover:bg-success hover:text-white'"
            >
              {{ cashier.is_active ? 'Deactivate' : 'Activate' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Cashier Modal -->
    <div
      v-if="showAddModal"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
      @click.self="closeAddModal"
    >
      <div class="bg-white rounded-lg  max-w-md w-full">
        <!-- Modal Header -->
        <div class="p-6 border-b border-gray-100">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 bg-primary-light rounded-lg flex items-center justify-center">
                <i class="fas fa-user-plus text-primary text-xl"></i>
              </div>
              <div>
                <h2 class="text-xl font-bold text-gray-800">Add New Cashier</h2>
                <p class="text-sm text-gray-500">Create a new cashier account</p>
              </div>
            </div>
            <button @click="closeAddModal" class="text-gray-400 hover:text-gray-600 p-2">
              <i class="fas fa-times text-xl"></i>
            </button>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submitAddCashier" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
            <input
              v-model="addForm.name"
              type="text"
              required
              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-primary focus:bg-white focus:border-primary transition-all"
              placeholder="John Doe"
            />
            <p v-if="formErrors.name" class="text-xs text-red-600 mt-1">{{ formErrors.name }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
            <input
              v-model="addForm.email"
              type="email"
              required
              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-primary focus:bg-white focus:border-primary transition-all"
              placeholder="john@example.com"
            />
            <p v-if="formErrors.email" class="text-xs text-red-600 mt-1">{{ formErrors.email }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
            <input
              v-model="addForm.phone_number"
              type="tel"
              required
              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-primary focus:bg-white focus:border-primary transition-all"
              placeholder="+1234567890"
            />
            <p v-if="formErrors.phone_number" class="text-xs text-red-600 mt-1">{{ formErrors.phone_number }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">4-Digit PIN *</label>
            <input
              v-model="addForm.pin"
              type="text"
              required
              maxlength="4"
              pattern="[0-9]{4}"
              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-green-500 focus:bg-white focus:border-green-500 transition-all text-center text-lg font-semibold tracking-widest"
              placeholder="****"
            />
            <p class="text-xs text-gray-500 mt-1">4-digit numeric PIN for login</p>
            <p v-if="formErrors.pin" class="text-xs text-red-600 mt-1">{{ formErrors.pin }}</p>
          </div>

          <!-- Error Display -->
          <div v-if="addError" class="p-3 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-sm text-red-600">{{ addError }}</p>
          </div>

          <!-- Actions -->
          <div class="flex gap-3 pt-4">
            <button
              type="button"
              @click="closeAddModal"
              class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="adding"
              class="flex-1 px-4 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark transition font-medium flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <i v-if="adding" class="fas fa-spinner fa-spin"></i>
              {{ adding ? 'Creating...' : 'Create Cashier' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Cashier Modal -->
    <div
      v-if="showEditModal"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
      @click.self="closeEditModal"
    >
      <div class="bg-white rounded-lg  max-w-md w-full">
        <!-- Modal Header -->
        <div class="p-6 border-b border-gray-100">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 bg-primary-light rounded-lg flex items-center justify-center">
                <i class="fas fa-user-edit text-primary text-xl"></i>
              </div>
              <div>
                <h2 class="text-xl font-bold text-gray-800">Edit Cashier</h2>
                <p class="text-sm text-gray-500">Update cashier information</p>
              </div>
            </div>
            <button @click="closeEditModal" class="text-gray-400 hover:text-gray-600 p-2">
              <i class="fas fa-times text-xl"></i>
            </button>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submitEditCashier" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
            <input
              v-model="editForm.name"
              type="text"
              required
              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-primary focus:bg-white focus:border-primary transition-all"
              placeholder="John Doe"
            />
            <p v-if="editFormErrors.name" class="text-xs text-red-600 mt-1">{{ editFormErrors.name }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
            <input
              v-model="editForm.email"
              type="email"
              required
              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-primary focus:bg-white focus:border-primary transition-all"
              placeholder="john@example.com"
            />
            <p v-if="editFormErrors.email" class="text-xs text-red-600 mt-1">{{ editFormErrors.email }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
            <input
              v-model="editForm.phone_number"
              type="tel"
              required
              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-primary focus:bg-white focus:border-primary transition-all"
              placeholder="+1234567890"
            />
            <p v-if="editFormErrors.phone_number" class="text-xs text-red-600 mt-1">{{ editFormErrors.phone_number }}</p>
          </div>

          <div class="pt-2 border-t border-gray-100">
            <div class="flex items-center justify-between mb-2">
              <label class="block text-sm font-medium text-gray-700">Change PIN (Optional)</label>
              <button
                type="button"
                @click="showPinField = !showPinField"
                class="text-xs text-primary hover:text-primary-dark font-medium"
              >
                {{ showPinField ? 'Cancel' : 'Change PIN' }}
              </button>
            </div>
            <div v-if="showPinField">
              <input
                v-model="editForm.pin"
                type="text"
                maxlength="4"
                pattern="[0-9]{4}"
                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-green-500 focus:bg-white focus:border-green-500 transition-all text-center text-lg font-semibold tracking-widest"
                placeholder="****"
              />
              <p class="text-xs text-gray-500 mt-1">Enter new 4-digit PIN</p>
              <p v-if="editFormErrors.pin" class="text-xs text-red-600 mt-1">{{ editFormErrors.pin }}</p>
            </div>
          </div>

          <!-- Error Display -->
          <div v-if="editError" class="p-3 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-sm text-red-600">{{ editError }}</p>
          </div>

          <!-- Actions -->
          <div class="flex gap-3 pt-4">
            <button
              type="button"
              @click="closeEditModal"
              class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="editing"
              class="flex-1 px-4 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark transition font-medium flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <i v-if="editing" class="fas fa-spinner fa-spin"></i>
              {{ editing ? 'Updating...' : 'Update Cashier' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- PIN Success Modal -->
    <div
      v-if="showPinModal"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
      @click.self="closePinModal"
    >
      <div class="bg-white rounded-lg  max-w-md w-full">
        <div class="p-6 text-center">
          <div class="w-16 h-16 bg-success-light rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-check text-success text-2xl"></i>
          </div>
          <h2 class="text-2xl font-bold text-gray-800 mb-2">
            {{ pinModalMode === 'create' ? 'Cashier Created!' : 'PIN Updated!' }}
          </h2>
          <p class="text-gray-600 mb-6">
            {{ pinModalMode === 'create'
              ? `${newCashierName} has been added successfully`
              : `PIN for ${newCashierName} has been updated` }}
          </p>

          <div class="p-4 bg-success-light border border-success rounded-lg mb-6">
            <p class="text-sm text-gray-600 mb-2">{{ pinModalMode === 'create' ? 'Login PIN:' : 'New PIN:' }}</p>
            <p class="text-3xl font-bold text-success tracking-widest">{{ newCashierPin }}</p>
            <p class="text-xs text-gray-500 mt-2">Share this PIN with the cashier securely</p>
          </div>

          <button
            @click="closePinModal"
            class="w-full px-4 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark transition font-medium"
          >
            Got it
          </button>
        </div>
      </div>
    </div>

    <!-- Confirm Toggle Status Modal -->
    <div
      v-if="showConfirmModal"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
      @click.self="closeConfirmModal"
    >
      <div class="bg-white rounded-lg  max-w-md w-full">
        <div class="p-6">
          <div class="flex items-center gap-3 mb-4">
            <div
              class="w-12 h-12 rounded-lg flex items-center justify-center"
              :class="cashierToToggle?.is_active ? 'bg-danger-light' : 'bg-success-light'"
            >
              <i
                class="text-xl"
                :class="cashierToToggle?.is_active ? 'fas fa-ban text-danger' : 'fas fa-check text-success'"
              ></i>
            </div>
            <div>
              <h2 class="text-xl font-bold text-gray-800">
                {{ cashierToToggle?.is_active ? 'Deactivate' : 'Activate' }} Cashier?
              </h2>
            </div>
          </div>

          <p class="text-gray-600 mb-6">
            Are you sure you want to {{ cashierToToggle?.is_active ? 'deactivate' : 'activate' }}
            <strong>{{ cashierToToggle?.name }}</strong>?
            <span v-if="cashierToToggle?.is_active" class="block mt-2 text-sm text-red-600">
              They will not be able to log in or make sales.
            </span>
          </p>

          <div class="flex gap-3">
            <button
              @click="closeConfirmModal"
              class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium"
            >
              Cancel
            </button>
            <button
              @click="executeToggleStatus"
              :disabled="toggling"
              class="flex-1 px-4 py-3 text-white rounded-lg transition font-medium flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
              :class="cashierToToggle?.is_active
                ? 'bg-danger hover:bg-danger'
                : 'bg-success hover:bg-success'"
            >
              <i v-if="toggling" class="fas fa-spinner fa-spin"></i>
              {{ toggling ? 'Processing...' : 'Confirm' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { fetchWrapper } from '@/helpers';

const baseUrl = import.meta.env.VITE_API_URL;
const loading = ref(true);
const cashiers = ref([]);

// Add cashier modal
const showAddModal = ref(false);
const adding = ref(false);
const addError = ref('');
const addForm = ref({
  name: '',
  email: '',
  phone_number: '',
  pin: ''
});
const formErrors = ref({});

// Edit cashier modal
const showEditModal = ref(false);
const editing = ref(false);
const editError = ref('');
const editForm = ref({
  id: null,
  name: '',
  email: '',
  phone_number: '',
  pin: ''
});
const editFormErrors = ref({});
const showPinField = ref(false);

// PIN success modal
const showPinModal = ref(false);
const newCashierPin = ref('');
const newCashierName = ref('');
const pinModalMode = ref('create'); // 'create' or 'update'

// Confirm toggle modal
const showConfirmModal = ref(false);
const cashierToToggle = ref(null);
const toggling = ref(false);

// Toast notification
const toast = ref({
  show: false,
  type: 'success',
  title: '',
  message: ''
});

function showToast(type, title, message) {
  toast.value = { show: true, type, title, message };
  setTimeout(() => {
    toast.value.show = false;
  }, 4000);
}

async function fetchCashiers() {
  loading.value = true;

  try {
    const response = await fetchWrapper.get(`${baseUrl}/manager/users`);
    cashiers.value = response;
  } catch (error) {
    console.error('Failed to fetch cashiers:', error);
    showToast('error', 'Error', 'Failed to load cashiers');
  } finally {
    loading.value = false;
  }
}

// Add cashier functions
function openAddModal() {
  addForm.value = {
    name: '',
    email: '',
    phone_number: '',
    pin: ''
  };
  formErrors.value = {};
  addError.value = '';
  showAddModal.value = true;
}

function closeAddModal() {
  showAddModal.value = false;
}

async function submitAddCashier() {
  if (!addForm.value.name || !addForm.value.email || !addForm.value.phone_number || !addForm.value.pin) {
    addError.value = 'Please fill in all required fields';
    return;
  }

  if (addForm.value.pin.length !== 4 || !/^[0-9]{4}$/.test(addForm.value.pin)) {
    addError.value = 'PIN must be exactly 4 digits';
    return;
  }

  adding.value = true;
  addError.value = '';
  formErrors.value = {};

  try {
    const response = await fetchWrapper.post(`${baseUrl}/manager/users`, addForm.value);

    newCashierPin.value = response.pin;
    newCashierName.value = addForm.value.name;
    pinModalMode.value = 'create';

    closeAddModal();
    showPinModal.value = true;

    // Refresh cashiers list
    await fetchCashiers();
  } catch (error) {
    console.error('Failed to create cashier:', error);

    // Handle validation errors
    if (error.errors) {
      formErrors.value = {
        name: error.errors.name?.[0],
        email: error.errors.email?.[0],
        phone_number: error.errors.phone_number?.[0],
        pin: error.errors.pin?.[0]
      };
    }

    addError.value = error.message || 'Failed to create cashier. Please try again.';
    showToast('error', 'Error', 'Failed to create cashier');
  } finally {
    adding.value = false;
  }
}

function closePinModal() {
  showPinModal.value = false;
  newCashierPin.value = '';
  newCashierName.value = '';
  pinModalMode.value = 'create';
}

// Edit cashier functions
function openEditModal(cashier) {
  editForm.value = {
    id: cashier.id,
    name: cashier.name,
    email: cashier.email,
    phone_number: cashier.phone_number,
    pin: ''
  };
  editFormErrors.value = {};
  editError.value = '';
  showPinField.value = false;
  showEditModal.value = true;
}

function closeEditModal() {
  showEditModal.value = false;
  showPinField.value = false;
}

async function submitEditCashier() {
  if (!editForm.value.name || !editForm.value.email || !editForm.value.phone_number) {
    editError.value = 'Please fill in all required fields';
    return;
  }

  if (showPinField.value && editForm.value.pin) {
    if (editForm.value.pin.length !== 4 || !/^[0-9]{4}$/.test(editForm.value.pin)) {
      editError.value = 'PIN must be exactly 4 digits';
      return;
    }
  }

  editing.value = true;
  editError.value = '';
  editFormErrors.value = {};

  const cashierName = editForm.value.name;
  const payload = {
    name: editForm.value.name,
    email: editForm.value.email,
    phone_number: editForm.value.phone_number
  };

  // Only include PIN if it's being changed
  if (showPinField.value && editForm.value.pin) {
    payload.pin = editForm.value.pin;
  }

  try {
    const response = await fetchWrapper.put(`${baseUrl}/manager/users/${editForm.value.id}`, payload);

    closeEditModal();

    // If PIN was changed, show the new PIN
    if (response.pin) {
      newCashierPin.value = response.pin;
      newCashierName.value = cashierName;
      pinModalMode.value = 'update';
      showPinModal.value = true;
    } else {
      showToast('success', 'Success', 'Cashier updated successfully');
    }

    // Refresh cashiers list
    await fetchCashiers();
  } catch (error) {
    console.error('Failed to update cashier:', error);

    // Handle validation errors
    if (error.errors) {
      editFormErrors.value = {
        name: error.errors.name?.[0],
        email: error.errors.email?.[0],
        phone_number: error.errors.phone_number?.[0],
        pin: error.errors.pin?.[0]
      };
    }

    editError.value = error.message || 'Failed to update cashier. Please try again.';
    showToast('error', 'Error', 'Failed to update cashier');
  } finally {
    editing.value = false;
  }
}

// Toggle status functions
function confirmToggleStatus(cashier) {
  cashierToToggle.value = cashier;
  showConfirmModal.value = true;
}

function closeConfirmModal() {
  showConfirmModal.value = false;
  cashierToToggle.value = null;
}

async function executeToggleStatus() {
  if (!cashierToToggle.value) return;

  toggling.value = true;
  const cashierName = cashierToToggle.value.name;
  const wasActive = cashierToToggle.value.is_active;

  try {
    const response = await fetchWrapper.post(`${baseUrl}/manager/users/${cashierToToggle.value.id}/toggle-status`);

    closeConfirmModal();
    showToast('success', 'Success', response.message);

    // Refresh cashiers list
    await fetchCashiers();
  } catch (error) {
    console.error('Failed to toggle status:', error);
    showToast('error', 'Error', error.message || 'Failed to update cashier status');
  } finally {
    toggling.value = false;
  }
}

onMounted(() => {
  fetchCashiers();
});
</script>
