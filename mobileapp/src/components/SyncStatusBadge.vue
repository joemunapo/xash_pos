<template>
    <div v-if="syncStore.pendingSalesCount > 0" class="relative">
        <button @click="showSyncModal = true" class="relative p-2 hover:bg-gray-100 rounded-lg transition-colors">
            <i class="fas fa-sync-alt text-yellow-500 text-lg" :class="{ 'fa-spin': syncStore.syncInProgress }"></i>
            <span
                class="absolute -top-1 -right-1 bg-yellow-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold">
                {{ syncStore.pendingSalesCount }}
            </span>
        </button>

        <Transition name="fade">
            <div v-if="showSyncModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50" @click="showSyncModal = false"></div>
                <div class="relative bg-white rounded-lg p-6 max-w-md w-full shadow-2xl">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Pending Sales</h3>
                        <button @click="showSyncModal = false" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <div class="mb-4">
                        <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
                            <i class="fas fa-info-circle"></i>
                            <span>{{ syncStore.pendingSalesCount }} sale(s) waiting to sync</span>
                        </div>
                        <div class="text-sm text-gray-600">
                            Total: <span class="font-semibold">${{ syncStore.totalPendingAmount.toFixed(2) }}</span>
                        </div>
                    </div>

                    <div v-if="!networkStore.isOnline"
                        class="flex items-start gap-2 p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-700 mb-4">
                        <i class="fas fa-exclamation-triangle mt-0.5"></i>
                        <p>You are offline. Sales will sync automatically when connection is restored.</p>
                    </div>

                    <div v-if="syncStore.syncInProgress"
                        class="flex items-center gap-2 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700 mb-4">
                        <i class="fas fa-spinner fa-spin"></i>
                        <p>Syncing {{ syncStore.syncedCount }}/{{ syncStore.pendingSalesCount }} sales...</p>
                    </div>

                    <div class="max-h-48 overflow-y-auto mb-4">
                        <div v-for="sale in syncStore.pendingSales" :key="sale.temp_receipt_number"
                            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg mb-2">
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900">{{ sale.temp_receipt_number }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ formatDate(sale.created_at) }} • ${{ parseFloat(sale.total_amount).toFixed(2) }}
                                </div>
                            </div>
                            <div v-if="sale.attempts > 0" class="text-xs text-red-500">
                                {{ sale.attempts }} attempt{{ sale.attempts > 1 ? 's' : '' }}
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button @click="showSyncModal = false"
                            class="flex-1 py-2.5 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition-colors">
                            Close
                        </button>
                        <button v-if="networkStore.isOnline" @click="manualSync" :disabled="syncStore.syncInProgress"
                            class="flex-1 py-2.5 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <span v-if="syncStore.syncInProgress">
                                <i class="fas fa-spinner fa-spin mr-1"></i>Syncing...
                            </span>
                            <span v-else>
                                <i class="fas fa-sync-alt mr-1"></i>Sync Now
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useSyncStore } from '@/stores/sync.store';
import { useNetworkStore } from '@/stores/network.store';
import { useAlertStore } from '@/stores/alert.store';

const syncStore = useSyncStore();
const networkStore = useNetworkStore();
const alertStore = useAlertStore();
const showSyncModal = ref(false);

async function manualSync() {
    try {
        await syncStore.syncPendingSales();
        if (syncStore.pendingSalesCount === 0) {
            showSyncModal.value = false;
        }
    } catch (error) {
        alertStore.error('Some sales failed to sync. Will retry automatically.');
    }
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins} min ago`;
    if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;

    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}
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
