import { Capacitor } from '@capacitor/core'
import { SunmiPrinter } from 'capacitor-sunmi-printer-v7'

const isNative = Capacitor.isNativePlatform()
const isAndroid = Capacitor.getPlatform() === 'android'

// Sunmi Printer state
let sunmiPrinterBound = false

/**
 * Check if Sunmi printer is available
 */
function isSunmiPrinterAvailable() {
    return isNative && isAndroid
}

/**
 * Initialize Sunmi printer - bind service
 */
async function initSunmiPrinter() {
    console.log('[Print] initSunmiPrinter called, bound:', sunmiPrinterBound)

    if (sunmiPrinterBound) {
        console.log('[Print] Printer already bound, skipping')
        return true
    }

    if (!isSunmiPrinterAvailable()) {
        console.log('[Print] Sunmi printer not available (not Android native)')
        return false
    }

    try {
        console.log('[Print] Binding Sunmi printer service...')
        await SunmiPrinter.bindService()
        sunmiPrinterBound = true
        console.log('[Print] Sunmi printer service bound successfully')

        // Get printer info
        try {
            const deviceName = await SunmiPrinter.getDeviceName()
            console.log('[Print] Sunmi Device:', deviceName?.name || 'Unknown')
        } catch (e) {
            console.log('[Print] Could not get device name:', e.message)
        }

        try {
            const status = await SunmiPrinter.updatePrinterState()
            console.log('[Print] Sunmi Printer Status:', status?.status, 'code:', status?.code)
        } catch (e) {
            console.log('[Print] Could not get printer status:', e.message)
        }

        return true
    } catch (error) {
        console.log('[Print] Failed to bind Sunmi printer:', error.message || error)
        sunmiPrinterBound = false
        return false
    }
}

/**
 * Log available plugins and printing capabilities for diagnostics
 */
export async function logPrintDiagnostics() {
    console.log('[Print Diagnostics] ===== Starting Print Diagnostics =====')
    console.log('[Print Diagnostics] Platform:', Capacitor.getPlatform())
    console.log('[Print Diagnostics] Is Native:', isNative)
    console.log('[Print Diagnostics] Is Android:', isAndroid)

    // Log window.Capacitor.Plugins if available
    if (typeof window !== 'undefined' && window.Capacitor && window.Capacitor.Plugins) {
        const registeredPlugins = Object.keys(window.Capacitor.Plugins)
        console.log('[Print Diagnostics] Registered Capacitor Plugins:', registeredPlugins.join(', ') || 'none')
    }

    // Try initializing Sunmi printer
    if (isSunmiPrinterAvailable()) {
        console.log('[Print Diagnostics] Attempting to bind printer service...')
        try {
            const bound = await initSunmiPrinter()

            if (bound) {
                console.log('[Print Diagnostics] Printer service bound!')
                try {
                    const status = await SunmiPrinter.updatePrinterState()
                    console.log('[Print Diagnostics] Printer status:', status?.status, '(code:', status?.code, ')')
                } catch (e) {
                    console.log('[Print Diagnostics] Could not get status:', e.message)
                }
            } else {
                console.log('[Print Diagnostics] Failed to bind printer service')
            }
        } catch (e) {
            console.log('[Print Diagnostics] Sunmi printer error:', e.message || e)
        }
    } else {
        console.log('[Print Diagnostics] Skipping Sunmi printer (not Android native)')
    }

    console.log('[Print Diagnostics] ===== End Print Diagnostics =====')
}

// Flag to run diagnostics only once
let diagnosticsRun = false

/**
 * Print a POS receipt using the thermal printer
 */
export async function printReceipt(receipt) {
    console.log('[Print] printReceipt() called with:', receipt)

    // Run diagnostics on first print attempt
    if (!diagnosticsRun) {
        diagnosticsRun = true
        try {
            await logPrintDiagnostics()
        } catch (e) {
            console.log('[Print] Diagnostics error:', e.message || e)
        }
    }

    if (!receipt || !receipt.receipt_number) {
        throw new Error('Invalid receipt data')
    }

    if (isNative) {
        return printReceiptNative(receipt)
    } else {
        return printReceiptWeb(receipt)
    }
}

