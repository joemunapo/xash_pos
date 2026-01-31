# Inventory Module Specification

## Overview
Professional stock management with GRN, suppliers, expiry tracking, and batch control.

---

## Features

### Stock Management
- Real-time stock levels per branch
- Multi-warehouse support
- Stock valuation (FIFO, Weighted Average)
- Stock alerts (low, out-of-stock)

### Batch & Expiry Tracking
- Batch/lot number assignment
- Expiry date tracking
- FEFO (First Expired, First Out) suggestions
- Expiring soon alerts
- Expired stock quarantine

### Stock Adjustments
- Physical count adjustments
- Damage write-offs
- Stock transfers between branches
- Adjustment reason codes
- Audit trail

### Purchase Orders
- Create PO from reorder points
- Supplier selection
- Multi-product POs
- PO approval workflow
- PO status tracking

### Goods Received Notes (GRN)
- Receive against PO
- Direct receiving (no PO)
- Partial receiving
- Cost price capture
- Batch assignment on receive

---

## Database Tables

```
products
├── id
├── category_id
├── name
├── sku
├── barcode
├── plu_code
├── description
├── unit
├── tax_rate
├── is_active
├── track_expiry
├── track_batches
└── timestamps

product_variants
├── id
├── product_id
├── name
├── sku
├── barcode
├── price_modifier
└── timestamps

stock
├── id
├── branch_id
├── product_id
├── variant_id
├── batch_id
├── quantity
├── reorder_level
├── reorder_quantity
└── timestamps

batches
├── id
├── product_id
├── batch_number
├── expiry_date
├── cost_price
├── quantity_received
├── quantity_remaining
└── timestamps

suppliers
├── id
├── name
├── contact_person
├── phone
├── email
├── address
├── payment_terms
└── timestamps

purchase_orders
├── id
├── supplier_id
├── branch_id
├── po_number
├── status
├── expected_date
├── notes
└── timestamps

grns
├── id
├── purchase_order_id
├── branch_id
├── grn_number
├── received_by
├── received_date
└── timestamps
```

---

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/stock` | Get stock levels |
| POST | `/api/stock/adjust` | Stock adjustment |
| POST | `/api/stock/transfer` | Inter-branch transfer |
| GET | `/api/purchase-orders` | List POs |
| POST | `/api/purchase-orders` | Create PO |
| POST | `/api/grns` | Create GRN |

---

*Module Owner: TBD*  
*Last Updated: December 2025*
