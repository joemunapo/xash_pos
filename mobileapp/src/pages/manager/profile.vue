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
        class="fixed top-4 right-4 z-[60] max-w-sm w-full rounded-lg shadow-lg p-4"
        :class="toast.type === 'success' ? 'bg-success-light border border-success' : 'bg-danger-light border border-danger'"
      >
        <div class="flex items-start gap-3">
          <div class="flex-shrink-0">
            <i
              :class="toast.type === 'success' ? 'fas fa-check-circle text-success' : 'fas fa-exclamation-circle text-red-500'"
              class="text-lg"
            ></i>
          </div>
          <div class="flex-1 min-w-0">
            <p
              class="text-sm font-medium"
              :class="toast.type === 'success' ? 'text-success' : 'text-red-800'"
            >
              {{ toast.title }}
            </p>
            <p
              class="text-sm mt-1"
              :class="toast.type === 'success' ? 'text-success' : 'text-red-600'"
            >
              {{ toast.message }}
            </p>
          </div>
          <button
            @click="toast.show = false"
            class="flex-shrink-0"
            :class="toast.type === 'success' ? 'text-success hover:opacity-70' : 'text-red-400 hover:text-red-600'"
          >
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>
    </Transition>

    <!-- Page Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
      <div class="flex items-start gap-4">
        <!-- Avatar -->
        <div class="w-16 h-16 md:w-20 md:h-20 bg-primary-light rounded-xl flex items-center justify-center flex-shrink-0">
          <i class="fas fa-user-circle text-primary text-3xl md:text-4xl"></i>
        </div>
        
        <!-- Info -->
        <div class="flex-1 min-w-0">
          <h1 class="text-xl md:text-2xl font-bold text-gray-800 truncate">{{ user?.name || 'Manager' }}</h1>
          <p class="text-sm text-gray-600 mt-1">{{ user?.email }}</p>
          
          <!-- Badges -->
          <div class="flex flex-wrap gap-2 mt-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-primary-light text-primary text-xs font-medium rounded-full">
              <i class="fas fa-user-tie"></i>
              Manager
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded-full">
              <i class="fas fa-building"></i>
              {{ user?.branch?.name || 'No Branch' }}
            </span>
          </div>
        </div>
      </div>
    </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Account Information Form -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-100 p-6">
          <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-primary-light rounded-lg flex items-center justify-center">
                <i class="fas fa-id-card text-primary"></i>
              </div>
              <h3 class="text-lg font-semibold text-gray-800">Account Information</h3>
            </div>
            <button
              v-if="!isEditing"
              @click="startEditing"
              class="px-4 py-2 text-sm font-medium bg-primary text-white rounded-lg hover:bg-primary-dark transition"
            >
              <i class="fas fa-edit mr-1"></i>Edit
            </button>
          </div>

          <form @submit.prevent="saveProfile" class="space-y-4">
            <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
              <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center border border-gray-200">
                <i class="fas fa-user text-primary"></i>
              </div>
              <div class="flex-1">
                <p class="text-xs text-gray-500 uppercase font-medium mb-1">Full Name</p>
                <input
                  v-if="isEditing"
                  v-model="profileForm.name"
                  type="text"
                  required
                  class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-primary focus:border-primary transition-all font-semibold"
                />
                <p v-else class="font-semibold text-gray-800">{{ user?.name || 'Not set' }}</p>
              </div>
            </div>

            <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
              <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center border border-gray-200">
                <i class="fas fa-envelope text-primary"></i>
              </div>
              <div class="flex-1">
                <p class="text-xs text-gray-500 uppercase font-medium mb-1">Email Address</p>
                <input
                  v-if="isEditing"
                  v-model="profileForm.email"
                  type="email"
                  required
                  class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-primary focus:border-primary transition-all font-semibold"
                />
                <p v-else class="font-semibold text-gray-800">{{ user?.email || 'Not set' }}</p>
              </div>
            </div>

            <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
              <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center border border-gray-200">
                <i class="fas fa-phone text-primary"></i>
              </div>
              <div class="flex-1">
                <p class="text-xs text-gray-500 uppercase font-medium mb-1">Phone Number</p>
                <input
                  v-if="isEditing"
                  v-model="profileForm.phone_number"
                  type="tel"
                  required
                  class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-primary focus:border-primary transition-all font-semibold"
                />
                <p v-else class="font-semibold text-gray-800">{{ user?.phone_number || 'Not set' }}</p>
              </div>
            </div>

            <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
              <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center border border-gray-200">
                <i class="fas fa-building text-primary"></i>
              </div>
              <div class="flex-1">
                <p class="text-xs text-gray-500 uppercase font-medium mb-1">Branch Assignment</p>
                <p class="font-semibold text-gray-800">{{ user?.branch?.name || 'No branch assigned' }}</p>
              </div>
            </div>

            <div v-if="formError" class="p-3 bg-red-50 border border-red-200 rounded-lg">
              <p class="text-sm text-red-600">{{ formError }}</p>
            </div>

            <div v-if="isEditing" class="flex gap-3 pt-4">
              <button
                type="button"
                @click="cancelEditing"
                class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="saving"
                class="flex-1 px-4 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark transition font-medium flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <i v-if="saving" class="fas fa-spinner fa-spin"></i>
                {{ saving ? 'Saving...' : 'Save Changes' }}
              </button>
            </div>
          </form>
        </div>

        <!-- Quick Actions & Security -->
        <div class="space-y-6">
          <!-- Security Settings -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-10 h-10 bg-primary-light rounded-lg flex items-center justify-center">
                <i class="fas fa-shield-alt text-primary"></i>
              </div>
              <h3 class="text-lg font-semibold text-gray-800">Security</h3>
            </div>

            <div class="space-y-3">
              <button
                @click="openChangePinModal"
                class="w-full flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition"
              >
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 bg-primary-light rounded-lg flex items-center justify-center">
                    <i class="fas fa-key text-primary text-sm"></i>
                  </div>
                  <div class="text-left">
                    <p class="text-sm font-medium text-gray-800">Change PIN</p>
                    <p class="text-xs text-gray-500">Update login PIN</p>
                  </div>
                </div>
                <i class="fas fa-chevron-right text-gray-400 text-sm"></i>
              </button>
            </div>
          </div>

          <!-- Logout -->
          <div class="bg-white rounded-lg shadow-sm border border-red-100 p-6">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-power-off text-red-600"></i>
              </div>
              <h3 class="text-lg font-semibold text-red-600">Sign Out</h3>
            </div>

            <button
              @click="confirmLogout"
              class="w-full flex items-center justify-center gap-2 p-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium"
            >
              <i class="fas fa-sign-out-alt"></i>
              Logout
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Change PIN Modal -->
    <div
      v-if="showPinModal"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
      @click.self="closePinModal"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
        <div class="p-6 border-b border-gray-100">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 bg-primary-light rounded-lg flex items-center justify-center">
                <i class="fas fa-key text-primary text-xl"></i>
              </div>
              <div>
                <h2 class="text-xl font-bold text-gray-800">Change PIN</h2>
                <p class="text-sm text-gray-500">Update your login PIN</p>
              </div>
            </div>
            <button @click="closePinModal" class="text-gray-400 hover:text-gray-600 p-2">
              <i class="fas fa-times text-xl"></i>
            </button>
          </div>
        </div>

        <form @submit.prevent="submitPinChange" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Current PIN *</label>
            <input
              v-model="pinForm.current_pin"
              type="password"
              maxlength="4"
              pattern="[0-9]{4}"
              required
              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-primary focus:bg-white focus:border-primary transition-all text-center text-lg font-semibold tracking-widest"
              placeholder="****"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">New PIN *</label>
            <input
              v-model="pinForm.new_pin"
              type="password"
              maxlength="4"
              pattern="[0-9]{4}"
              required
              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-primary focus:bg-white focus:border-primary transition-all text-center text-lg font-semibold tracking-widest"
              placeholder="****"
            />
            <p class="text-xs text-gray-500 mt-1">Must be 4 digits</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New PIN *</label>
            <input
              v-model="pinForm.new_pin_confirmation"
              type="password"
              maxlength="4"
              pattern="[0-9]{4}"
              required
              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-primary focus:bg-white focus:border-primary transition-all text-center text-lg font-semibold tracking-widest"
              placeholder="****"
            />
          </div>

          <div v-if="pinError" class="p-3 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-sm text-red-600">{{ pinError }}</p>
          </div>

          <div class="flex gap-3 pt-4">
            <button
              type="button"
              @click="closePinModal"
              class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="changingPin"
              class="flex-1 px-4 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark transition font-medium flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <i v-if="changingPin" class="fas fa-spinner fa-spin"></i>
              {{ changingPin ? 'Updating...' : 'Update PIN' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div
      v-if="showLogoutModal"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
      @click.self="showLogoutModal = false"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
        <div class="p-6">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
              <i class="fas fa-sign-out-alt text-red-600 text-xl"></i>
            </div>
            <div>
              <h2 class="text-xl font-bold text-gray-800">Confirm Logout</h2>
            </div>
          </div>

          <p class="text-gray-600 mb-6">
            Are you sure you want to logout? You will need to login again to access your account.
          </p>

          <div class="flex gap-3">
            <button
              @click="showLogoutModal = false"
              class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium"
            >
              Cancel
            </button>
            <button
              @click="logout"
              class="flex-1 px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium flex items-center justify-center gap-2"
            >
              <i class="fas fa-sign-out-alt"></i>
              Logout
            </button>
          </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores';
import { fetchWrapper } from '@/helpers';

const router = useRouter();
const authStore = useAuthStore();
const user = computed(() => authStore.user);
const baseUrl = import.meta.env.VITE_API_URL;

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

// Profile form
const isEditing = ref(false);
const saving = ref(false);
const formError = ref('');
const profileForm = ref({
  name: '',
  email: '',
  phone_number: ''
});

function startEditing() {
  profileForm.value = {
    name: user.value?.name || '',
    email: user.value?.email || '',
    phone_number: user.value?.phone_number || ''
  };
  formError.value = '';
  isEditing.value = true;
}

function cancelEditing() {
  isEditing.value = false;
  formError.value = '';
}

async function saveProfile() {
  if (!profileForm.value.name || !profileForm.value.email || !profileForm.value.phone_number) {
    formError.value = 'All fields are required';
    return;
  }

  saving.value = true;
  formError.value = '';

  try {
    const response = await fetchWrapper.put(`${baseUrl}/pos/me`, profileForm.value);

    // Update local auth store
    authStore.user = response.user;
    localStorage.setItem('user', JSON.stringify(response.user));

    isEditing.value = false;
    showToast('success', 'Success', 'Profile updated successfully');
  } catch (error) {
    console.error('Failed to update profile:', error);
    formError.value = error.message || 'Failed to update profile. Please try again.';
    showToast('error', 'Error', 'Failed to update profile');
  } finally {
    saving.value = false;
  }
}

// Change PIN modal
const showPinModal = ref(false);
const pinForm = ref({
  current_pin: '',
  new_pin: '',
  new_pin_confirmation: ''
});
const pinError = ref('');
const changingPin = ref(false);

function openChangePinModal() {
  pinForm.value = {
    current_pin: '',
    new_pin: '',
    new_pin_confirmation: ''
  };
  pinError.value = '';
  showPinModal.value = true;
}

function closePinModal() {
  showPinModal.value = false;
  pinForm.value = {
    current_pin: '',
    new_pin: '',
    new_pin_confirmation: ''
  };
  pinError.value = '';
}

async function submitPinChange() {
  if (!pinForm.value.current_pin || !pinForm.value.new_pin || !pinForm.value.new_pin_confirmation) {
    pinError.value = 'All fields are required';
    return;
  }

  if (pinForm.value.new_pin.length !== 4 || !/^[0-9]{4}$/.test(pinForm.value.new_pin)) {
    pinError.value = 'New PIN must be exactly 4 digits';
    return;
  }

  if (pinForm.value.new_pin !== pinForm.value.new_pin_confirmation) {
    pinError.value = 'New PIN and confirmation do not match';
    return;
  }

  changingPin.value = true;
  pinError.value = '';

  try {
    await fetchWrapper.post(`${baseUrl}/pos/pin/change`, pinForm.value);

    closePinModal();
    showToast('success', 'Success', 'PIN changed successfully');
  } catch (error) {
    console.error('Failed to change PIN:', error);
    pinError.value = error.message || 'Failed to change PIN. Please try again.';
    showToast('error', 'Error', 'Failed to change PIN');
  } finally {
    changingPin.value = false;
  }
}

// Logout
const showLogoutModal = ref(false);

function confirmLogout() {
  showLogoutModal.value = true;
}

async function logout() {
  showLogoutModal.value = false;
  await authStore.logout();
  // Note: authStore.logout() already handles the redirect
}
</script>
