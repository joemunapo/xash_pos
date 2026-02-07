import { Capacitor } from '@capacitor/core'
import {
    AlignmentModeEnum,
    BitmapPrintTypeEnum,
    SunmiPrinter,
} from 'capacitor-sunmi-printer-v7'

const isNative = Capacitor.isNativePlatform()
const isAndroid = Capacitor.getPlatform() === 'android'

// Sunmi Printer state
let sunmiPrinterBound = false
const RECEIPT_PRINT_WIDTH = 384
const RECEIPT_SIDE_PADDING = 10
const RECEIPT_LOGO_PATHS = [
    '/xash_logo_blag.jpg',
    '/xash_logo_blag.png',
    '/xash_logo_black.jpg',
    '/xash_logo_black.png',
]

function money(value) {
    const parsed = parseFloat(value ?? 0)
    return Number.isFinite(parsed) ? parsed.toFixed(2) : '0.00'
}

function getUserDisplayName(user) {
    if (!user) return null
    if (user.name && String(user.name).trim()) return String(user.name).trim()
    const first = user.first_name ? String(user.first_name).trim() : ''
    const last = user.last_name ? String(user.last_name).trim() : ''
    const fullName = `${first} ${last}`.trim()
    return fullName || null
}

function resolveBusinessName(source = {}, context = {}) {
    const user = context.user || null
    return source.business_name
        || source.businessName
        || source.shop_name
        || source.company_name
        || source.business?.name
        || source.branch?.business_name
        || source.branch?.business?.name
        || context.business_name
        || context.businessName
        || user?.business_name
        || user?.business?.name
        || user?.branch?.business_name
        || user?.branch?.business?.name
        || null
}

function resolveBranchName(source = {}, context = {}) {
    const user = context.user || null
    return source.branch_name
        || source.branchName
        || source.branch?.name
        || context.branch_name
        || context.branchName
        || user?.branch?.name
        || null
}

function resolveCashierName(source = {}, context = {}) {
    const user = context.user || null
    return source.cashier_name
        || source.cashierName
        || source.user_name
        || source.user?.name
        || source.cashier?.name
        || context.cashier_name
        || context.cashierName
        || getUserDisplayName(user)
        || null
}

function normalizeItems(items) {
    if (!Array.isArray(items)) return []
    return items.map((item) => {
        const quantity = Number(item?.quantity ?? 0) || 0
        const unitPrice = Number(item?.unit_price ?? item?.price ?? 0) || 0
        const total = Number(item?.total ?? item?.line_total ?? (quantity * unitPrice)) || 0

        return {
            product_name: item?.product_name || item?.name || item?.product?.name || 'Item',
            quantity,
            unit_price: unitPrice,
            total,
        }
    })
}

export function buildReceiptPayload(sale = {}, context = {}) {
    const fallbackTotal = Number(sale.total_amount ?? context.total_amount ?? 0) || 0
    return {
        receipt_number: sale.receipt_number || context.receipt_number || '',
        created_at: sale.created_at || context.created_at || new Date().toISOString(),
        business_name: resolveBusinessName(sale, context),
        branch_name: resolveBranchName(sale, context),
        items: normalizeItems(sale.items || context.items),
        subtotal: sale.subtotal ?? context.subtotal ?? fallbackTotal,
        discount_amount: sale.discount_amount ?? context.discount_amount ?? 0,
        total_amount: fallbackTotal,
        payment_method: sale.payment_method || context.payment_method || 'cash',
        payments: Array.isArray(sale.payments)
            ? sale.payments
            : (Array.isArray(context.payments) ? context.payments : null),
        amount_paid: sale.amount_paid ?? context.amount_paid ?? fallbackTotal,
        change_amount: sale.change_amount ?? context.change_amount ?? 0,
        customer_name: sale.customer_name || sale.customer?.name || context.customer_name || null,
        cashier_name: resolveCashierName(sale, context),
    }
}

function arrayBufferToBase64(buffer) {
    const bytes = new Uint8Array(buffer)
    const chunkSize = 0x8000
    let binary = ''
    for (let i = 0; i < bytes.length; i += chunkSize) {
        const slice = bytes.subarray(i, i + chunkSize)
        binary += String.fromCharCode(...slice)
    }
    return btoa(binary)
}

async function getAssetBase64(path) {
    try {
        const response = await fetch(path)
        if (!response.ok) {
            return null
        }
        const buffer = await response.arrayBuffer()
        return arrayBufferToBase64(buffer)
    } catch {
        return null
    }
}

