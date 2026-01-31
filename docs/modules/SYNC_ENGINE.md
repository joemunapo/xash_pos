# Sync Engine Module Specification

## Overview
100% offline capability with automatic conflict resolution and background sync scheduling.

---

## Features

### Offline Capability
- Full POS operation without internet
- Local SQLite database
- Cached product catalog
- Locally stored customer data

### Sync Queue
- Queue transactions for sync
- Priority-based processing
- Retry logic for failures
- Queue size monitoring

### Background Sync
- Scheduled sync intervals
- Opportunistic sync on connectivity
- Batch sync optimization
- Delta sync (changes only)

### Conflict Resolution
- Timestamp-based resolution
- Server wins for master data
- Local wins for transactions
- Manual conflict review queue

### Data Integrity
- Checksum verification
- Sync logs and audit trail
- Rollback capability
- Data consistency checks

---

## Sync Priority

| Priority | Data Type | Direction |
|----------|-----------|-----------|
| 1 | Sales/Transactions | Upload |
| 2 | Stock Adjustments | Upload |
| 3 | Product Catalog | Download |
| 4 | Pricing Updates | Download |
| 5 | Customer Data | Bidirectional |
| 6 | Reports Data | Download |

---

## Sync States

```
PENDING     - Queued for sync
SYNCING     - Currently syncing
SYNCED      - Successfully synced
FAILED      - Sync failed, will retry
CONFLICT    - Manual resolution needed
```

---

## Local Database Schema

```sql
-- Sync queue table
CREATE TABLE sync_queue (
  id TEXT PRIMARY KEY,
  entity_type TEXT,
  entity_id TEXT,
  action TEXT,
  payload TEXT,
  priority INTEGER,
  status TEXT DEFAULT 'PENDING',
  attempts INTEGER DEFAULT 0,
  last_error TEXT,
  created_at DATETIME,
  updated_at DATETIME
);

-- Sync log table
CREATE TABLE sync_log (
  id TEXT PRIMARY KEY,
  sync_type TEXT,
  direction TEXT,
  records_count INTEGER,
  started_at DATETIME,
  completed_at DATETIME,
  status TEXT,
  error_message TEXT
);
```

---

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/sync/upload` | Upload local changes |
| GET | `/api/sync/download` | Download server changes |
| GET | `/api/sync/status` | Get sync status |
| POST | `/api/sync/resolve` | Resolve conflicts |

---

## Implementation Notes

### Mobile App (Capacitor)
- Use Capacitor SQLite plugin
- Background sync with WorkManager (Android)
- Network state monitoring
- Sync indicator in UI

### Server (Laravel)
- Webhook notifications for changes
- Batch API endpoints
- Rate limiting for sync
- Sync token for tracking

---

*Module Owner: TBD*  
*Last Updated: December 2025*
