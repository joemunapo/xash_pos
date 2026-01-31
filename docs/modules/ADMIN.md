# Admin Module Specification

## Overview

The Admin Module is the central control hub for business owners to manage their entire POS ecosystem across multiple shops/branches. This module provides complete visibility, configuration control, and business intelligence for decision-making.

**Tech Stack:** Laravel 12 + Inertia.js (Vue 3)

---

## User Roles Hierarchy

```
┌─────────────────────────────────────────────────────────────────┐
│                        BUSINESS OWNER                           │
│                    (Super Admin / Admin)                        │
│         Full access to all branches and features                │
└─────────────────────────────────────────────────────────────────┘
                              │
        ┌─────────────────────┼─────────────────────┐
        ▼                     ▼                     ▼
┌───────────────┐     ┌───────────────┐     ┌───────────────┐
│    MANAGER    │     │    MANAGER    │     │    MANAGER    │
│   Branch A    │     │   Branch B    │     │   Branch C    │
└───────────────┘     └───────────────┘     └───────────────┘
        │                     │                     │
   ┌────┴────┐           ┌────┴────┐           ┌────┴────┐
   ▼         ▼           ▼         ▼           ▼         ▼
┌──────┐ ┌──────┐   ┌──────┐ ┌──────┐   ┌──────┐ ┌──────┐
│Cashier│ │Stockist│ │Cashier│ │Stockist│ │Cashier│ │Stockist│
└──────┘ └──────┘   └──────┘ └──────┘   └──────┘ └──────┘
```

---

## Admin Dashboard

### Overview Widgets
- **Total Revenue Today** - Aggregated across all branches
- **Total Orders Today** - Combined order count
- **Active Branches** - Online/offline status
- **Low Stock Alerts** - Critical stock warnings
- **Top Performing Branch** - Today's leader
- **Underperforming Branches** - Needs attention

### Quick Actions
- Create New Branch
- Add New Product
- View All Reports
- Manage Users
- System Settings

---

## Core Features

### 1. Company Profile Management
- Company name, logo, and branding
- Business registration details
- Tax registration (ZIMRA VAT number)
- Contact information
- Default currency settings
- Fiscal year configuration

### 2. Branch Management
- Create, edit, deactivate branches
- Branch details (name, address, contact)
- Operating hours per branch
- Branch-specific settings:
  - Receipt header/footer
  - Tax rates
  - Currency preferences
  - Printer configurations

### 3. User Management
- Create users with role assignments
- Assign users to branches
- Set user permissions
- PIN management for POS login
- User activity logs
- Deactivate/reactivate users

### 4. Product Catalog (Master)
- Central product database
- Category hierarchy management
- Product creation with variants
- Barcode/SKU assignment
- Default pricing (can be overridden per branch)
- Tax configuration per product
- Bulk import/export (CSV, Excel)

### 5. Pricing Control
- Master price list
- Branch-specific price overrides
- Price tiers (retail, wholesale, member)
- Promotional pricing with date ranges
- Currency-specific pricing (USD/ZWL)
- Bulk price updates

### 6. Supplier Management
- Supplier database
- Contact details and payment terms
- Supplier product mapping
- Purchase history per supplier
- Supplier performance metrics

### 7. Consolidated Reporting
- Company-wide sales reports
- Branch comparison analytics
- Employee performance rankings
- Inventory valuation summary
- Profit & loss overview
- Cash flow summary
- Tax reports for ZIMRA

### 8. System Configuration
- Default settings for new branches
- Payment methods configuration
- Tax rate management
- Receipt templates
- Notification preferences
- Backup and restore settings

---

## Navigation Structure

