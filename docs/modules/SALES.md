# Sales Module Specification

## Overview
Ultra-fast Sunmi-optimized checkout system for XASH POS.

---

## Features

### Product Search
- Barcode scanning (hardware + camera)
- QR code scanning
- PLU (Price Look-Up) codes
- Text search with fuzzy matching
- Recent items quick access

### Cart Management
- Add/remove items
- Quantity adjustments
- Price overrides (with permission)
- Item-level discounts
- Cart-level discounts
- Hold transaction
- Recall held transactions

### Checkout Flow
- Customer assignment (optional)
- Loyalty points application
- Coupon/voucher redemption
- Payment method selection
- Receipt generation
- Cash drawer trigger

### Split Payments
- Multiple payment methods per transaction
- Supported methods:
  - Cash (USD/ZWL)
  - EcoCash
  - Mukuru
  - Card
  - Mixed combinations

---

## Database Tables

```
sales
├── id
├── branch_id
├── user_id (cashier)
├── customer_id (nullable)
├── sale_number
├── subtotal
├── discount_amount
├── tax_amount
├── total
├── payment_status
├── created_at
└── updated_at

sale_items
├── id
├── sale_id
├── product_id
├── variant_id (nullable)
├── quantity
├── unit_price
├── discount
├── tax_amount
├── total
└── created_at

sale_payments
├── id
├── sale_id
├── payment_method
├── currency
├── amount
├── reference
├── status
└── created_at
```

---

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/sales` | Create new sale |
| GET | `/api/sales/{id}` | Get sale details |
| POST | `/api/sales/{id}/void` | Void a sale |
| POST | `/api/sales/{id}/refund` | Refund a sale |
| GET | `/api/sales/daily` | Daily sales summary |

---

## Offline Behavior

- All sales stored locally in SQLite
- Sync to server when online
- Unique UUID generation for offline sales
- Conflict resolution via timestamps

---

*Module Owner: TBD*  
*Last Updated: December 2025*
