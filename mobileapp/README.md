# XASHPOS Mobile App

A Vue 3 + Capacitor mobile point-of-sale application for Android devices with offline support and thermal printer integration.

## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Development](#development)
- [Building](#building)
- [Deployment](#deployment)
- [Project Structure](#project-structure)
- [Key Features](#key-features)
- [Troubleshooting](#troubleshooting)

## Overview

The XASHPOS Mobile App is a Progressive Web App (PWA) that can be deployed as a web application or compiled as a native Android app using Capacitor. It provides cashiers with a fast, intuitive interface for processing sales, managing carts, and printing receipts.

## Features

- **Product Management**
  - Browse products by category
  - Search by name, barcode, or SKU
  - Barcode scanning support
  - Quick quantity adjustments
  - Multi-unit support (pieces, dozens, packs, etc.)

- **Cart & Checkout**
  - Add/remove items with quantity control
  - Real-time stock validation
  - Discount application (item-level and cart-level)
  - Multiple payment methods (Cash, Card, Mobile Money, Split)
  - Change calculation
  - Customer association

- **Sales Management**
  - View sales history by date
  - Filter and search receipts
  - Void/cancel completed sales
  - Reprint receipts
  - Daily sales summary

- **Offline Support**
  - Continue operations without internet
  - Local data caching
  - Auto-sync when connection restored
  - Pending sale queue

- **Receipt Printing**
  - Native Sunmi printer integration
  - Bitmap-based high-quality receipts
  - Business name and branch display
  - Cashier and customer details
  - Itemized list with totals

## Tech Stack

- **Framework**: Vue 3 (Composition API with `<script setup>`)
- **Native Wrapper**: Capacitor 7
- **State Management**: Pinia
- **Routing**: Vue Router 4
- **HTTP Client**: Fetch API with custom wrapper
- **Styling**: Tailwind CSS v4
- **Icons**: Font Awesome 6
- **Build Tool**: Vite
- **Printer Support**: capacitor-sunmi-printer-v7 (for Sunmi devices)

## Requirements

- Node.js >= 18.x
- npm >= 9.x
- Android Studio (for Android builds)
- Java JDK 17 (for Android builds)
- A running XASHPOS Laravel backend

## Installation

### 1. Navigate to Mobile App Directory

```bash
cd mobileapp
```

### 2. Install Dependencies

```bash
npm install
```

### 3. Configure Environment Variables

Create a `.env` file in the `mobileapp` directory:

```env
# Backend API URL (adjust to your Laravel backend)
VITE_API_URL='http://127.0.0.1:8001/api'

# For production/mobile builds, use your server IP or domain
# VITE_API_URL='https://api.yourserver.com/api'
```

**Important**:
- For local development: Use `http://127.0.0.1:8001/api` or `http://localhost:8001/api`
- For Android device testing: Use your computer's local IP (e.g., `http://192.168.1.100:8001/api`)
- For production: Use your production domain (e.g., `https://api.yourserver.com/api`)

## Configuration

### API URL Configuration

The API URL is set in `mobileapp/.env` and is used throughout the app:

```javascript
// Auto-loaded by Vite
const baseUrl = import.meta.env.VITE_API_URL
```

### Capacitor Configuration

Edit `capacitor.config.ts` to customize app settings:

```typescript
const config: CapacitorConfig = {
  appId: 'com.xashpos.mobile',
  appName: 'XASHPOS',
  webDir: 'dist',
  server: {
    androidScheme: 'https',
    cleartext: true, // Allow HTTP in development
  },
}
```

## Development

### Start Development Server

```bash
npm run dev
```

The app will be available at `http://localhost:5173` (or another port if 5173 is busy).

### Development with Hot Reload

Vite provides instant hot module replacement (HMR) for a smooth development experience:

1. Make changes to any `.vue` file
2. Save the file
3. Changes appear instantly in the browser

### Testing on Android Device

To test on a physical Android device during development:

1. Update `.env` with your computer's local IP:
   ```env
   VITE_API_URL='http://192.168.1.100:8001/api'
   ```

2. Build and run:
   ```bash
   npm run build
   npx cap sync android
   npx cap run android
   ```

## Building

### Build for Web (PWA)

```bash
npm run build
```

This creates an optimized production build in the `dist` directory.

#### Preview Production Build

```bash
npm run preview
```

### Build for Android

#### Prerequisites

1. **Install Android Studio**
   - Download from: https://developer.android.com/studio
   - Install Android SDK Platform 33+

2. **Install Java JDK 17**
   - Download from: https://adoptium.net/
   - Set `JAVA_HOME` environment variable

#### Build Steps

1. **Build the web app**:
   ```bash
   npm run build
   ```

2. **Sync Capacitor**:
   ```bash
   npx cap sync android
   ```

3. **Open in Android Studio**:
   ```bash
   npx cap open android
   ```

4. **In Android Studio**:
   - Select "Build" → "Generate Signed Bundle / APK"
   - Follow the wizard to create a signed APK or AAB
   - Or click "Run" to test on a connected device/emulator

#### Build APK from Command Line

```bash
# Debug APK
cd android
./gradlew assembleDebug

# Release APK (requires keystore)
./gradlew assembleRelease
```

**Debug APK location**: `android/app/build/outputs/apk/debug/app-debug.apk`

**Release APK location**: `android/app/build/outputs/apk/release/app-release.apk`

#### Installing APK on Device

```bash
# Using ADB
adb install android/app/build/outputs/apk/debug/app-debug.apk

# Or transfer APK to device and install manually
```

## Deployment

### Deploy as Web App (PWA)

1. Build the app:
   ```bash
   npm run build
   ```

2. Upload the `dist` folder contents to your web server

3. Configure your web server to:
   - Serve `index.html` for all routes (for Vue Router)
   - Enable HTTPS (required for PWA features)
   - Set proper cache headers

#### Nginx Configuration Example

```nginx
server {
    listen 80;
    server_name pos.yourserver.com;
    root /var/www/xashpos-mobile/dist;
    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }

    # Enable gzip compression
    gzip on;
    gzip_types text/css application/javascript image/svg+xml;
}
```

### Deploy as Android App

1. Build a signed APK/AAB (see [Build for Android](#build-for-android))
2. Distribute via:
   - Google Play Store (recommended for updates)
   - Direct APK download from your server
   - Internal app distribution platforms

## Project Structure

```
mobileapp/
├── android/                 # Capacitor Android project
├── dist/                    # Build output (generated)
├── public/                  # Static assets
│   ├── xash_logo_blag.jpg  # App logo for receipts
│   └── manifest.json       # PWA manifest
├── src/
│   ├── assets/             # CSS and images
│   ├── helpers/            # Utility functions
│   │   └── fetch-wrapper.js    # API client wrapper
│   ├── pages/              # Vue page components
│   │   ├── auth/           # Login, PIN setup
│   │   ├── pos/            # POS features (sell, sales, products)
│   │   └── manager/        # Manager features
│   ├── router/             # Vue Router configuration
│   │   └── index.js
│   ├── services/           # Business logic services
│   │   ├── print.js        # Receipt printing service
│   │   └── ...
│   ├── stores/             # Pinia stores
│   │   ├── alert.store.js  # Toast notifications
│   │   ├── auth.store.js   # Authentication state
│   │   └── sync.store.js   # Offline sync management
│   ├── App.vue             # Root component
│   └── main.js             # App entry point
├── .env                    # Environment variables
├── capacitor.config.ts     # Capacitor configuration
├── index.html              # HTML entry point
├── package.json            # Dependencies
├── tailwind.config.js      # Tailwind CSS configuration
├── vite.config.js          # Vite configuration
└── README.md               # This file
```

## Key Features

### Authentication

The app uses PIN-based authentication for cashiers:

1. Login with phone number and PIN
2. Receive Sanctum token from Laravel backend
3. Token stored securely in localStorage
4. Auto-logout after 8 hours of inactivity

### Offline Support

The app includes offline capabilities:

- **Local Storage**: Products, categories cached locally
- **Pending Sales Queue**: Sales saved locally when offline
- **Auto-Sync**: Automatically syncs pending sales when connection restored
- **Sync Status**: Visual indicators for sync state

Managed by `sync.store.js`:
```javascript
// Check if online
if (syncStore.isOnline) {
  // Sync pending sales
  await syncStore.syncPendingSales()
}
```

### Receipt Printing

Supports Sunmi thermal printers (V2 PRO, V2s, V2 Plus, etc.):

**Print Service** (`src/services/print.js`):
- Bitmap-based printing for high quality
- Business name and logo support
- Line wrapping and text formatting
- Automatic printer initialization
- Fallback to console logging for web testing

**Usage**:
```javascript
import { printReceipt, buildReceiptPayload } from '@/services/print'

const receiptData = buildReceiptPayload(sale, {
  user: authStore.user,
  business_name: 'My Shop',
  cashier_name: 'John Doe',
})

await printReceipt(receiptData)
```

### State Management

The app uses Pinia for state management:

- **auth.store.js**: User authentication, profile, logout
- **alert.store.js**: Toast notifications (success, error, warning)
- **sync.store.js**: Offline sync, pending sales queue

### API Integration

Custom fetch wrapper (`src/helpers/fetch-wrapper.js`):
- Automatic token injection
- Error handling
- Request/response interceptors
- Timeout handling

## Troubleshooting

### Issue: API Connection Failed

**Symptoms**: "Network Error" or "Failed to fetch"

**Solutions**:
1. Verify backend is running: `php artisan serve --port=8001`
2. Check `VITE_API_URL` in `.env`
3. For Android device: Use computer's IP, not `localhost`
4. Check firewall settings (allow port 8001)

### Issue: Build Errors

**Symptoms**: Build fails with errors

**Solutions**:
```bash
# Clear node_modules and reinstall
rm -rf node_modules package-lock.json
npm install

# Clear Vite cache
rm -rf node_modules/.vite

# Rebuild
npm run build
```

### Issue: Printer Not Working

**Symptoms**: "Printer not available" or prints are blank

**Solutions**:
1. Verify you're running on a Sunmi device
2. Check printer status in device settings
3. Restart the app
4. Check printer service binding:
   ```javascript
   import { logPrintDiagnostics } from '@/services/print'
   await logPrintDiagnostics()
   ```

### Issue: Capacitor Sync Fails

**Symptoms**: Changes not reflected in Android build

**Solutions**:
```bash
# Clean and rebuild
rm -rf dist android
npm run build
npx cap add android
npx cap sync android
```

### Issue: White Screen on Android

**Symptoms**: App shows white screen after installing

**Solutions**:
1. Check console logs in Android Studio (Logcat)
2. Verify `capacitor.config.ts` has correct `webDir: 'dist'`
3. Ensure API URL is correct (not localhost for device)
4. Check if backend API is accessible from device

### Issue: Can't Login

**Symptoms**: Login fails or returns 401

**Solutions**:
1. Verify user exists in database with correct phone and PIN
2. Check backend `/api/pos/login` endpoint is accessible
3. Verify CORS settings in Laravel (`config/cors.php`)
4. Check Sanctum configuration (`config/sanctum.php`)

## Support

For issues related to:
- **Backend**: See main project README
- **Mobile App**: Check this README or contact the development team

## License

Proprietary software. All rights reserved.

---

**Built with Vue 3, Capacitor, and Tailwind CSS**
