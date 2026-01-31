# Multi-Branch Module Specification

## Overview
Centralized control with branch-specific customizations for multi-store operations.

---

## Features

### Branch Management
- Create/edit/deactivate branches
- Branch details (name, address, contact)
- Operating hours configuration
- Branch-specific settings

### Centralized Catalog
- Master product catalog
- Central pricing control
- Branch-specific price overrides
- Tax configuration per branch

### User & Permissions
- Assign users to branches
- Branch-specific roles
- Cross-branch access permissions
- Manager hierarchy

### Stock Transfers
- Request stock from other branches
- Approve/reject transfer requests
- Track in-transit stock
- Auto-update stock levels on completion

### Consolidated Reporting
- Company-wide dashboard
- Branch comparison metrics
- Drill-down by branch
- Multi-branch inventory view

---

## Database Tables

```
branches
├── id
├── company_id
├── name
├── code
├── address
├── phone
├── email
├── currency
├── tax_rate
├── is_active
├── settings (JSON)
└── timestamps

branch_user
├── branch_id
├── user_id
├── role
├── is_primary
└── timestamps

branch_product_prices
├── id
├── branch_id
├── product_id
├── price
├── effective_from
└── timestamps

stock_transfers
├── id
├── from_branch_id
├── to_branch_id
├── requested_by
├── approved_by
├── status
├── notes
└── timestamps

stock_transfer_items
├── id
├── transfer_id
├── product_id
├── quantity_requested
├── quantity_sent
├── quantity_received
└── timestamps
```

---

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/branches` | List branches |
| POST | `/api/branches` | Create branch |
| PUT | `/api/branches/{id}` | Update branch |
| POST | `/api/transfers` | Request transfer |
| PUT | `/api/transfers/{id}/approve` | Approve transfer |
| PUT | `/api/transfers/{id}/complete` | Complete transfer |

---

*Module Owner: TBD*  
*Last Updated: December 2025*