```
Admin Panel
├── Dashboard
│   ├── Overview
│   ├── Branch Status
│   └── Quick Stats
│
├── Branches
│   ├── All Branches
│   ├── Add New Branch
│   └── Branch Settings
│
├── Products
│   ├── All Products
│   ├── Categories
│   ├── Add Product
│   ├── Bulk Import
│   └── Price Management
│
├── Inventory
│   ├── Stock Overview (All Branches)
│   ├── Stock Transfers
│   ├── Low Stock Alerts
│   └── Expiring Items
│
├── Suppliers
│   ├── All Suppliers
│   ├── Add Supplier
│   └── Purchase Orders
│
├── Users
│   ├── All Users
│   ├── Add User
│   ├── Roles & Permissions
│   └── Activity Logs
│
├── Customers
│   ├── Customer Database
│   ├── Loyalty Program
│   └── Coupons & Vouchers
│
├── Reports
│   ├── Sales Reports
│   ├── Branch Comparison
│   ├── Employee Performance
│   ├── Inventory Reports
│   ├── Financial Reports
│   └── Tax Reports
│
├── Settings
│   ├── Company Profile
│   ├── Payment Methods
│   ├── Tax Configuration
│   ├── Receipt Templates
│   ├── Notifications
│   └── Backup & Restore
│
└── Help & Support
    ├── Documentation
    ├── Contact Support
    └── System Logs
```

---

## Database Tables

```sql
-- Company/Business
companies
├── id
├── name
├── trading_name
├── logo
├── registration_number
├── vat_number
├── address
├── phone
├── email
├── website
├── default_currency
├── fiscal_year_start
├── settings (JSON)
├── is_active
└── timestamps

-- Admin Users
users
├── id
├── company_id
├── name
├── email
├── password
├── phone
├── pin (hashed, for POS)
├── role
├── avatar
├── is_active
├── last_login_at
├── email_verified_at
└── timestamps

-- Roles
roles
├── id
├── company_id
├── name
├── slug
├── description
├── is_system (boolean)
└── timestamps

-- Permissions
permissions
├── id
├── name
├── slug
├── module
├── description
└── timestamps

-- Role-Permission Pivot
role_permissions
├── role_id
├── permission_id
└── timestamps

-- User-Branch Assignment
branch_user
├── id
├── user_id
├── branch_id
├── role_id
├── is_primary
└── timestamps

-- Activity Logs
activity_logs
├── id
├── user_id
├── branch_id
├── action
├── model_type
├── model_id
├── old_values (JSON)
├── new_values (JSON)
├── ip_address
├── user_agent
└── timestamps
```

---

## Permissions Matrix

| Permission | Admin | Manager | Cashier | Stockist |
|------------|-------|---------|---------|----------|
| View Dashboard | ✅ All | ✅ Branch | ❌ | ❌ |
| Manage Branches | ✅ | ❌ | ❌ | ❌ |
| Create Products | ✅ | ✅ | ❌ | ❌ |
| Edit Products | ✅ | ✅ | ❌ | ❌ |
| Delete Products | ✅ | ❌ | ❌ | ❌ |
| View Stock | ✅ All | ✅ Branch | ❌ | ✅ Branch |
| Adjust Stock | ✅ | ✅ | ❌ | ✅ |
| Create Users | ✅ | ✅ Branch | ❌ | ❌ |
| Manage Roles | ✅ | ❌ | ❌ | ❌ |
| Process Sales | ✅ | ✅ | ✅ | ❌ |
| Void Sales | ✅ | ✅ | ❌ | ❌ |
| Issue Refunds | ✅ | ✅ | ❌ | ❌ |
| View Reports | ✅ All | ✅ Branch | ❌ | ❌ |
| Export Data | ✅ | ✅ | ❌ | ❌ |
| System Settings | ✅ | ❌ | ❌ | ❌ |
| Manage Suppliers | ✅ | ✅ | ❌ | ✅ |
| Create PO | ✅ | ✅ | ❌ | ✅ |
| Receive GRN | ✅ | ✅ | ❌ | ✅ |
| Manage Customers | ✅ | ✅ | ✅ View | ❌ |
| Manage Loyalty | ✅ | ✅ | ❌ | ❌ |