/**
 * Print POS receipt on native platform using Sunmi printer
 * Uses direct printing without buffer mode for V2_PRO compatibility
 */
async function printReceiptNative(receipt) {
    console.log('[Print] Starting native receipt print...')

    if (!isSunmiPrinterAvailable()) {
        console.log('[Print] Sunmi printer not available - skipping print')
        return
    }

    console.log('[Print] Calling initSunmiPrinter...')
    const bound = await initSunmiPrinter()
    console.log('[Print] initSunmiPrinter returned:', bound)

    if (!bound) {
        console.log('[Print] Failed to bind Sunmi printer - skipping print')
        return
    }

    try {
        // Build receipt text content
        const receiptDate = receipt.created_at ? new Date(receipt.created_at) : new Date()
        const day = String(receiptDate.getDate()).padStart(2, '0')
        const month = String(receiptDate.getMonth() + 1).padStart(2, '0')
        const year = receiptDate.getFullYear()
        const hours = String(receiptDate.getHours()).padStart(2, '0')
        const minutes = String(receiptDate.getMinutes()).padStart(2, '0')
        const dateTime = `${day}-${month}-${year} ${hours}:${minutes}`

        // Payment method label
        const paymentMethodLabel = receipt.payment_method === 'cash' ? 'Cash' :
            receipt.payment_method === 'card' ? 'Card' :
                receipt.payment_method === 'mobile_money' ? 'Mobile Money' :
                    receipt.payment_method === 'split' ? 'Split Payment' :
                        receipt.payment_method || 'Cash'

        // Build items text
        let itemsText = ''
        if (receipt.items && receipt.items.length > 0) {
            for (const item of receipt.items) {
                const name = item.product_name || item.name
                const qty = parseFloat(item.quantity || 0).toFixed(0)
                const price = parseFloat(item.unit_price || item.price || 0).toFixed(2)
                const total = parseFloat(item.total || (item.quantity * item.unit_price)).toFixed(2)
                itemsText += `${name}\n  ${qty} x $${price} = $${total}\n`
            }
        }

        const subtotal = parseFloat(receipt.subtotal || receipt.total_amount || 0).toFixed(2)
        const total = parseFloat(receipt.total_amount || 0).toFixed(2)
        const amountPaid = parseFloat(receipt.amount_paid || receipt.total_amount || 0).toFixed(2)

        // Build single receipt string
        let receiptText = ''
        receiptText += '================================\n'
        receiptText += '          XASH POS\n'
        if (receipt.branch_name) {
            receiptText += `      ${receipt.branch_name}\n`
        }
        receiptText += '================================\n'
        receiptText += `Receipt #: ${receipt.receipt_number}\n`
        receiptText += `Date: ${dateTime}\n`
        receiptText += '--------------------------------\n'
        receiptText += 'ITEMS\n'
        receiptText += '--------------------------------\n'
        receiptText += itemsText
        receiptText += '--------------------------------\n'
        receiptText += `Subtotal: $${subtotal}\n`

        if (receipt.discount_amount && parseFloat(receipt.discount_amount) > 0) {
            const discount = parseFloat(receipt.discount_amount).toFixed(2)
            receiptText += `Discount: -$${discount}\n`
        }

        receiptText += `TOTAL: $${total}\n`
        receiptText += '--------------------------------\n'
        receiptText += 'PAYMENT\n'
        receiptText += `Method: ${paymentMethodLabel}\n`

        if (receipt.payments && receipt.payments.length > 0) {
            for (const payment of receipt.payments) {
                const method = payment.method === 'cash' ? 'Cash' :
                    payment.method === 'ecocash' ? 'Ecocash' :
                        payment.method === 'swipe' ? 'Card' : payment.method
                const amount = parseFloat(payment.amount || 0).toFixed(2)
                receiptText += `  ${method}: $${amount}\n`
            }
        } else {
            receiptText += `Paid: $${amountPaid}\n`
        }

        if (receipt.change_amount && parseFloat(receipt.change_amount) > 0) {
            const change = parseFloat(receipt.change_amount).toFixed(2)
            receiptText += `Change: $${change}\n`
        }

        receiptText += '================================\n'

        if (receipt.customer_name) {
            receiptText += `Customer: ${receipt.customer_name}\n`
        }
        if (receipt.cashier_name || receipt.user_name) {
            receiptText += `Cashier: ${receipt.cashier_name || receipt.user_name}\n`
        }

        receiptText += '\nThank you for your purchase!\n'
        receiptText += 'Visit us again\n'
        receiptText += '\n\n\n'

        console.log('[Print] Printing receipt text...')
        console.log('[Print] Receipt content:', receiptText.substring(0, 100) + '...')

        // Use buffer mode with commitPrinterBuffer which resolves immediately
        // This bypasses the callback issues with printText methods
        try {
            console.log('[Print] Entering buffer mode...')
            await SunmiPrinter.enterPrinterBuffer({ clean: true })
            console.log('[Print] Buffer mode entered')

            // Queue print commands without awaiting (they hang)
            console.log('[Print] Queueing print commands...')
            SunmiPrinter.printText({ text: receiptText }).catch(() => { })

            // Small delay to let commands queue
            await new Promise(resolve => setTimeout(resolve, 200))

            // Commit buffer - this resolves immediately!
            console.log('[Print] Committing buffer...')
            await SunmiPrinter.commitPrinterBuffer()
            console.log('[Print] Buffer committed - print should start!')

        } catch (e) {
            console.log('[Print] Buffer approach failed:', e.message)

            // Fallback: try sendRAWData with ESC/POS commands
            try {
                console.log('[Print] Trying raw ESC/POS data...')
                const initCmd = '\x1B\x40' // ESC @ - Initialize printer
                const rawData = initCmd + receiptText + '\n\n\n\n'
                SunmiPrinter.sendRAWData({ data: rawData }).catch(() => { })
                console.log('[Print] Raw data queued')
            } catch (e2) {
                console.log('[Print] Raw data approach also failed:', e2.message)
            }
        }

        console.log('[Print] Native receipt print completed!')
    } catch (error) {
        console.log('[Print] Native receipt print failed:', error.message || error)
        console.log('[Print] Error stack:', error.stack || 'no stack')
    }
}

