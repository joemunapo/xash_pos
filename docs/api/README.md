# XASH POS API Documentation

## Overview

RESTful API for XASH POS system. Full API access for third-party integrations and plugin development.

---

## Base URL

```
Production: https://api.xashpos.com/v1
Development: http://localhost:8001/api
```

---

## Authentication

### Token-Based (Sanctum)
```http
Authorization: Bearer {token}
```

### Obtaining Token
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}

Response:
{
  "token": "1|abc123...",
  "user": {...}
}
```

### PIN Login (POS Devices)
```http
POST /api/auth/pin-login
Content-Type: application/json

{
  "branch_id": 1,
  "pin": "1234"
}
```

---

## API Modules

| Module | Base Path | Description |
|--------|-----------|-------------|
| Auth | `/api/auth` | Authentication |
| Users | `/api/users` | User management |
| Branches | `/api/branches` | Branch management |
| Products | `/api/products` | Product catalog |
| Categories | `/api/categories` | Categories |
| Sales | `/api/sales` | Sales transactions |
| Stock | `/api/stock` | Inventory |
| Customers | `/api/customers` | Customer management |
| Reports | `/api/reports` | Reporting |
| Sync | `/api/sync` | Mobile sync |

---

## Common Response Format

### Success
```json
{
  "success": true,
  "data": {...},
  "message": "Operation successful"
}
```

### Error
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid.",
    "details": {...}
  }
}
```

### Paginated
```json
{
  "success": true,
  "data": [...],
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 20,
    "total": 195
  }
}
```

---

## Rate Limiting

| Endpoint Type | Limit |
|---------------|-------|
| Standard | 60 requests/minute |
| Sync | 120 requests/minute |
| Reports | 30 requests/minute |

---

## Webhooks

Register webhook endpoints to receive real-time events:

```http
POST /api/webhooks
{
  "url": "https://your-app.com/webhook",
  "events": ["sale.completed", "stock.low"]
}
```

### Available Events
- `sale.completed`
- `sale.voided`
- `sale.refunded`
- `stock.low`
- `stock.adjusted`
- `customer.created`
- `payment.completed`

---

## SDK

Coming soon: JavaScript SDK for third-party integrations.

```javascript
import { XashPOS } from '@xash/pos-sdk';

const client = new XashPOS({
  apiKey: 'your-api-key',
  baseUrl: 'https://api.xashpos.com/v1'
});

const products = await client.products.list();
```

---

## Detailed Documentation

- [Authentication Guide](./auth.md)
- [Products API](./products.md)
- [Sales API](./sales.md)
- [Inventory API](./inventory.md)
- [Webhooks Guide](./webhooks.md)

---

*API Version: 1.0*  
*Last Updated: December 2025*
