# XASHPOS - Point of Sale System

A comprehensive multi-portal Point of Sale (POS) system built with Laravel and Vue.js, designed for retail operations with support for admin management, cashier operations, and mobile POS terminals.

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [System Requirements](#system-requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Database Setup](#database-setup)
- [Mobile App Setup](#mobile-app-setup)
- [Default Credentials](#default-credentials)
- [Project Structure](#project-structure)
- [API Documentation](#api-documentation)
- [Development Workflow](#development-workflow)
- [Common Tasks](#common-tasks)
- [Troubleshooting](#troubleshooting)

## Features

### Admin Portal
- **Dashboard**: Real-time sales analytics and business insights
- **Product Management**: Create, edit, and manage products with categories, variants, and units
- **Inventory Management**: Track stock levels, transfers, and adjustments across branches
- **User Management**: Manage staff with role-based access (Admin, Manager, Cashier, Stockist)
- **Branch Management**: Multi-branch support with branch-specific pricing and stock
- **Customer Management**: Track customer information and loyalty points
- **Supplier Management**: Manage suppliers and purchase orders
- **Activity Logs**: Comprehensive audit trail of all system activities
- **Settings**: Configure company details, payment methods, taxes, and receipts

### Mobile POS Terminal (Cashier Portal)
- **Product Selection**: Browse and search products by name, barcode, or SKU
- **Barcode Scanning**: Quick product lookup via barcode
- **Cart Management**: Add, remove, and adjust quantities in cart
- **Stock Validation**: Real-time stock checking to prevent overselling
- **Multiple Payment Methods**: Support for cash, card, and mobile money
- **Quick Checkout**: Fast payment processing with change calculation
- **Offline Support**: Continue operations with limited connectivity
- **Receipt Generation**: Print or email receipts after sale

### Inventory Features
- Multi-unit support (dozen, pack, case, etc.)
- Unit conversions and relationships
- Stock movement tracking
- Batch and expiry date tracking
- Reorder level alerts
- Branch-specific pricing

## Tech Stack

### Backend
- **Framework**: Laravel 12
- **Database**: MySQL 8.0+
- **Authentication**: Laravel Sanctum (API tokens)
- **Authorization**: Role-based access control

### Frontend (Admin Portal)
- **Framework**: Vue 3 (Composition API)
- **UI Framework**: Inertia.js v2
- **Styling**: Tailwind CSS v4
- **Icons**: Font Awesome
- **Build Tool**: Vite

### Mobile App
- **Framework**: Vue 3 + Capacitor
- **State Management**: Pinia
- **Routing**: Vue Router
- **Styling**: Tailwind CSS v4
- **API Client**: Fetch API with wrapper

## System Requirements

- PHP >= 8.2
- Composer >= 2.6
- Node.js >= 18.x
- npm >= 9.x
- MySQL >= 8.0
- Git

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/xashpos.git
cd xashpos
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node Dependencies

```bash
npm install
```

### 4. Environment Configuration

```bash
# Copy the example environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 5. Configure Environment Variables

Edit `.env` file with your settings:

```env
APP_NAME=XASHPOS
APP_ENV=local
APP_URL=http://127.0.0.1:8001

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=xashpos
DB_USERNAME=root
DB_PASSWORD=

# Optional: Email configuration for receipts
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
```

## Database Setup

### 1. Create Database

```bash
# Create MySQL database
mysql -u root -p
CREATE DATABASE xashpos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### 2. Run Migrations

```bash
php artisan migrate
```

### 3. Seed Database (Optional - for demo data)

```bash
php artisan db:seed
```

This will create:
- Demo company: XASH Demo Store
- Main branch in Harare
- Admin user: `admin@xashpos.com` / `password`
- Cashier user: `cashier@xashpos.com` / `password`
- Sample activity logs

### 4. Create Storage Symlink

```bash
php artisan storage:link
```

## Mobile App Setup

The mobile POS app is located in the `mobileapp` directory.

### 1. Navigate to Mobile App Directory

```bash
cd mobileapp
```

### 2. Install Dependencies

```bash
npm install
```

### 3. Configure API URL

Create/edit `mobileapp/.env`:

```env
VITE_API_URL='http://127.0.0.1:8001/api'
```

### 4. Run Development Server

```bash
npm run dev
```

The mobile app will be available at `http://localhost:3000`

### 5. Build for Production

```bash
npm run build
```

## Default Credentials

After running the seeder, you can log in with:

### Admin Portal (`/admin/dashboard`)
- **Email**: admin@xashpos.com
- **Password**: password

### Cashier Mobile App (`/login`)
- **Email**: cashier@xashpos.com
- **Password**: password
- **Phone**: +263771234567
- **PIN**: 123456 (set after first login)

## Project Structure

```
xashpos/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          # Admin portal controllers
│   │   │   └── POS/            # Mobile POS API controllers
│   │   └── Middleware/
│   ├── Models/                 # Eloquent models
│   └── Services/               # Business logic services
├── database/
│   ├── migrations/             # Database migrations
│   └── seeders/                # Database seeders
├── resources/
│   ├── js/
│   │   ├── Components/         # Vue components
│   │   ├── Layouts/            # Admin layout components
│   │   └── Pages/              # Inertia pages
│   └── views/                  # Blade templates
├── routes/
│   ├── web.php                 # Web routes
│   ├── api.php                 # API routes (POS)
│   └── admin.php               # Admin portal routes
├── mobileapp/                  # Mobile POS application
│   ├── src/
│   │   ├── pages/              # Vue pages
│   │   ├── stores/             # Pinia stores
│   │   ├── router/             # Vue Router
│   │   └── helpers/            # Utility functions
│   └── public/                 # Static assets
└── public/
    └── storage/                # Public file storage (symlinked)
```

## API Documentation

### Authentication

All POS API endpoints require authentication using Laravel Sanctum tokens.

#### Login (POS)
```http
POST /api/pos/login
Content-Type: application/json

{
  "phone_number": "+263771234567",
  "pin": "123456"
}
```

Response:
```json
{
  "user": { ... },
  "token": "1|xxxxxxxx"
}
```

### POS Endpoints

All endpoints are prefixed with `/api/pos` and require `Authorization: Bearer {token}` header.

#### Get Products
```http
GET /api/pos/products?category_id=1&search=product
```

#### Get Categories
```http
GET /api/pos/categories
```

#### Scan Barcode
```http
POST /api/pos/scan-barcode
Content-Type: application/json

{
  "barcode": "1234567890"
}
```

#### Create Sale
```http
POST /api/pos/sales
Content-Type: application/json

{
  "items": [
    {
      "product_id": 1,
      "quantity": 2,
      "unit_price": 10.00,
      "unit": "piece"
    }
  ],
  "payment_method": "cash",
  "amount_paid": 25.00,
  "customer_id": null,
  "discount_amount": 0,
  "notes": ""
}
```

#### Get Sales History
```http
GET /api/pos/sales?date=2025-12-23&status=completed
```

#### Dashboard Stats
```http
GET /api/pos/dashboard
```

## Development Workflow

### Start Development Servers

#### Backend (Laravel)
```bash
php artisan serve --port=8001
```

#### Frontend (Admin Portal)
```bash
npm run dev
```

#### Mobile App
```bash
cd mobileapp
npm run dev
```

### Code Formatting

```bash
# Format PHP code (Laravel Pint)
vendor/bin/pint

# Format only modified files
vendor/bin/pint --dirty
```

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/SaleTest.php

# Run with coverage
php artisan test --coverage
```

## Common Tasks

### Create a New Admin User

```bash
php artisan tinker
```

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::create([
    'company_id' => 1,
    'name' => 'New Admin',
    'email' => 'newadmin@example.com',
    'password' => Hash::make('password'),
    'role' => 'admin',
    'is_active' => true,
    'email_verified_at' => now(),
]);
```

### Reset Database

```bash
php artisan migrate:fresh --seed
```

### Clear Caches

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Generate IDE Helper Files

```bash
composer require --dev barryvdh/laravel-ide-helper
php artisan ide-helper:generate
php artisan ide-helper:models
```

## Troubleshooting

### Issue: 419 CSRF Token Mismatch

**Solution**: Clear browser cache and cookies, or regenerate the app key:
```bash
php artisan key:generate
```

### Issue: Storage symlink not working

**Solution**: Remove and recreate the symlink:
```bash
rm public/storage
php artisan storage:link
```

### Issue: Migration errors about existing tables

**Solution**: Check migration status and manually mark as run if needed:
```bash
php artisan migrate:status

# If table exists but not in migrations table
php artisan tinker
DB::table('migrations')->insert([
    'migration' => '2025_12_23_xxxxx_table_name',
    'batch' => DB::table('migrations')->max('batch') + 1
]);
```

### Issue: Mobile app can't connect to API

**Solution**:
1. Check `mobileapp/.env` has correct API URL
2. Ensure backend server is running on correct port
3. Restart mobile app dev server: `npm run dev`

### Issue: Images not loading in mobile app

**Solution**:
1. Verify storage symlink exists: `php artisan storage:link`
2. Check file permissions on `storage/app/public`
3. Verify `VITE_API_URL` in `mobileapp/.env` is correct

### Issue: Stock movement errors

**Solution**: The `stock_movements` table requires `balance_after` field. This is automatically handled by the `SaleController`.

## Stock Tracking

### Overview

XASHPOS includes comprehensive stock tracking capabilities. When products are marked with `track_stock = true`, the system automatically tracks inventory levels.

### How Stock Tracking Works

#### Automatic Stock Updates

Stock levels are **automatically updated** when:

1. **Sales are completed** (`SaleController.php`)
   - Stock is decremented when a sale is processed
   - A stock movement record is created with type `sale`
   - Each sale item reduces the branch's stock quantity

2. **Sales are voided/cancelled**
   - Stock is restored to previous levels
   - A stock movement record is created with type `sale_cancelled`

#### Manual Stock Management

You can manually adjust stock through the **Admin Portal**:

##### Stock Adjustments

Access: **Admin Portal → Inventory → Stock Adjustments**

To create a manual stock adjustment:
1. Navigate to Inventory > Stock Adjustments
2. Click "Create Adjustment"
3. Fill in the form:
   - **Product**: Select the product
   - **Branch**: Select the branch
   - **Quantity**: Enter the adjustment (+10 to add, -10 to remove)
   - **Reason**: Document why the adjustment is needed (e.g., "Damaged goods", "Stock count correction")
   - **Notes**: Optional additional details

The system will:
- Update the stock quantity
- Create a stock movement record with type `adjustment`
- Log who made the adjustment and when

##### Stock Transfers

Access: **Admin Portal → Inventory → Transfers**

To transfer stock between branches:
1. Navigate to Inventory > Transfers
2. Click "Create Transfer"
3. Fill in the form:
   - **Product**: Select the product
   - **From Branch**: Source branch
   - **To Branch**: Destination branch
   - **Quantity**: Amount to transfer
   - **Notes**: Optional transfer details

The system will:
- Deduct stock from the source branch
- Add stock to the destination branch
- Create two stock movement records (transfer_out and transfer_in)
- Validate that source branch has sufficient stock

### Stock Movement Types

The system tracks the following movement types:
- `purchase` - Stock received from suppliers
- `sale` - Stock sold to customers (automatic)
- `sale_cancelled` - Stock restored from voided sales (automatic)
- `adjustment` - Manual stock corrections
- `transfer_in` - Stock received from another branch
- `transfer_out` - Stock sent to another branch
- `damage` - Damaged or expired goods
- `return` - Customer returns

### Viewing Stock Levels

#### Admin Portal
- **Inventory Overview**: View all stock levels across branches
- **Low Stock Alerts**: See products below reorder levels
- **Stock Movements**: View complete audit trail of all stock changes

#### Mobile POS
- Stock levels are shown on each product card
- Out-of-stock products are clearly marked
- The system prevents selling more than available stock

### Best Practices

1. **Regular Stock Counts**: Perform physical stock counts and use adjustments to correct discrepancies
2. **Document Adjustments**: Always provide clear reasons for manual adjustments
3. **Set Reorder Levels**: Configure appropriate reorder levels for each product
4. **Monitor Low Stock**: Check low stock alerts regularly
5. **Review Movement History**: Use stock movements to investigate discrepancies

## License

This project is proprietary software. All rights reserved.

## Support

For support, please contact the development team or create an issue in the repository.

---

**Built with Laravel 12, Vue 3, and Tailwind CSS**
