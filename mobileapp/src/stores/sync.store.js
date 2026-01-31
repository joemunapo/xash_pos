import { ref, computed, watch } from 'vue';
import { defineStore } from 'pinia';
import { db } from '@/utils/indexeddb';
import { useNetworkStore } from './network.store';
import { useOfflineStore } from './offline.store';
import { useAlertStore } from './alert.store';
import { fetchWrapper } from '@/helpers/fetch-wrapper';

const baseUrl = import.meta.env.VITE_API_URL;

export const useSyncStore = defineStore('sync', () => {
    // State
    const pendingSales = ref([]);
    const syncInProgress = ref(false);
    const syncErrors = ref([]);
    const lastSyncTime = ref(null);
    const autoSyncEnabled = ref(true);
    const syncedCount = ref(0);
    const failedCount = ref(0);

    // Load last sync time from localStorage
    const storedSyncTime = localStorage.getItem('lastSyncTime');
    if (storedSyncTime) {
        lastSyncTime.value = new Date(storedSyncTime);
    }

    // Getters
    const hasPendingSales = computed(() => pendingSales.value.length > 0);
    const pendingSalesCount = computed(() => pendingSales.value.length);
    const totalPendingAmount = computed(() => {
        return pendingSales.value.reduce((sum, sale) => sum + parseFloat(sale.total_amount || 0), 0);
    });

    // Actions
    async function addPendingSale(saleData) {
        try {
            // Add to IndexedDB
            await db.pending_sales.add({
                ...saleData,
                attempts: 0,
                created_at: saleData.created_at || new Date().toISOString(),
                temp_id: saleData.temp_receipt_number
            });

            // Add to state
            pendingSales.value.push(saleData);

            // Log the action
            await db.sync_log.add({
                action: 'sale_queued',
                timestamp: new Date().toISOString(),
                details: {
                    temp_receipt: saleData.temp_receipt_number,
                    amount: saleData.total_amount
                }
            });

            return true;
        } catch (error) {
            return false;
        }
    }

    async function getPendingSales() {
        try {
            const sales = await db.pending_sales.toArray();
            pendingSales.value = sales;
            return sales;
        } catch (error) {
            return [];
        }
    }

    async function syncPendingSales() {
        const networkStore = useNetworkStore();
        const alertStore = useAlertStore();
        const offlineStore = useOfflineStore();

        if (!networkStore.isOnline) {
            return false;
        }

        if (syncInProgress.value) {
            return false;
        }

        if (pendingSales.value.length === 0) {
            return true;
        }

        syncInProgress.value = true;
        syncedCount.value = 0;
        failedCount.value = 0;
        syncErrors.value = [];

        const salesToSync = [...pendingSales.value];

        for (const sale of salesToSync) {
            const success = await syncSingleSale(sale.temp_receipt_number);
            if (success) {
                syncedCount.value++;
            } else {
                failedCount.value++;
            }
        }

        syncInProgress.value = false;
        lastSyncTime.value = new Date();
        localStorage.setItem('lastSyncTime', lastSyncTime.value.toISOString());

        // Show notification
        if (syncedCount.value > 0) {
            alertStore.success(`${syncedCount.value} sale(s) synced successfully`);
        }

        if (failedCount.value > 0) {
            alertStore.error(`${failedCount.value} sale(s) failed to sync. Will retry later.`);
        }

        // Clear optimistic stock changes for synced sales
        if (syncedCount.value > 0) {
            offlineStore.clearOptimisticStockChanges();
            // Refresh product data
            await offlineStore.syncCachedData();
        }

        return failedCount.value === 0;
    }

    async function syncSingleSale(tempId) {
        const networkStore = useNetworkStore();

        if (!networkStore.isOnline) {
            return false;
        }

        try {
            // Get sale from IndexedDB
            const sale = await db.pending_sales.get(tempId);
            if (!sale) {
                return false;
            }

            // Prepare sale data for API
            const saleData = {
                items: sale.items,
                payment_method: sale.payment_method,
                amount_paid: sale.amount_paid,
                customer_id: sale.customer_id || null,
                discount_amount: sale.discount_amount || 0,
                notes: sale.notes || null
            };

            // Send to API
            const response = await fetchWrapper.post(`${baseUrl}/pos/sales`, saleData);

            if (response && response.sale) {
                // Store synced sale
                await db.synced_sales.add({
                    sale_id: response.sale.id,
                    receipt_number: response.sale.receipt_number,
                    temp_receipt: tempId,
                    synced_at: new Date().toISOString(),
                    sale_data: response.sale
                });

                // Remove from pending
                await db.pending_sales.delete(tempId);

                // Update state
                pendingSales.value = pendingSales.value.filter(s => s.temp_receipt_number !== tempId);

                // Log success
                await db.sync_log.add({
                    action: 'sale_synced',
                    timestamp: new Date().toISOString(),
                    details: {
                        temp_receipt: tempId,
                        real_receipt: response.sale.receipt_number,
                        sale_id: response.sale.id
                    }
                });

                return true;
            }

            return false;
        } catch (error) {
            // Increment attempt count
            try {
                const sale = await db.pending_sales.get(tempId);
                if (sale) {
                    await db.pending_sales.update(tempId, {
                        attempts: (sale.attempts || 0) + 1,
                        last_error: error.message || 'Unknown error'
                    });
                }

                // Log error
                await db.sync_log.add({
                    action: 'sale_sync_failed',
                    timestamp: new Date().toISOString(),
                    error_message: error.message || 'Unknown error',
                    details: {
                        temp_receipt: tempId
                    }
                });

                syncErrors.value.push({
                    tempId,
                    error: error.message || 'Unknown error'
                });
            } catch (logError) {
                // Silently fail
            }

            return false;
        }
    }

    async function removePendingSale(tempId) {
        try {
            await db.pending_sales.delete(tempId);
            pendingSales.value = pendingSales.value.filter(s => s.temp_receipt_number !== tempId);
            return true;
        } catch (error) {
            return false;
        }
    }

    async function handleSyncError(tempId, error) {
        try {
            const sale = await db.pending_sales.get(tempId);
            if (sale) {
                await db.pending_sales.update(tempId, {
                    attempts: (sale.attempts || 0) + 1,
                    last_error: error.message || 'Unknown error'
                });
            }

            await db.sync_log.add({
                action: 'sale_sync_failed',
                timestamp: new Date().toISOString(),
                error_message: error.message || 'Unknown error',
                details: {
                    temp_receipt: tempId
                }
            });
        } catch (logError) {
            // Silently fail
        }
    }

    function startAutoSync() {
        const networkStore = useNetworkStore();

        // Watch for network status changes
        watch(
            () => networkStore.isOnline,
            async (isOnline) => {
                if (isOnline && pendingSales.value.length > 0 && !syncInProgress.value) {
                    await syncPendingSales();
                }
            }
        );

        // Also listen to custom network event
        window.addEventListener('network-status-changed', async (event) => {
            if (event.detail.isOnline && pendingSales.value.length > 0 && !syncInProgress.value) {
                setTimeout(() => syncPendingSales(), 2000); // Delay 2 seconds to ensure connection is stable
            }
        });

        // Periodic sync check every 5 minutes
        if (autoSyncEnabled.value) {
            setInterval(async () => {
                if (networkStore.isOnline && pendingSales.value.length > 0 && !syncInProgress.value) {
                    await syncPendingSales();
                }
            }, 5 * 60 * 1000); // 5 minutes
        }
    }

    async function getSyncedSaleByTempReceipt(tempReceipt) {
        try {
            const syncedSales = await db.synced_sales.toArray();
            return syncedSales.find(s => s.temp_receipt === tempReceipt);
        } catch (error) {
            return null;
        }
    }

    async function clearSyncedSales() {
        try {
            await db.synced_sales.clear();
        } catch (error) {
            // Silently fail
        }
    }

    return {
        // State
        pendingSales,
        syncInProgress,
        syncErrors,
        lastSyncTime,
        autoSyncEnabled,
        syncedCount,
        failedCount,

        // Getters
        hasPendingSales,
        pendingSalesCount,
        totalPendingAmount,

        // Actions
        addPendingSale,
        getPendingSales,
        syncPendingSales,
        syncSingleSale,
        removePendingSale,
        handleSyncError,
        startAutoSync,
        getSyncedSaleByTempReceipt,
        clearSyncedSales
    };
});
