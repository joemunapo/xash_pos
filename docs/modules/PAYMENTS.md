# Payment Integrations Module Specification

## Overview
Multi-payment support including mobile money, cash, card, and Zimbabwe-specific payment methods.

---

## Supported Payment Methods

| Method | Type | Currencies |
|--------|------|------------|
| Cash | Physical | USD, ZWL |
| EcoCash | Mobile Money | ZWL |
| Mukuru | Mobile Money | USD, ZWL |
| Paynow | Gateway | USD, ZWL |
| DPO | Gateway | USD |
| Card | POS Terminal | USD |

---

## Features

### Multi-Currency
- USD as primary
- ZWL as secondary
- Daily exchange rate updates
- Currency conversion at checkout
- Rounding rules per currency

### Split Payments
- Multiple methods per sale
- Partial payments
- Balance calculation
- Mixed currency payments

### Payment Processing
- Real-time validation
- Transaction reference generation
- Payment status tracking
- Automatic reconciliation

### Refunds
- Full/partial refunds
- Refund to original method
- Refund approval workflow
- Refund reason tracking

---

## Integration Details

### EcoCash
```php
// EcoCash API Integration
POST /api/payments/ecocash
{
  "phone": "0771234567",
  "amount": 150.00,
  "reference": "SALE-001234"
}
```

### Paynow
```php
// Paynow Integration
POST /api/payments/paynow
{
  "amount": 25.00,
  "email": "customer@email.com",
  "reference": "SALE-001234",
  "return_url": "..."
}
```

### Mukuru
```php
// Mukuru Integration
POST /api/payments/mukuru
{
  "phone": "0771234567",
  "amount": 100.00,
  "currency": "USD"
}
```

---

## Database Tables

```
payments
├── id
├── sale_id
├── payment_method
├── amount
├── currency
├── exchange_rate
├── reference
├── external_reference
├── status
├── gateway_response (JSON)
├── processed_at
└── timestamps

payment_methods
├── id
├── name
├── code
├── type
├── currencies (JSON)
├── is_active
├── config (JSON encrypted)
└── timestamps

exchange_rates
├── id
├── from_currency
├── to_currency
├── rate
├── source
├── effective_date
└── timestamps
```

---

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/payments/methods` | Available methods |
| POST | `/api/payments/process` | Process payment |
| GET | `/api/payments/{id}/status` | Check status |
| POST | `/api/payments/{id}/refund` | Process refund |
| GET | `/api/exchange-rates` | Current rates |

---

## Webhook Events

```
payment.completed
payment.failed
payment.refunded
exchange_rate.updated
```

---

*Module Owner: TBD*  
*Last Updated: December 2025*
