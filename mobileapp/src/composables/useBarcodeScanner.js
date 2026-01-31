import { ref } from 'vue';
import { Capacitor } from '@capacitor/core';
import { BarcodeScanner, BarcodeFormat } from '@capacitor-mlkit/barcode-scanning';
import { useAlertStore } from '@/stores';
import { SunmiScanner } from '../plugins/sunmiScanner.ts';

const isAndroid = Capacitor.getPlatform() === 'android';
const isNative = Capacitor.isNativePlatform();

// Module-level cache for scanner state (shared across all instances)
let cachedSupportCheck = null;
let cachedPermissionStatus = null;
let googleModuleInstalled = false;
let initializationPromise = null;

export function useBarcodeScanner() {
    const isScanning = ref(false);
    const isSupported = ref(false);
    const isReady = ref(false);
    const isSunmiDevice = ref(false);
    const sunmiListenerActive = ref(false);
    const alertStore = useAlertStore();

    // Callback for when barcode is scanned via Sunmi hardware
    let onSunmiScanCallback = null;
    let sunmiListener = null;

    // Check if running on Sunmi device and setup hardware scanner
    async function initSunmiScanner() {
        // Only available on Android native platform
        if (!isNative || !isAndroid) {
            console.log('[BarcodeScanner] Sunmi scanner only available on Android');
            return false;
        }

        try {
            const result = await SunmiScanner.isSunmiDevice();
            isSunmiDevice.value = result.isSunmi;
            console.log('[BarcodeScanner] Is Sunmi device:', result.isSunmi);
            return result.isSunmi;
        } catch (error) {
            console.log('[BarcodeScanner] Error checking Sunmi device:', error.message || error);
            return false;
        }
    }

    // Start listening for Sunmi hardware scanner
    async function startSunmiListener(callback) {
        if (!isSunmiDevice.value || sunmiListenerActive.value) return;
        if (!isNative || !isAndroid) return;

        try {
            // Store callback
            onSunmiScanCallback = callback;

            // Add listener for scan events
            sunmiListener = await SunmiScanner.addListener('onBarcodeScanned', (event) => {
                console.log('[BarcodeScanner] Sunmi hardware scan:', event.barcode);
                if (onSunmiScanCallback && event.barcode) {
                    onSunmiScanCallback(event.barcode);
                }
            });

            // Start listening for broadcasts
            await SunmiScanner.startListening();
            sunmiListenerActive.value = true;
            console.log('[BarcodeScanner] Sunmi hardware scanner listener started');
        } catch (error) {
            console.log('[BarcodeScanner] Error starting Sunmi listener:', error.message || error);
        }
    }

    // Stop listening for Sunmi hardware scanner
    async function stopSunmiListener() {
        if (!sunmiListenerActive.value) return;
        if (!isNative || !isAndroid) return;

        try {
            await SunmiScanner.stopListening();
            if (sunmiListener) {
                await sunmiListener.remove();
                sunmiListener = null;
            }
            onSunmiScanCallback = null;
            sunmiListenerActive.value = false;
            console.log('[BarcodeScanner] Sunmi hardware scanner listener stopped');
        } catch (error) {
            console.log('[BarcodeScanner] Error stopping Sunmi listener:', error.message || error);
        }
    }

    // Check if ML Kit barcode scanning is supported (cached)
    async function checkSupport() {
        // Return cached result if available
        if (cachedSupportCheck !== null) {
            isSupported.value = cachedSupportCheck;
            return cachedSupportCheck;
        }

        try {
            const result = await BarcodeScanner.isSupported();
            cachedSupportCheck = result.supported;
            isSupported.value = result.supported;
            return result.supported;
        } catch (error) {
            console.error('[BarcodeScanner] Error checking ML Kit support:', error);
            cachedSupportCheck = false;
            isSupported.value = false;
            return false;
        }
    }

    // Request camera permission (cached if already granted)
    async function requestPermission() {
        // If we already have cached granted permission, skip the check
        if (cachedPermissionStatus === 'granted') {
            return true;
        }

        try {
            const status = await BarcodeScanner.checkPermissions();
            cachedPermissionStatus = status.camera;

            if (status.camera === 'granted') {
                return true;
            }

            if (status.camera === 'denied') {
                alertStore.error('Camera permission is required for barcode scanning. Please enable it in settings.');
                return false;
            }

            // Request permission
            const result = await BarcodeScanner.requestPermissions();
            cachedPermissionStatus = result.camera;
            return result.camera === 'granted';
        } catch (error) {
            console.error('[BarcodeScanner] Error requesting camera permission:', error);
            alertStore.error('Failed to request camera permission');
            return false;
        }
    }

    // Install Google Barcode Scanner Module (required for Android, cached)
    async function installGoogleBarcodeModule() {
        // Skip if already installed
        if (googleModuleInstalled) {
            return;
        }

        try {
            const result = await BarcodeScanner.isGoogleBarcodeScannerModuleAvailable();
            if (result.available) {
                googleModuleInstalled = true;
                return;
            }

            console.log('[BarcodeScanner] Installing Google Barcode Scanner module...');
            await BarcodeScanner.installGoogleBarcodeScannerModule();
            googleModuleInstalled = true;
            console.log('[BarcodeScanner] Google Barcode Scanner module installed');
        } catch (error) {
            console.error('[BarcodeScanner] Error installing Google Barcode Scanner module:', error);
            // Continue anyway - might work on iOS or if already installed
        }
    }

    // Initialize scanner (call early to pre-warm, runs in background)
    async function initialize() {
        // Only initialize once
        if (initializationPromise) {
            return initializationPromise;
        }

        initializationPromise = (async () => {
            console.log('[BarcodeScanner] Pre-initializing scanner...');
            const startTime = Date.now();

            try {
                // Run all checks in parallel
                const [supported, permissionResult] = await Promise.all([
                    checkSupport(),
                    BarcodeScanner.checkPermissions().then(r => {
                        cachedPermissionStatus = r.camera;
                        return r.camera;
                    }).catch(() => null),
                ]);

                // Install Google module in the background (Android only)
                if (supported && isAndroid) {
                    installGoogleBarcodeModule().catch(() => {});
                }

                isReady.value = supported && cachedPermissionStatus === 'granted';
                console.log('[BarcodeScanner] Pre-initialization complete in', Date.now() - startTime, 'ms');
                return isReady.value;
            } catch (error) {
                console.error('[BarcodeScanner] Pre-initialization error:', error);
                return false;
            }
        })();

        return initializationPromise;
    }

    // Scan barcode using camera (ML Kit)
    async function scan() {
        try {
            // If already pre-initialized and ready, skip checks
            if (isReady.value && cachedSupportCheck && cachedPermissionStatus === 'granted') {
                console.log('[BarcodeScanner] Using cached initialization, opening scanner immediately');
            } else {
                // Check support first (uses cache if available)
                const supported = await checkSupport();
                if (!supported) {
                    alertStore.error('Camera barcode scanning is not supported on this device');
                    return null;
                }

                // Request permission (uses cache if already granted)
                const hasPermission = await requestPermission();
                if (!hasPermission) {
                    return null;
                }

                // Install Google module if needed (Android, uses cache)
                await installGoogleBarcodeModule();
            }

            isScanning.value = true;

            // Add scanning class to body for CSS styling
            document.body.classList.add('barcode-scanning');

            // Start scanning
            const result = await BarcodeScanner.scan({
                formats: [
                    BarcodeFormat.Ean13,
                    BarcodeFormat.Ean8,
                    BarcodeFormat.UpcA,
                    BarcodeFormat.UpcE,
                    BarcodeFormat.Code128,
                    BarcodeFormat.Code39,
                    BarcodeFormat.Code93,
                    BarcodeFormat.QrCode,
                    BarcodeFormat.DataMatrix,
                ],
            });

            if (result.barcodes && result.barcodes.length > 0) {
                const barcode = result.barcodes[0];
                return barcode.rawValue || barcode.displayValue;
            }

            return null;
        } catch (error) {
            console.error('[BarcodeScanner] Error scanning barcode:', error);

            // Handle user cancellation
            if (error.message?.includes('canceled') || error.message?.includes('cancelled')) {
                return null;
            }

            alertStore.error('Failed to scan barcode');
            return null;
        } finally {
            isScanning.value = false;
            document.body.classList.remove('barcode-scanning');
        }
    }

    // Stop scanning (if needed)
    async function stopScan() {
        try {
            await BarcodeScanner.stopScan();
        } catch (error) {
            console.error('[BarcodeScanner] Error stopping scan:', error);
        } finally {
            isScanning.value = false;
            document.body.classList.remove('barcode-scanning');
        }
    }

    return {
        isScanning,
        isSupported,
        isReady,
        isSunmiDevice,
        sunmiListenerActive,
        checkSupport,
        requestPermission,
        initialize,
        scan,
        stopScan,
        initSunmiScanner,
        startSunmiListener,
        stopSunmiListener,
    };
}