/**
 * Fallback web receipt printing (for development/testing)
 */
function printReceiptWeb(receipt) {
    console.log('[Print] Web receipt print (development mode)')
    console.log('========== RECEIPT ==========')
    console.log(`Receipt #: ${receipt.receipt_number}`)
    console.log(`Date: ${receipt.created_at || new Date().toISOString()}`)
    console.log('-----------------------------')
    console.log('ITEMS:')
    if (receipt.items && receipt.items.length > 0) {
        receipt.items.forEach(item => {
            console.log(`  ${item.product_name || item.name}`)
            console.log(`  ${item.quantity} x $${item.unit_price} = $${item.total}`)
        })
    }
    console.log('-----------------------------')
    console.log(`Subtotal: $${receipt.subtotal || receipt.total_amount}`)
    if (receipt.discount_amount) {
        console.log(`Discount: -$${receipt.discount_amount}`)
    }
    console.log(`TOTAL: $${receipt.total_amount}`)
    console.log('-----------------------------')
    console.log(`Payment: ${receipt.payment_method}`)
    console.log(`Paid: $${receipt.amount_paid}`)
    if (receipt.change_amount) {
        console.log(`Change: $${receipt.change_amount}`)
    }
    console.log('=============================')
    return Promise.resolve()
}

export default {
    printReceipt,
    logPrintDiagnostics,
}
