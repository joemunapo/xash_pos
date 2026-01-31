import { registerPlugin } from '@capacitor/core'
import type { PluginListenerHandle } from '@capacitor/core'

export interface SunmiScannerPlugin {
    /**
     * Start listening for Sunmi hardware scanner broadcasts
     */
    startListening(): Promise<void>

    /**
     * Stop listening for Sunmi hardware scanner broadcasts
     */
    stopListening(): Promise<void>

    /**
     * Check if running on a Sunmi device
     */
    isSunmiDevice(): Promise<{ isSunmi: boolean }>

    /**
     * Add listener for barcode scan events
     */
    addListener(
        eventName: 'onBarcodeScanned',
        listenerFunc: (event: { barcode: string; source?: string }) => void
    ): Promise<PluginListenerHandle>

    /**
     * Remove all listeners
     */
    removeAllListeners(): Promise<void>
}

export const SunmiScanner = registerPlugin<SunmiScannerPlugin>('SunmiScanner')
