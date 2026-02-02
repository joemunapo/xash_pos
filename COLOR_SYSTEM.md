# XASH POS Color System Documentation

## Overview
The XASH POS application now uses a centralized color system that makes it easy to change theme colors throughout the entire application.

## Color Palette

### Primary Colors
- **Primary (Brand)**: `#1669C5` - Used for headers, main buttons, and brand elements
- **Success**: `#00A86B` - Used for payments, balances, and completed states
- **Accent/Warning**: `#FF9F1C` - Used for pending states, alerts, and action items
- **Error/Danger**: `#D32F2F` - Used for failed states and warnings

### Background Colors
- **White**: `#FFFFFF`
- **Light Grey**: `#F4F6F8`

### Text Colors
- **Primary**: `#1F2933`
- **Secondary**: `#4A4A4A`

## How to Change Colors

### Method 1: Update CSS Variables (Recommended)
The easiest way to change colors is to update the CSS custom properties in `resources/css/app.css`:

```css
:root {
    /* Primary (Brand / Header / Main Buttons) */
    --color-primary-light: #1669C5;
    --color-primary-dark: #4A9AE8;

    /* Secondary/Accent (Pending / Alerts / Action) */
    --color-accent-light: #FF9F1C;
    --color-accent-dark: #FFB84D;

    /* Success (Payments / Balances / Completed) */
    --color-success-light: #00A86B;
    --color-success-dark: #2DD68F;

    /* Destructive / Error (Failed / Warnings) */
    --color-destructive-light: #D32F2F;
    --color-destructive-dark: #EF4343;

    /* Background Colors */
    --color-bg-white: #FFFFFF;
    --color-bg-light-grey: #F4F6F8;

    /* Text Colors */
    --color-text-primary: #1F2933;
    --color-text-secondary: #4A4A4A;
}
```

### Method 2: Update Tailwind Color Palettes
For more granular control, update the Tailwind color palettes in `resources/css/app.css`:

```css
@theme inline {
    /* Brand / Primary - Blue (#1669C5) */
    --color-brand-50: #EBF2FB;
    --color-brand-100: #D1E3F6;
    --color-brand-200: #A3C7ED;
    --color-brand-300: #75ABE4;
    --color-brand-400: #478FDB;
    --color-brand-500: #1669C5;  /* Main brand color */
    --color-brand-600: #12549E;
    --color-brand-700: #0D3F77;
    --color-brand-800: #092A50;
    --color-brand-900: #041529;

    /* Success - Green (#00A86B) */
    --color-success-500: #00A86B;  /* Main success color */
    /* ... other shades ... */

    /* Warning/Accent - Orange (#FF9F1C) */
    --color-warn-500: #FF9F1C;  /* Main warning color */
    /* ... other shades ... */

    /* Danger/Error - Red (#D32F2F) */
    --color-danger-500: #D32F2F;  /* Main danger color */
    /* ... other shades ... */
}
```

## Where Colors Are Used

### SuperAdmin Portal
- **Brand colors** (`brand-*`): Buttons, links, active states, branding elements
- **Success colors** (`success-*`): Active tenant badges, activate buttons
- **Warning colors** (`warn-*`): Trial status badges, expiring subscription alerts
- **Danger colors** (`danger-*`): Suspended status badges, delete/suspend buttons

### Login Pages
- **Brand colors**: Logo gradient, button backgrounds, focus states
- **Danger colors**: Form validation errors

### Components Updated
1. `resources/css/app.css` - Root color variables
2. `resources/js/Pages/SuperAdmin/Dashboard.vue`
3. `resources/js/Pages/SuperAdmin/Tenants/Index.vue`
4. `resources/js/Pages/SuperAdmin/Plans/Index.vue`
5. `resources/js/Layouts/SuperAdminLayout.vue`
6. `resources/js/Pages/Auth/Login.vue`

## Color Usage Guidelines

### When to Use Each Color

**Brand (Blue #1669C5)**
- Primary buttons and CTAs
- Active navigation items
- Logo and branding
- Links and interactive elements
- Primary metrics and totals

**Success (Green #00A86B)**
- Completed/successful states
- Active/healthy status
- Positive metrics (payments, balances)
- Confirmation actions

**Warning/Accent (Orange #FF9F1C)**
- Pending/in-progress states
- Items requiring attention
- Trial periods
- Alerts that aren't critical

**Danger (Red #D32F2F)**
- Error states
- Failed operations
- Destructive actions (delete, suspend)
- Critical warnings
- Form validation errors

## Example: Changing the Primary Brand Color

To change from blue to purple:

1. Open `resources/css/app.css`
2. Find the Brand color palette
3. Update the values:
```css
/* Brand / Primary - Purple (#7C3AED) */
--color-brand-50: #F5F3FF;
--color-brand-100: #EDE9FE;
--color-brand-200: #DDD6FE;
--color-brand-300: #C4B5FD;
--color-brand-400: #A78BFA;
--color-brand-500: #7C3AED;  /* Changed to purple */
--color-brand-600: #6D28D9;
--color-brand-700: #5B21B6;
--color-brand-800: #4C1D95;
--color-brand-900: #3B0764;
```

4. Save the file and rebuild:
```bash
npm run build
```

## Notes

- All colors automatically support dark mode with appropriate variants
- The color system uses Tailwind CSS utilities for consistency
- Changing a color in the CSS variables will update it everywhere it's used
- No need to search and replace colors in individual components