function queueSunmi(label, run) {
    try {
        const promise = run()
        if (promise && typeof promise.catch === 'function') {
            promise.catch((error) => {
                console.log(`[Print] ${label} failed:`, error?.message || error)
            })
        }
    } catch (error) {
        console.log(`[Print] ${label} failed:`, error?.message || error)
    }
}

async function awaitSunmi(label, run, timeoutMs = 1800) {
    try {
        return await Promise.race([
            run(),
            new Promise((_, reject) => setTimeout(() => reject(new Error(`${label} timeout`)), timeoutMs)),
        ])
    } catch (error) {
        console.log(`[Print] ${label} failed:`, error?.message || error)
        return null
    }
}

function createReceiptCanvas(height) {
    if (typeof document === 'undefined') {
        return null
    }

    const canvas = document.createElement('canvas')
    canvas.width = RECEIPT_PRINT_WIDTH
    canvas.height = Math.max(1, Math.ceil(height))
    const ctx = canvas.getContext('2d')
    if (!ctx) {
        return null
    }

    ctx.fillStyle = '#FFFFFF'
    ctx.fillRect(0, 0, canvas.width, canvas.height)
    ctx.fillStyle = '#000000'
    ctx.textBaseline = 'top'
    ctx.imageSmoothingEnabled = false
    return { canvas, ctx }
}

function wrapText(ctx, text, maxWidth) {
    const lines = []
    const input = String(text ?? '')
    const paragraphs = input.split('\n')

    for (const paragraph of paragraphs) {
        const words = paragraph.trim().split(/\s+/).filter(Boolean)
        if (!words.length) {
            lines.push('')
            continue
        }

        let currentLine = words[0]
        for (let i = 1; i < words.length; i += 1) {
            const candidate = `${currentLine} ${words[i]}`
            if (ctx.measureText(candidate).width <= maxWidth) {
                currentLine = candidate
            } else {
                lines.push(currentLine)
                currentLine = words[i]
            }
        }
        lines.push(currentLine)
    }

    return lines.length ? lines : ['']
}

function truncateText(ctx, text, maxWidth) {
    let output = String(text ?? '')
    if (ctx.measureText(output).width <= maxWidth) {
        return output
    }

    const ellipsis = '...'
    while (output.length > 0) {
        output = output.slice(0, -1)
        if (ctx.measureText(output + ellipsis).width <= maxWidth) {
            return output + ellipsis
        }
    }
    return ellipsis
}

function lineTextToBase64(text, options = {}) {
    const {
        align = 'left',
        fontSize = 22,
        fontWeight = 700,
        paddingX = RECEIPT_SIDE_PADDING,
        paddingY = 2,
    } = options

    const probe = createReceiptCanvas(64)
    if (!probe) return null

    probe.ctx.font = `${fontWeight} ${fontSize}px sans-serif`
    const maxTextWidth = RECEIPT_PRINT_WIDTH - (paddingX * 2)
    const lines = wrapText(probe.ctx, text, maxTextWidth)
    const lineHeight = Math.ceil(fontSize * 1.2)
    const height = (lines.length * lineHeight) + (paddingY * 2)

    const surface = createReceiptCanvas(height)
    if (!surface) return null
    const { canvas, ctx } = surface

    ctx.font = `${fontWeight} ${fontSize}px sans-serif`
    for (let i = 0; i < lines.length; i += 1) {
        const line = lines[i]
        const y = paddingY + (i * lineHeight)
        if (align === 'center') {
            ctx.textAlign = 'center'
            ctx.fillText(line, RECEIPT_PRINT_WIDTH / 2, y)
        } else if (align === 'right') {
            ctx.textAlign = 'right'
            ctx.fillText(line, RECEIPT_PRINT_WIDTH - paddingX, y)
        } else {
            ctx.textAlign = 'left'
            ctx.fillText(line, paddingX, y)
        }
    }

    return canvas.toDataURL('image/png').split(',')[1]
}

function rowTextToBase64(left, right, options = {}) {
    const {
        fontSize = 21,
        fontWeight = 700,
        paddingX = RECEIPT_SIDE_PADDING,
        paddingY = 2,
        gap = 10,
    } = options

    const surface = createReceiptCanvas(Math.ceil(fontSize * 1.2) + (paddingY * 2))
    if (!surface) return null
    const { canvas, ctx } = surface

    ctx.font = `${fontWeight} ${fontSize}px sans-serif`

    const rightText = String(right ?? '')
    const rightWidth = ctx.measureText(rightText).width
    const maxLeftWidth = Math.max(40, RECEIPT_PRINT_WIDTH - (paddingX * 2) - rightWidth - gap)
    const leftText = truncateText(ctx, String(left ?? ''), maxLeftWidth)
    const y = paddingY

    ctx.textAlign = 'left'
    ctx.fillText(leftText, paddingX, y)

    ctx.textAlign = 'right'
    ctx.fillText(rightText, RECEIPT_PRINT_WIDTH - paddingX, y)

    return canvas.toDataURL('image/png').split(',')[1]
}