---

## API Endpoints

### Company
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/company` | Get company details |
| PUT | `/api/company` | Update company details |
| POST | `/api/company/logo` | Upload company logo |

### Users
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/users` | List all users |
| POST | `/api/users` | Create user |
| GET | `/api/users/{id}` | Get user details |
| PUT | `/api/users/{id}` | Update user |
| DELETE | `/api/users/{id}` | Deactivate user |
| POST | `/api/users/{id}/reset-pin` | Reset user PIN |

### Roles & Permissions
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/roles` | List roles |
| POST | `/api/roles` | Create role |
| PUT | `/api/roles/{id}` | Update role |
| GET | `/api/permissions` | List permissions |
| PUT | `/api/roles/{id}/permissions` | Assign permissions |

### Activity Logs
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/activity-logs` | List activity logs |
| GET | `/api/activity-logs/user/{id}` | User activity |

---

## Inertia.js Pages Structure

```
resources/js/Pages/
├── Admin/
│   ├── Dashboard/
│   │   └── Index.vue
│   │
│   ├── Branches/
│   │   ├── Index.vue
│   │   ├── Create.vue
│   │   ├── Edit.vue
│   │   └── Show.vue
│   │
│   ├── Products/
│   │   ├── Index.vue
│   │   ├── Create.vue
│   │   ├── Edit.vue
│   │   ├── Categories/
│   │   │   ├── Index.vue
│   │   │   └── Create.vue
│   │   └── Import.vue
│   │
│   ├── Users/
│   │   ├── Index.vue
│   │   ├── Create.vue
│   │   ├── Edit.vue
│   │   └── Roles/
│   │       ├── Index.vue
│   │       ├── Create.vue
│   │       └── Edit.vue
│   │
│   ├── Inventory/
│   │   ├── Index.vue
│   │   ├── Transfers/
│   │   │   ├── Index.vue
│   │   │   └── Create.vue
│   │   └── Alerts.vue
│   │
│   ├── Suppliers/
│   │   ├── Index.vue
│   │   ├── Create.vue
│   │   └── Edit.vue
│   │
│   ├── Customers/
│   │   ├── Index.vue
│   │   ├── Create.vue
│   │   ├── Edit.vue
│   │   └── Loyalty/
│   │       └── Index.vue
│   │
│   ├── Reports/
│   │   ├── Index.vue
│   │   ├── Sales.vue
│   │   ├── Inventory.vue
│   │   ├── Employees.vue
│   │   └── Financial.vue
│   │
│   └── Settings/
│       ├── Index.vue
│       ├── Company.vue
│       ├── Payments.vue
│       ├── Tax.vue
│       ├── Receipts.vue
│       └── Notifications.vue
```

---

## Key UI Components

### Data Tables
- Sortable columns
- Search and filter
- Pagination
- Bulk actions
- Export options (CSV, Excel, PDF)

### Forms
- Validation with error display
- Auto-save drafts
- File uploads with preview
- Rich text editor (for descriptions)

### Charts & Analytics
- Line charts (trends)
- Bar charts (comparisons)
- Pie charts (distributions)
- KPI cards with trend indicators

### Notifications
- Toast notifications
- Email alerts
- SMS alerts (optional)
- In-app notification center

---

## Security Features

- Two-factor authentication (optional)
- Session management
- IP whitelist (optional)
- Password policies
- Automatic logout
- Audit trail for all changes
- Role-based access control

---

## Implementation Priority

### Phase 1 (MVP)
1. Company profile setup
2. User CRUD with basic roles
3. Branch management
4. Basic dashboard

### Phase 2
1. Full permissions system
2. Product management
3. Activity logs
4. Basic reports

### Phase 3
1. Advanced analytics
2. Consolidated reporting
3. Notification system
4. Settings panels

---

*Module Owner: TBD*  
*Last Updated: December 2025*
