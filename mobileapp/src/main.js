import { createApp } from 'vue'
import './style.css'
import App from './App.vue'


import { createPinia } from 'pinia';
import { router } from "@/router";
const pinia = createPinia();

const app = createApp(App)

app
    .use(router)
    .use(pinia)
    .mount('#app')


// Ensure Pinia is initialized before accessing stores
import { useAuthStore } from "@/stores";
import { useNetworkStore } from "@/stores/network.store";
import { useOfflineStore } from "@/stores/offline.store";
import { useSyncStore } from "@/stores/sync.store";
import { initializeDB } from "@/utils/indexeddb";

const authStore = useAuthStore(pinia);
const networkStore = useNetworkStore(pinia);
const offlineStore = useOfflineStore(pinia);
const syncStore = useSyncStore(pinia);

// Initialize offline functionality and validate auth
(async () => {
    try {
        // 1. Initialize IndexedDB
        await initializeDB();

        // 2. Initialize network monitoring
        await networkStore.initialize();

        // 3. Load cached data in the background (only for POS users, not managers)
        const user = authStore.user;
        const isPosUser = user && user.role !== 'manager';

        if (isPosUser) {
            offlineStore.getProducts().catch(() => {});
            offlineStore.getCategories().catch(() => {});

            // 4. Setup auto-sync
            syncStore.startAutoSync();

            // 5. Load pending sales from IndexedDB
            await syncStore.getPendingSales();
        }

    } catch (error) {
        // Silently fail
    }

    // 6. Check auth and navigate
    const isValid = authStore.checkAuth();
    if (!isValid) {
        await router.push('/auth/login');
    }
})();