function printLineBitmap(text, options = {}, queue) {
    const base64 = lineTextToBase64(text, options)
    if (!base64) {
        queue('printText line fallback', () => SunmiPrinter.printText({ text: `${text}\n` }))
        return
    }
    queue('printBitmapCustom line', () => SunmiPrinter.printBitmapCustom({
        bitmap: base64,
        type: BitmapPrintTypeEnum.BLACK_AND_WHITE,
    }))
}

function printRowBitmap(left, right, options = {}, queue) {
    const base64 = rowTextToBase64(left, right, options)
    if (!base64) {
        queue('printText row fallback', () => SunmiPrinter.printText({ text: `${left} ${right}\n` }))
        return
    }
    queue('printBitmapCustom row', () => SunmiPrinter.printBitmapCustom({
        bitmap: base64,
        type: BitmapPrintTypeEnum.BLACK_AND_WHITE,
    }))
}

async function printReceiptLogo(queue) {
    let logoBase64 = null
    for (const path of RECEIPT_LOGO_PATHS) {
        logoBase64 = await getAssetBase64(path)
        if (logoBase64) {
            break
        }
    }

    if (!logoBase64) {
        return false
    }

    queue('setAlignment center', () => SunmiPrinter.setAlignment({ alignment: AlignmentModeEnum.CENTER }))
    queue('printBitmapCustom logo', () => SunmiPrinter.printBitmapCustom({
        bitmap: logoBase64,
        type: BitmapPrintTypeEnum.BLACK_AND_WHITE,
    }))
    queue('lineWrap logo', () => SunmiPrinter.lineWrap({ lines: 2 }))
    queue('setAlignment left', () => SunmiPrinter.setAlignment({ alignment: AlignmentModeEnum.LEFT }))
    return true
}

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
        await awaitSunmi('bindService', () => SunmiPrinter.bindService(), 2000)
        sunmiPrinterBound = true
        console.log('[Print] Sunmi printer service bound successfully')

        // Get printer info
        try {
            const deviceName = await awaitSunmi('getDeviceName', () => SunmiPrinter.getDeviceName(), 1200)
            console.log('[Print] Sunmi Device:', deviceName?.name || 'Unknown')
        } catch (e) {
            console.log('[Print] Could not get device name:', e.message)
        }

        try {
            const status = await awaitSunmi('updatePrinterState(init)', () => SunmiPrinter.updatePrinterState(), 1200)
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
                    const status = await awaitSunmi('updatePrinterState(diagnostics)', () => SunmiPrinter.updatePrinterState(), 1500)
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

        const subtotal = money(receipt.subtotal || receipt.total_amount || 0)
        const total = money(receipt.total_amount || 0)
        const amountPaid = money(receipt.amount_paid || receipt.total_amount || 0)
        const businessName = receipt.business_name || ''

        console.log('[Print] Printing receipt in bitmap-enhanced mode...')

        // Use buffered mode for stable Sunmi output ordering
        const queued = { count: 0 }
        const queue = (label, run) => {
            queued.count += 1
            queueSunmi(label, run)
        }

        await awaitSunmi('enterPrinterBuffer', () => SunmiPrinter.enterPrinterBuffer({ clean: true }), 2000)
        queue('printerInit', () => SunmiPrinter.printerInit())
        queue('setBold', () => SunmiPrinter.setBold({ enable: true }))
        queue('setFontSize', () => SunmiPrinter.setFontSize({ size: 24 }))
        queue('setAlignment left', () => SunmiPrinter.setAlignment({ alignment: AlignmentModeEnum.LEFT }))

        // Header branding (logo image first, then fallback text if needed)
        const logoPrinted = await printReceiptLogo(queue)
        if (!logoPrinted) {
            printLineBitmap('XASH Pos', { align: 'center', fontSize: 28, fontWeight: 800 }, queue)
        }

        if (businessName) {
            printLineBitmap(businessName, { align: 'center', fontSize: 19, fontWeight: 800 }, queue)
        }

        if (receipt.branch_name) {
            printLineBitmap(receipt.branch_name, { align: 'center', fontSize: 18, fontWeight: 700 }, queue)
        }

        if (logoPrinted) {
            queue('lineWrap post-logo', () => SunmiPrinter.lineWrap({ lines: 1 }))
        }

        printRowBitmap('Receipt #', receipt.receipt_number, { fontSize: 18, fontWeight: 700 }, queue)
        printRowBitmap('Date', dateTime, { fontSize: 18, fontWeight: 700 }, queue)
        printLineBitmap('------------------------------', { fontSize: 18, fontWeight: 700 }, queue)
        printLineBitmap('ITEMS', { fontSize: 20, fontWeight: 800 }, queue)

        if (receipt.items && receipt.items.length > 0) {
            for (const item of receipt.items) {
                const name = item.product_name || item.name || 'Item'
                const qty = parseFloat(item.quantity || 0).toFixed(0)
                const price = money(item.unit_price || item.price || 0)
                const lineTotal = money(item.total || ((item.quantity || 0) * (item.unit_price || item.price || 0)))

                printRowBitmap(`${name} ${qty} x $${price}`, `$${lineTotal}`, { fontSize: 18, fontWeight: 700 }, queue)
            }
        }

        printLineBitmap('------------------------------', { fontSize: 18, fontWeight: 700 }, queue)
        printRowBitmap('Subtotal', `$${subtotal}`, { fontSize: 18, fontWeight: 700 }, queue)

        if (receipt.discount_amount && parseFloat(receipt.discount_amount) > 0) {
            const discount = money(receipt.discount_amount)
            printRowBitmap('Discount', `-$${discount}`, { fontSize: 18, fontWeight: 700 }, queue)
        }

        printRowBitmap('TOTAL', `$${total}`, { fontSize: 24, fontWeight: 800 }, queue)
        printLineBitmap('------------------------------', { fontSize: 18, fontWeight: 700 }, queue)
        printRowBitmap('Method', paymentMethodLabel, { fontSize: 18, fontWeight: 700 }, queue)

        if (receipt.payments && receipt.payments.length > 0) {
            for (const payment of receipt.payments) {
                const method = payment.method === 'cash' ? 'Cash' :
                    payment.method === 'ecocash' ? 'Ecocash' :
                        payment.method === 'swipe' ? 'Card' : payment.method
                printRowBitmap(method, `$${money(payment.amount || 0)}`, { fontSize: 18, fontWeight: 700 }, queue)
            }
        } else {
            printRowBitmap('Paid', `$${amountPaid}`, { fontSize: 18, fontWeight: 700 }, queue)
        }

        if (receipt.change_amount && parseFloat(receipt.change_amount) > 0) {
            printRowBitmap('Change', `$${money(receipt.change_amount)}`, { fontSize: 18, fontWeight: 700 }, queue)
        }

        printLineBitmap('------------------------------', { fontSize: 18, fontWeight: 700 }, queue)

        if (receipt.customer_name) {
            printRowBitmap('Customer', receipt.customer_name, { fontSize: 17, fontWeight: 700 }, queue)
        }
        if (receipt.cashier_name || receipt.user_name) {
            printRowBitmap('Cashier', receipt.cashier_name || receipt.user_name, { fontSize: 17, fontWeight: 700 }, queue)
        }

        queue('lineWrap thanks', () => SunmiPrinter.lineWrap({ lines: 1 }))
        printLineBitmap('Thank you for your purchase!', { align: 'center', fontSize: 20, fontWeight: 800 }, queue)
        printLineBitmap('Visit us again', { align: 'center', fontSize: 17, fontWeight: 700 }, queue)
        queue('lineWrap end', () => SunmiPrinter.lineWrap({ lines: 5 }))

        const queueDelay = Math.min(2000, Math.max(300, queued.count * 10))
        await new Promise((resolve) => setTimeout(resolve, queueDelay))
        await awaitSunmi('commitPrinterBuffer', () => SunmiPrinter.commitPrinterBuffer(), 2500)
        console.log('[Print] Buffer committed - print should start!')

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
    if (receipt.business_name) {
        console.log(`Business: ${receipt.business_name}`)
    }
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
    if (receipt.cashier_name || receipt.user_name) {
        console.log(`Cashier: ${receipt.cashier_name || receipt.user_name}`)
    }
    if (receipt.change_amount) {
        console.log(`Change: $${receipt.change_amount}`)
    }
    console.log('=============================')
    return Promise.resolve()
}

export default {
    buildReceiptPayload,
    printReceipt,
    logPrintDiagnostics,
}
