# Customer & Loyalty Module Specification

## Overview
Advanced customer relationship and tiered loyalty program with coupons and membership QR codes.

---

## Features

### Customer Management
- Customer registration
- Profile management
- Purchase history
- Customer notes
- Contact preferences

### Loyalty Program
- Points earning on purchases
- Points redemption
- Tiered membership levels
- Member benefits per tier
- Points expiry rules

### Membership Tiers
| Tier | Points Required | Benefits |
|------|-----------------|----------|
| Bronze | 0 | 1x points earning |
| Silver | 1,000 | 1.5x points, 5% discount |
| Gold | 5,000 | 2x points, 10% discount |
| Platinum | 10,000 | 3x points, 15% discount, priority |

### Coupons & Vouchers
- Percentage discounts
- Fixed amount discounts
- BOGO (Buy One Get One)
- Usage limits
- Validity periods
- Customer-specific coupons

### Member Identification
- QR code scanning at POS
- Phone number lookup
- Loyalty card barcode
- App-based identification

---

## Database Tables

```
customers
├── id
├── first_name
├── last_name
├── phone
├── email
├── address
├── date_of_birth
├── gender
├── loyalty_tier
├── loyalty_points
├── member_since
├── qr_code
└── timestamps

loyalty_transactions
├── id
├── customer_id
├── sale_id
├── type (earn/redeem)
├── points
├── balance_after
├── description
└── timestamps

coupons
├── id
├── code
├── type (percentage/fixed/bogo)
├── value
├── min_purchase
├── max_discount
├── usage_limit
├── usage_count
├── customer_id (nullable)
├── valid_from
├── valid_until
├── is_active
└── timestamps

coupon_usages
├── id
├── coupon_id
├── customer_id
├── sale_id
├── discount_applied
└── timestamps
```

---

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/customers` | List customers |
| POST | `/api/customers` | Create customer |
| GET | `/api/customers/{id}` | Customer details |
| GET | `/api/customers/{id}/history` | Purchase history |
| POST | `/api/loyalty/earn` | Earn points |
| POST | `/api/loyalty/redeem` | Redeem points |
| POST | `/api/coupons/validate` | Validate coupon |

---

*Module Owner: TBD*  
*Last Updated: December 2025*
