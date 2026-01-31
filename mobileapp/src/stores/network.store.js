import { ref, computed } from 'vue';
import { defineStore } from 'pinia';
import { Network } from '@capacitor/network';
import { Capacitor } from '@capacitor/core';

export const useNetworkStore = defineStore('network', () => {
    // State
    const isOnline = ref(true);
    const connectionType = ref('wifi');
    const lastOnlineTime = ref(null);
    const listeners = ref([]);
    const isNative = ref(false);

    // Load last online time from localStorage
    const storedTime = localStorage.getItem('lastOnlineTime');
    if (storedTime) {
        lastOnlineTime.value = new Date(storedTime);
    }

    // Getters
    const isOffline = computed(() => !isOnline.value);
    const connectionStatus = computed(() => isOnline.value ? 'online' : 'offline');

    // Actions
    async function initialize() {
        // Check if running on native platform (iOS/Android) or web
        isNative.value = Capacitor.isNativePlatform();

        if (isNative.value) {
            // Native platform: Use Capacitor Network plugin
            try {
                const status = await Network.getStatus();
                updateConnectionState(status);

                const listener = await Network.addListener('networkStatusChange', (status) => {
                    updateConnectionState(status);
                });

                listeners.value.push(listener);
            } catch (error) {
                isOnline.value = true;
                connectionType.value = 'unknown';
            }
        } else {
            // Web/Browser: Use navigator.onLine
            isOnline.value = navigator.onLine;
            connectionType.value = navigator.onLine ? 'wifi' : 'none';

            // Listen to browser online/offline events
            window.addEventListener('online', handleBrowserOnline);
            window.addEventListener('offline', handleBrowserOffline);
        }
    }

    function handleBrowserOnline() {
        updateConnectionState({ connected: true, connectionType: 'wifi' });
    }

    function handleBrowserOffline() {
        updateConnectionState({ connected: false, connectionType: 'none' });
    }

    function updateConnectionState(status) {
        const wasOnline = isOnline.value;
        isOnline.value = status.connected;
        connectionType.value = status.connectionType || 'unknown';

        // Store timestamp when going online
        if (isOnline.value) {
            lastOnlineTime.value = new Date();
            localStorage.setItem('lastOnlineTime', lastOnlineTime.value.toISOString());
        }

        // Emit custom event for other parts of the app to listen to
        if (wasOnline !== isOnline.value) {
            const event = new CustomEvent('network-status-changed', {
                detail: {
                    isOnline: isOnline.value,
                    connectionType: connectionType.value,
                    timestamp: new Date()
                }
            });
            window.dispatchEvent(event);
        }
    }

    async function checkConnection() {
        try {
            const status = await Network.getStatus();
            updateConnectionState(status);
            return isOnline.value;
        } catch (error) {
            return isOnline.value;
        }
    }

    function setOnline(status) {
        const wasOnline = isOnline.value;
        isOnline.value = status;

        if (status && !wasOnline) {
            lastOnlineTime.value = new Date();
            localStorage.setItem('lastOnlineTime', lastOnlineTime.value.toISOString());
        }

        if (wasOnline !== isOnline.value) {
            const event = new CustomEvent('network-status-changed', {
                detail: {
                    isOnline: isOnline.value,
                    connectionType: connectionType.value,
                    timestamp: new Date()
                }
            });
            window.dispatchEvent(event);
        }
    }

    function cleanup() {
        // Remove Capacitor listeners (native)
        listeners.value.forEach(listener => {
            if (listener && listener.remove) {
                listener.remove();
            }
        });
        listeners.value = [];

        // Remove browser event listeners (web)
        if (!isNative.value) {
            window.removeEventListener('online', handleBrowserOnline);
            window.removeEventListener('offline', handleBrowserOffline);
        }
    }

    return {
        // State
        isOnline,
        connectionType,
        lastOnlineTime,

        // Getters
        isOffline,
        connectionStatus,

        // Actions
        initialize,
        checkConnection,
        setOnline,
        cleanup
    };
});
