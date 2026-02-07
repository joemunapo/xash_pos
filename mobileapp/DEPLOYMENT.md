# XASH Pos — Sunmi App Store Deployment Guide

Deployment guide for building and submitting the **XASH Pos** Capacitor 7 app (`co.zw.xash.pos`) to the Sunmi App Store.

---

## Table of Contents

1. [Prerequisites](#1-prerequisites)
2. [Build Configuration](#2-build-configuration)
3. [Generate a Release Keystore](#3-generate-a-release-keystore)
4. [Configure Gradle Signing](#4-configure-gradle-signing)
5. [Build the Release APK](#5-build-the-release-apk)
6. [Register on Sunmi Partner Platform](#6-register-on-sunmi-partner-platform)
7. [Submit to Sunmi App Store](#7-submit-to-sunmi-app-store)
8. [Sunmi Review Checklist](#8-sunmi-review-checklist)
9. [Post-Release & Updates](#9-post-release--updates)
10. [Sunmi-Specific Features in This App](#10-sunmi-specific-features-in-this-app)
11. [Troubleshooting](#11-troubleshooting)

---

## 1. Prerequisites

| Tool             | Minimum Version | Notes                                     |
| ---------------- | --------------- | ----------------------------------------- |
| Node.js          | 18+             |                                           |
| npm              | 9+              |                                           |
| Java JDK         | 17              | Required by Gradle 8.x                    |
| Android Studio   | Hedgehog+       | For SDK manager, emulator & signing tools |
| Android SDK      | API 35          | Already set in `variables.gradle`         |
| Capacitor CLI    | 7.x             | Already in `package.json`                 |

Ensure `ANDROID_HOME` and `JAVA_HOME` environment variables are set:

```bash
export ANDROID_HOME=$HOME/Android/Sdk
export JAVA_HOME=/usr/lib/jvm/java-17-openjdk
export PATH=$PATH:$ANDROID_HOME/platform-tools:$ANDROID_HOME/tools
```

---

## 2. Build Configuration

### Version Bumping

Before every release, update version info in `android/app/build.gradle`:

```groovy
defaultConfig {
    applicationId "co.zw.xash.pos"
    minSdkVersion rootProject.ext.minSdkVersion   // 23
    targetSdkVersion rootProject.ext.targetSdkVersion // 35
    versionCode 2        // ← Increment for every upload
    versionName "1.1.0"  // ← Semantic version shown to users
}
```

> [!IMPORTANT]
> Sunmi App Store **rejects re-uploads with the same `versionCode`**. Always increment it.

### minSdkVersion Considerations

Your current `minSdkVersion` is **23** (Android 6.0). This is compatible with all current Sunmi devices:

| Sunmi Device | Android Version | Compatible |
| ------------ | --------------- | ---------- |
| V2 / V2 Pro  | 7.1+            | ✅          |
| P2 / P2 Pro  | 7.1 / 11 Go    | ✅          |
| T2 / T2s     | 7.1 / 8.1      | ✅          |
| L2 / L2s     | 9.0+            | ✅          |

---

## 3. Generate a Release Keystore

If you don't already have a keystore, generate one:

```bash
keytool -genkey -v \
  -keystore xashpos-release.keystore \
  -alias xashpos \
  -keyalg RSA \
  -keysize 2048 \
  -validity 10000 \
  -storepass YOUR_STORE_PASSWORD \
  -keypass YOUR_KEY_PASSWORD \
  -dname "CN=XASH Pos, OU=Mobile, O=Xash, L=Harare, ST=Harare, C=ZW"
```

> [!CAUTION]
> **Never commit the keystore or passwords to git.** Store the keystore file and credentials securely. If you lose the keystore you cannot update your app on the Sunmi App Store.

Move the keystore to a safe location (e.g., `~/.android/keystores/`):

```bash
mkdir -p ~/.android/keystores
mv xashpos-release.keystore ~/.android/keystores/
```

---

## 4. Configure Gradle Signing

### Option A: `keystore.properties` file (Recommended)

Create `mobileapp/android/keystore.properties` (add to `.gitignore`):

```properties
storeFile=/home/YOUR_USER/.android/keystores/xashpos-release.keystore
storePassword=YOUR_STORE_PASSWORD
keyAlias=xashpos
keyPassword=YOUR_KEY_PASSWORD
```

Then update `mobileapp/android/app/build.gradle`:

```groovy
// Add at the top of the file, before the android { } block
def keystorePropertiesFile = rootProject.file("keystore.properties")
def keystoreProperties = new Properties()
if (keystorePropertiesFile.exists()) {
    keystoreProperties.load(new FileInputStream(keystorePropertiesFile))
}

android {
    namespace "co.zw.xash.pos"
    compileSdk rootProject.ext.compileSdkVersion

    // Add signing config
    signingConfigs {
        release {
            storeFile file(keystoreProperties['storeFile'] ?: '/dev/null')
            storePassword keystoreProperties['storePassword'] ?: ''
            keyAlias keystoreProperties['keyAlias'] ?: ''
            keyPassword keystoreProperties['keyPassword'] ?: ''
            // Sunmi REQUIRES V1 signing. V2 is also recommended.
            v1SigningEnabled true
            v2SigningEnabled true
        }
    }

    defaultConfig {
        applicationId "co.zw.xash.pos"
        minSdkVersion rootProject.ext.minSdkVersion
        targetSdkVersion rootProject.ext.targetSdkVersion
        versionCode 2
        versionName "1.1.0"
        // ... rest of defaultConfig
    }

    buildTypes {
        release {
            signingConfig signingConfigs.release  // ← Add this line
            minifyEnabled false
            proguardFiles getDefaultProguardFile('proguard-android.txt'), 'proguard-rules.pro'
        }
    }
    // ... rest of android block
}
```

> [!WARNING]
> Sunmi **requires V1 (JAR) signing** for APK verification. Make sure `v1SigningEnabled true` is set. Without it, your APK will be rejected during review.

---

## 5. Build the Release APK

### Full Build Pipeline

From the `mobileapp/` directory:

```bash
# 1. Install dependencies
npm install

# 2. Build the Vite/Vue web assets
npm run build

# 3. Sync web assets to the Android project
npx cap sync android

# 4. Build the release APK via Gradle
cd android
./gradlew assembleRelease
```

The signed APK will be at:

```
android/app/build/outputs/apk/release/app-release.apk
```

### Quick Build (npm script)

Add this to `mobileapp/package.json` for convenience:

```json
"scripts": {
    "sunmi:build": "npm run build && npx cap sync android && cd android && ./gradlew assembleRelease",
    "sunmi:apk": "echo 'APK at:' && ls -la android/app/build/outputs/apk/release/app-release.apk"
}
```

Then run:

```bash
npm run sunmi:build
npm run sunmi:apk
```

### Verify the APK Signature

Confirm V1 + V2 signatures are present:

```bash
# Using apksigner (in Android SDK build-tools)
$ANDROID_HOME/build-tools/35.0.0/apksigner verify --verbose android/app/build/outputs/apk/release/app-release.apk
```

Expected output should include:

```
Verified using v1 scheme (JAR signing): true
Verified using v2 scheme (APK Signature Scheme v2): true
```

---

## 6. Register on Sunmi Partner Platform

### Step-by-Step

1. **Go to the Partner Platform** for your region:
   - 🌍 Global: [partner.sunmi.com](https://partner.sunmi.com)
   - 🇪🇺 Europe: [partner.eu.sunmi.com](https://partner.eu.sunmi.com)
   - 🇺🇸 North America: [partner.us.sunmi.com](https://partner.us.sunmi.com)

2. **Click "Register"** and create an account with your email.

3. **Activate** your account via the email link (check spam if you don't see it).

4. **Complete company information** — business name, address, contact details. Sunmi reviews this within ~1 business day.

5. **Apply for Developer role** — after your partner account is approved, apply for the Developer role from the platform dashboard. Review takes 1–3 business days.

6. **Access Developer Tools** — once approved, you'll have access to the App Publishing section, SDKs, and developer documentation.

---

## 7. Submit to Sunmi App Store

### Submission Steps

1. **Log in** to the Partner Platform → navigate to **App Publishing**.

2. **Upload APK** — upload your signed `app-release.apk`.

3. **Fill in App Information**:

   | Field                | Value                                                  |
   | -------------------- | ------------------------------------------------------ |
   | App Name             | `XASH Pos` (must match `android:label` in the manifest) |
   | Package Name         | `co.zw.xash.pos`                                    |
   | Category             | Business / POS                                         |
   | Supported Devices    | Select target Sunmi models (V2, P2, T2, etc.)          |
   | Region               | Select your target regions                             |
   | Description          | Clear description of POS functionality                 |
   | Screenshots          | 3–5 screenshots, no watermarks, consistent orientation |
   | Privacy Policy URL   | Your privacy policy URL                                |
   | Test Account         | Provide if the app requires login                      |

4. **Confirm & Submit** for review.

> [!NOTE]
> Sunmi reviews typically take **2–5 business days**. They run automated security scans followed by a manual functional review.

### Copy/Paste App Store Content (for this app)

Use these values directly:

| Field | Value |
| --- | --- |
| App Name | `XASH Pos` |
| Package Name | `co.zw.xash.pos` |
| Category | `Business` |
| Supported Devices | `Portrait (Mobile, Desktop)` |
| Short Description | `Fast, reliable point-of-sale for retail teams with offline-ready sales and receipt printing.` |
| Full Description | `XASH Pos is a retail point-of-sale app for cashier and manager workflows. It supports quick checkout, product search, barcode scanning, multiple payment methods, receipt printing, and offline-first operation. Managers can review sales, products, inventory, and reports from the same mobile workflow. Built for daily shop-floor speed and reliability.` |
| Keywords | `POS, point of sale, retail, cashier, inventory, sales, receipt, barcode` |

### App Icon Upload Fix (Invalid dimensions/size)

Sunmi icon field requires:
- Format: `PNG/JPG/JPEG`
- File size: **10KB to 200KB**
- Recommended dimensions: **144x144**

Use this exact file:
- `mobileapp/assets/sunmi-app-icon-144.png` (`144x144`, `11314` bytes)

Fallback:
- `mobileapp/assets/sunmi-app-icon-144.jpg` (`144x144`, `11238` bytes)

> [!WARNING]
> Do not upload `mobileapp/assets/logo.png` to this form field. It is `1024x1024` and may be rejected.

### Screenshots to Upload

Use these cleaned screenshots:
- `public/screenshots/sunmi-screenshot-01.jpg`
- `public/screenshots/sunmi-screenshot-02.jpg`
- `public/screenshots/sunmi-screenshot-03.jpg`
- `public/screenshots/sunmi-screenshot-04.jpg`
- `public/screenshots/sunmi-screenshot-05.jpg`
- `public/screenshots/sunmi-screenshot-06.jpg`

---

## 8. Sunmi Review Checklist

Before submitting, verify your app meets Sunmi's review standards:

### ✅ Basic Information
- [ ] App name matches `android:label` in `AndroidManifest.xml`
- [ ] Description accurately reflects the app's functions
- [ ] Screenshots are clear, no watermarks, consistent orientation
- [ ] No illegal or inappropriate content in description

### ✅ Functional Requirements
- [ ] App launches without crashing on target Sunmi devices
- [ ] App installs and uninstalls cleanly
- [ ] Page layout is correct (no misalignment or overflow)
- [ ] All functional modules work smoothly to completion
- [ ] Printer integration works on Sunmi hardware
- [ ] Barcode scanner integration works on Sunmi hardware

### ✅ Safety & Permissions
- [ ] Only necessary permissions are requested (`INTERNET`, `CAMERA`)
- [ ] No excessive network traffic consumption
- [ ] No ROOT-level code
- [ ] No intrusive floating window advertisements
- [ ] APK is signed with V1 + V2 signatures

### ✅ Testing on Device
- [ ] Test on at least one physical Sunmi device before submitting
- [ ] Confirm printer receipt formatting is correct
- [ ] Confirm barcode scanning via hardware scanner button works
- [ ] Verify network requests work correctly on the device

---

## 9. Post-Release & Updates

### Updating the App

1. Increment `versionCode` and update `versionName` in `build.gradle`
2. Build a new release APK (`npm run sunmi:build`)
3. Go to Partner Platform → App Publishing → select your app → **Upload Update**
4. Provide update notes describing what changed
5. Submit for re-review

### Distribution Options

- **Regional deployment** — release to specific countries first
- **Device targeting** — deploy to specific Sunmi models only
- **Auto-update** — Sunmi supports auto-update over Wi-Fi for managed fleets
- **Blacklist/Whitelist** — operators can control which devices receive your app

---

## 10. Sunmi-Specific Features in This App

This app already includes Sunmi hardware integrations:

### Printer (`capacitor-sunmi-printer-v7`)

- **Plugin**: `capacitor-sunmi-printer-v7@^0.5.7`
- **Config**: Auto-binds on load via `capacitor.config.ts`
- **Service**: `src/services/print.js` — handles receipt printing with text formatting

### Hardware Barcode Scanner

- **Plugin**: Custom `src/plugins/sunmiScanner.ts`
- **Composable**: `src/composables/useBarcodeScanner.js` — listens for hardware scan events

### Capacitor Config Reference

```typescript
// capacitor.config.ts
{
    appId: 'co.zw.xash.pos',
    appName: 'XASH Pos',
    webDir: 'dist',
    plugins: {
        SunmiPrinter: { bindOnLoad: true }
    }
}
```

---

## 11. Troubleshooting

### APK rejected: "Invalid signature"
Ensure both V1 and V2 signing are enabled in `signingConfigs.release`. Sunmi requires V1 (JAR signing).

### Printer not binding on device
- Check that `SunmiPrinter.bindOnLoad` is `true` in `capacitor.config.ts`
- Verify the device is a genuine Sunmi device (the AIDL service is pre-installed)
- Check logcat: `adb logcat | grep -i sunmi`

### Build fails with "SDK not found"
Ensure `local.properties` in `android/` points to your SDK:
```properties
sdk.dir=/home/YOUR_USER/Android/Sdk
```

### Gradle out of memory
Increase heap in `gradle.properties`:
```properties
org.gradle.jvmargs=-Xmx2048m
```

### Testing without a Sunmi device
- The app gracefully degrades — printer and scanner features are skipped on non-Sunmi devices
- Use `npm run dev` (Vite dev server) for web-based UI testing
- Use a standard Android emulator for basic APK testing (hardware features won't work)

---

## Quick Reference

```bash
# Full build pipeline (from mobileapp/ directory)
npm install
npm run build
npx cap sync android
cd android && ./gradlew assembleRelease

# Find your APK
ls -la android/app/build/outputs/apk/release/app-release.apk

# Verify signature
$ANDROID_HOME/build-tools/35.0.0/apksigner verify --verbose \
  android/app/build/outputs/apk/release/app-release.apk
```
