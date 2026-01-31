# Sunmi Integration Module Specification

## Overview
Native Sunmi SDK integration for hardware access - printers, scanners, displays, and peripherals.

---

## Supported Devices

| Device | Model | Features |
|--------|-------|----------|
| Sunmi V2 | Handheld | Built-in printer, scanner |
| Sunmi V2 Pro | Handheld | NFC, fingerprint |
| Sunmi T2 | Desktop | Large display, receipt printer |
| Sunmi D2 | Desktop | Customer display, cash drawer |
| Sunmi S2 | Mobile | Compact, built-in printer |

---

## Hardware Features

### Thermal Printer
- 58mm/80mm paper support
- Logo printing
- Barcode/QR code printing
- Multiple font sizes
- Paper cut command
- Printer status check

### Barcode Scanner
- Built-in camera scanner
- External USB scanner support
- 1D/2D barcode support
- Continuous scan mode
- Scan sound/vibration feedback

### Customer Display
- Secondary screen support
- Display cart items
- Display total
- Marketing content
- Customer-facing mode

### Cash Drawer
- Electronic drawer support
- Open command
- Status check (open/closed)

### Weighing Scale
- Serial/USB scale connection
- Weight reading
- Auto-add by weight
- Tare function

---

## Capacitor Plugin Structure

```javascript
// Sunmi Printer Plugin
import { SunmiPrinter } from '@xash/sunmi-printer';

// Print receipt
await SunmiPrinter.printReceipt({
  header: 'XASH GROCERY',
  items: [...],
  total: 150.00,
  paymentMethod: 'Cash',
  footer: 'Thank you!'
});

// Print barcode
await SunmiPrinter.printBarcode({
  data: '1234567890123',
  type: 'EAN13'
});

// Check printer status
const status = await SunmiPrinter.getStatus();
```

```javascript
// Sunmi Scanner Plugin
import { SunmiScanner } from '@xash/sunmi-scanner';

// Start scanning
SunmiScanner.addListener('scan', (result) => {
  console.log('Scanned:', result.code);
});

await SunmiScanner.startScan();
```

---

## Receipt Template

```
================================
        XASH GROCERY
     Branch: Main Street
   Tel: +263 77 123 4567
================================
Date: 2025-12-20  Time: 14:30
Cashier: John D.
--------------------------------
Item            Qty    Amount
--------------------------------
Bread White      2      $4.00
Milk 2L          1      $3.50
Eggs Tray        1      $5.00
--------------------------------
Subtotal:              $12.50
VAT (15%):              $1.88
--------------------------------
TOTAL:                 $14.38
================================
Cash:                  $20.00
Change:                 $5.62
================================
    Thank you for shopping!
      Receipt #: 00001234
================================
        [QR CODE HERE]
================================
```

---

## Implementation Notes

### Android Integration
- Sunmi SDK via Capacitor plugin
- AIDL service binding
- Permission handling
- Device detection

### Fallback Support
- Generic ESC/POS for non-Sunmi
- Bluetooth printer support
- Network printer option

---

*Module Owner: TBD*  
*Last Updated: December 2025*
