import { registerPlugin } from '@capacitor/core'

export interface UsbEndpoint {
    address: number
    type: number
    typeName: string
    direction: number
    directionName: 'IN' | 'OUT'
    maxPacketSize: number
}

export interface UsbInterface {
    id: number
    interfaceClass: number
    interfaceSubclass: number
    endpointCount: number
    endpoints: UsbEndpoint[]
}

export interface UsbDeviceInfo {
    deviceName: string
    vendorId: number
    productId: number
    deviceClass: number
    deviceSubclass: number
    interfaceCount: number
    interfaces: UsbInterface[]
}

export interface ListDevicesResult {
    count: number
    devices: UsbDeviceInfo[]
}

export interface UsbThermalPrinterPlugin {
    listDevices(): Promise<ListDevicesResult>
    connect(): Promise<{ connected: boolean; deviceName: string }>
    printText(options: {
        text: string
        align?: 'left' | 'center' | 'right'
        bold?: boolean
        size?: 'normal' | 'large' | 'xlarge'
    }): Promise<void>
    cut(): Promise<void>
    disconnect(): Promise<void>
}

export const UsbThermalPrinter =
    registerPlugin<UsbThermalPrinterPlugin>('UsbThermalPrinter')
