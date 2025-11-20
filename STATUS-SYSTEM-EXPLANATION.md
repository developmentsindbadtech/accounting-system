# Status System Explanation

## Overview
Different modules use different status systems based on their business logic and workflow requirements.

## Status Types by Module

### 1. **Journal Entries** - Uses `status` (enum)
**Status Values:**
- `draft` (default) - Entry is being prepared, can be edited/deleted
- `posted` - Entry is finalized and posted to the general ledger, cannot be edited/deleted
- `reversed` - Entry has been reversed (for corrections)

**Why "Draft" instead of "Active":**
- Journal entries follow an approval workflow
- They need to be reviewed before being posted to the general ledger
- Once posted, they affect financial statements and should be locked
- This is standard accounting practice (similar to QuickBooks)

**Should you be able to change status?**
✅ **YES** - You need:
- **Post** button: Change from `draft` → `posted` (finalizes the entry)
- **Reverse** button: Change from `posted` → `reversed` (for corrections)
- Once posted, editing/deletion should be blocked

---

### 2. **Invoices** - Uses `status` (enum)
**Status Values:**
- `draft` (default) - Invoice is being prepared
- `sent` - Invoice has been sent to customer
- `paid` - Invoice has been fully paid
- `overdue` - Invoice is past due date
- `cancelled` - Invoice has been cancelled

**Why different from Journal Entries:**
- Invoices follow a customer payment workflow
- Status changes based on customer actions (payment) and time (overdue)
- Can be cancelled if not paid

**Should you be able to change status?**
✅ **YES** - You need:
- **Send** button: Change from `draft` → `sent`
- **Mark as Paid** button: Change from `sent`/`overdue` → `paid`
- **Cancel** button: Change to `cancelled` (if not paid)
- Auto-update to `overdue` when due date passes
- Only `draft` invoices should be editable/deletable

---

### 3. **Bills** - Uses `status` (enum)
**Status Values:**
- `draft` (default) - Bill is being prepared
- `received` - Bill has been received from vendor
- `paid` - Bill has been fully paid
- `overdue` - Bill is past due date
- `cancelled` - Bill has been cancelled

**Why similar to Invoices:**
- Bills follow a vendor payment workflow
- Status changes based on payment actions and time

**Should you be able to change status?**
✅ **YES** - You need:
- **Mark as Received** button: Change from `draft` → `received`
- **Mark as Paid** button: Change from `received`/`overdue` → `paid`
- **Cancel** button: Change to `cancelled` (if not paid)
- Auto-update to `overdue` when due date passes
- Only `draft`/`received` bills should be editable/deletable

---

### 4. **Fixed Assets** - Uses `status` (enum)
**Status Values:**
- `active` (default) - Asset is in use
- `disposed` - Asset has been sold/disposed

**Why "Active" instead of "Draft":**
- Assets are either in use or disposed
- No workflow needed - it's a simple on/off state

**Should you be able to change status?**
✅ **YES** - You need:
- **Dispose** button: Change from `active` → `disposed`
- Once disposed, asset should be locked from further depreciation

---

### 5. **Customers, Vendors, Accounts, Inventory Items** - Uses `is_active` (boolean)
**Status Values:**
- `true` (Active) - Record is active and can be used
- `false` (Inactive) - Record is inactive/hidden but not deleted

**Why "is_active" instead of "status":**
- These are master data records (not transactions)
- Simple on/off toggle is sufficient
- Used for filtering (e.g., only show active customers in dropdowns)

**Should you be able to change status?**
✅ **YES** - You need:
- **Toggle Active/Inactive** checkbox or button
- Inactive records should be hidden from dropdowns but still visible in lists
- Useful for temporarily disabling records without deleting them

---

## Summary Table

| Module | Status Field | Values | Default | Needs Status Change? |
|--------|-------------|--------|---------|---------------------|
| **Journal Entries** | `status` | draft, posted, reversed | draft | ✅ YES - Post/Reverse buttons |
| **Invoices** | `status` | draft, sent, paid, overdue, cancelled | draft | ✅ YES - Send/Mark Paid/Cancel buttons |
| **Bills** | `status` | draft, received, paid, overdue, cancelled | draft | ✅ YES - Mark Received/Paid/Cancel buttons |
| **Fixed Assets** | `status` | active, disposed | active | ✅ YES - Dispose button |
| **Customers** | `is_active` | true, false | true | ✅ YES - Toggle checkbox |
| **Vendors** | `is_active` | true, false | true | ✅ YES - Toggle checkbox |
| **Accounts** | `is_active` | true, false | true | ✅ YES - Toggle checkbox |
| **Inventory Items** | `is_active` | true, false | true | ✅ YES - Toggle checkbox |

---

## Recommended Implementation

### For Transaction Modules (Journal Entries, Invoices, Bills):
1. **Add status change buttons** in the show/edit pages
2. **Enforce business rules:**
   - Only draft entries can be edited/deleted
   - Posted entries cannot be modified
   - Status changes should be logged (who, when)
3. **Add status filters** in index pages (filter by status)

### For Master Data (Customers, Vendors, Accounts, Items):
1. **Add Active/Inactive toggle** in edit forms
2. **Filter inactive records** from dropdowns (but show in lists)
3. **Visual indicator** (badge/icon) for inactive status

---

## Why This Design Makes Sense

1. **Journal Entries** need approval workflow before affecting financials
2. **Invoices/Bills** need payment tracking workflow
3. **Fixed Assets** need disposal tracking
4. **Master Data** just needs simple enable/disable

This follows standard accounting software patterns (QuickBooks, Xero, etc.) and Saudi IFRS requirements for audit trails and transaction integrity.

