<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-header text-white px-4 py-3 flex items-center sticky top-0 z-40 pt-safe">
      <router-link to="/pos/dashboard" class="p-1 mr-3">
        <i class="fas fa-arrow-left"></i>
      </router-link>
      <h1 class="text-lg font-bold">Profile</h1>
    </div>

    <!-- Profile Card -->
    <div class="px-4 py-6">
      <div class="bg-white rounded-lg shadow p-6 text-center">
        <div class="w-20 h-20 bg-primary-light rounded-full mx-auto flex items-center justify-center mb-4">
          <i class="fas fa-user text-3xl text-primary"></i>
        </div>
        <h2 class="text-xl font-bold text-gray-800">{{ user?.name }}</h2>
        <p class="text-gray-500 capitalize">{{ user?.role }}</p>
        <div class="mt-3 text-sm text-gray-500">
          <p><i class="fas fa-phone mr-2"></i>{{ user?.phone_number }}</p>
          <p v-if="user?.email"><i class="fas fa-envelope mr-2"></i>{{ user?.email }}</p>
        </div>
      </div>
    </div>

    <!-- Branch Info -->
    <div class="px-4 mb-6">
      <h3 class="text-sm font-medium text-gray-500 mb-2">Current Branch</h3>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center">
          <div class="w-10 h-10 bg-primary-light rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-store text-primary"></i>
          </div>
          <div>
            <p class="font-medium text-gray-800">{{ branch?.name || 'No branch assigned' }}</p>
            <p class="text-xs text-gray-500">{{ branch?.code }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="px-4 space-y-3">
      <button
        v-if="branches.length > 1"
        @click="showBranchSwitch = true"
        class="w-full flex items-center justify-between bg-white rounded-lg shadow p-4"
      >
        <div class="flex items-center">
          <i class="fas fa-exchange-alt text-primary mr-3"></i>
          <span class="font-medium">Switch Branch</span>
        </div>
        <i class="fas fa-chevron-right text-gray-400"></i>
      </button>

      <button
        @click="showLogoutModal = true"
        class="w-full flex items-center justify-between bg-white rounded-lg shadow p-4 text-red-600"
      >
        <div class="flex items-center">
          <i class="fas fa-sign-out-alt mr-3"></i>
          <span class="font-medium">Logout</span>
        </div>
        <i class="fas fa-chevron-right text-gray-400"></i>
      </button>
    </div>

    <!-- Logout Confirmation Modal -->
    <Transition name="fade">
      <div v-if="showLogoutModal" class="modal-backdrop">
        <div class="absolute inset-0 bg-overlay" @click="showLogoutModal = false"></div>
        <div class="modal-content">
          <div class="text-center">
            <div class="modal-icon modal-icon-danger">
              <i class="fas fa-sign-out-alt text-xl"></i>
            </div>
            <h3 class="modal-title">Confirm Logout</h3>
            <p class="modal-text">
              Are you sure you want to log out? Any unsaved work may be lost.
            </p>
            <div class="modal-actions">
              <button
                @click="showLogoutModal = false"
                class="btn btn-secondary"
              >
                Cancel
              </button>
              <button
                @click="confirmLogout"
                class="btn btn-danger"
              >
                Yes, Logout
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Branch Switch Modal -->
    <Transition name="fade">
      <div v-if="showBranchSwitch" class="fixed inset-0 z-50">
        <div class="absolute inset-0 bg-overlay" @click="showBranchSwitch = false"></div>
        <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-lg p-4">
          <h2 class="text-lg font-bold mb-4">Select Branch</h2>
          <div class="space-y-2">
            <button
              v-for="b in branches"
              :key="b.id"
              @click="switchBranch(b.id)"
              :class="[
                'w-full flex items-center p-3 rounded-lg border-2 transition',
                b.id === branch?.id ? 'border-primary bg-primary-light' : 'border-gray-200'
              ]"
            >
              <i class="fas fa-store mr-3" :class="b.id === branch?.id ? 'text-primary' : 'text-gray-400'"></i>
              <div class="text-left">
                <p class="font-medium">{{ b.name }}</p>
                <p class="text-xs text-gray-500">{{ b.code }}</p>
              </div>
              <i v-if="b.id === branch?.id" class="fas fa-check text-primary ml-auto"></i>
            </button>
          </div>
          <button
            @click="showBranchSwitch = false"
            class="w-full mt-4 py-3 bg-gray-200 rounded-lg font-medium"
          >
            Cancel
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore, useAlertStore } from '@/stores';
import { fetchWrapper } from '@/helpers';

const authStore = useAuthStore();
const alertStore = useAlertStore();
const baseUrl = import.meta.env.VITE_API_URL;

const branches = ref([]);
const showBranchSwitch = ref(false);
const showLogoutModal = ref(false);

const user = computed(() => authStore.user);
const branch = computed(() => authStore.user?.branch);

async function fetchBranches() {
  try {
    const response = await fetchWrapper.get(`${baseUrl}/pos/branches`);
    branches.value = response.branches || [];
  } catch (error) {
    console.error('Failed to fetch branches:', error);
  }
}

async function switchBranch(branchId) {
  try {
    const response = await fetchWrapper.post(`${baseUrl}/pos/switch-branch`, {
      branch_id: branchId
    });

    // Update user's branch in store
    if (authStore.user) {
      authStore.user.branch = response.branch;
      localStorage.setItem('user', JSON.stringify(authStore.user));
    }

    alertStore.success('Branch switched successfully');
    showBranchSwitch.value = false;
  } catch (error) {
    alertStore.error(error.message || 'Failed to switch branch');
  }
}

async function confirmLogout() {
  showLogoutModal.value = false;
  await authStore.logout();
}

onMounted(() => {
  fetchBranches();
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
