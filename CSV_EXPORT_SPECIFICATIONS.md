# CSV Export Specifications - All Modules

This document details the CSV export functionality for all modules in the Accounting System.

---

## ✅ Available CSV Exports

All the following modules have **fully implemented** CSV export functionality:

1. ✅ Chart of Accounts
2. ✅ Journal Entries
3. ✅ Customers
4. ✅ Invoices
5. ✅ Vendors
6. ✅ Bills
7. ✅ Inventory
8. ✅ Fixed Assets

---

## 📊 Export Details by Module

### 1. Chart of Accounts

**Route:** `GET /chart-of-accounts/export`  
**Controller:** `ChartOfAccountsController@export`  
**Filename:** `chart-of-accounts-YYYY-MM-DD-HHmmss.csv`

**CSV Columns:**
```csv
Code, Name, Type, Parent Account, Level, Balance, Status, Updated At, Created At
```

**Example Data:**
```csv
1000, Cash, Asset, , 1, 50000.00, Active, 2025-11-21 10:00:00, 2025-11-01 09:00:00
1100, Accounts Receivable, Asset, , 1, 25000.00, Active, 2025-11-21 11:00:00, 2025-11-01 09:00:00
```

**Features:**
- Includes account hierarchy (parent/child relationships)
- Shows account balances
- Respects tenant isolation
- Supports sorting by: code, name, type, balance, is_active, updated_at, created_at

---

### 2. Journal Entries

**Route:** `GET /journal-entries/export`  
**Controller:** `JournalEntryController@export`  
**Filename:** `journal-entries-YYYY-MM-DD-HHmmss.csv`

**CSV Columns:**
```csv
Entry Number, Entry Date, Description, Reference Number, Total Debit, Total Credit, Status, Created By, Updated At, Created At
```

**Example Data:**
```csv
JE-001, 2025-11-15, Opening Balance, REF-001, 100000.00, 100000.00, posted, Admin User, 2025-11-21 10:00:00, 2025-11-15 09:00:00
JE-002, 2025-11-16, Purchase Equipment, PO-123, 5000.00, 5000.00, posted, Accountant, 2025-11-21 11:00:00, 2025-11-16 10:00:00
```

**Features:**
- Includes entry header information (no line details in export)
- Shows who created the entry
- Status indicator (draft/posted/reversed)
- Supports sorting by: entry_number, entry_date, description, reference_number, total_debit, total_credit, status, updated_at, created_at

---

### 3. Customers

**Route:** `GET /customers/export`  
**Controller:** `CustomerController@export`  
**Filename:** `customers-YYYY-MM-DD-HHmmss.csv`

**CSV Columns:**
```csv
Code, Name, Email, Phone, Address, Tax ID, Credit Limit, Balance, Status, Updated At, Created At
```

**Example Data:**
```csv
CUS-001, ABC Corporation, abc@example.com, +1234567890, 123 Main St, TAX-123456, 100000.00, 25000.00, Active, 2025-11-21 10:00:00, 2025-11-01 09:00:00
CUS-002, XYZ Ltd, xyz@example.com, +1987654321, 456 Oak Ave, TAX-789012, 50000.00, 10000.00, Active, 2025-11-21 11:00:00, 2025-11-02 09:00:00
```

**Features:**
- Complete customer master data
- Shows current balance
- Credit limit tracking
- Supports sorting by: code, name, email, phone, balance, is_active, updated_at, created_at

---

### 4. Invoices

**Route:** `GET /invoices/export`  
**Controller:** `InvoiceController@export`  
**Filename:** `invoices-YYYY-MM-DD-HHmmss.csv`

**CSV Columns:**
```csv
Invoice Number, Customer, Invoice Date, Due Date, Subtotal, VAT Amount, Total, Amount Paid, Status, Updated At, Created At
```

**Example Data:**
```csv
INV-001, ABC Corporation, 2025-11-15, 2025-12-15, 10000.00, 1500.00, 11500.00, 11500.00, paid, 2025-11-21 10:00:00, 2025-11-15 09:00:00
INV-002, XYZ Ltd, 2025-11-16, 2025-12-16, 5000.00, 750.00, 5750.00, 0.00, sent, 2025-11-21 11:00:00, 2025-11-16 10:00:00
```

**Features:**
- Invoice header information
- VAT/Tax calculations
- Payment status tracking
- Supports sorting by: invoice_number, invoice_date, due_date, customer_id, total, status, updated_at, created_at

---

### 5. Vendors

**Route:** `GET /vendors/export`  
**Controller:** `VendorController@export`  
**Filename:** `vendors-YYYY-MM-DD-HHmmss.csv`

**CSV Columns:**
```csv
Code, Name, Email, Phone, Address, Tax ID, Payment Terms, Balance, Status, Updated At, Created At
```

**Example Data:**
```csv
VEN-001, Supplier Inc, supplier@example.com, +1234567890, 789 Supplier Rd, TAX-456789, Net 30, 15000.00, Active, 2025-11-21 10:00:00, 2025-11-01 09:00:00
VEN-002, Vendor Co, vendor@example.com, +1987654321, 321 Vendor St, TAX-012345, Net 60, 8000.00, Active, 2025-11-21 11:00:00, 2025-11-02 09:00:00
```

**Features:**
- Complete vendor master data
- Shows current balance (payables)
- Payment terms tracking
- Supports sorting by: code, name, email, phone, balance, is_active, updated_at, created_at

---

### 6. Bills

**Route:** `GET /bills/export`  
**Controller:** `BillController@export`  
**Filename:** `bills-YYYY-MM-DD-HHmmss.csv`

**CSV Columns:**
```csv
Bill Number, Vendor, Bill Date, Due Date, Subtotal, VAT Amount, Total, Status, Updated At, Created At
```

**Example Data:**
```csv
BILL-001, Supplier Inc, 2025-11-15, 2025-12-15, 8000.00, 1200.00, 9200.00, paid, 2025-11-21 10:00:00, 2025-11-15 09:00:00
BILL-002, Vendor Co, 2025-11-16, 2025-12-16, 3000.00, 450.00, 3450.00, received, 2025-11-21 11:00:00, 2025-11-16 10:00:00
```

**Features:**
- Bill header information
- VAT/Tax calculations
- Payment status tracking
- Supports sorting by: bill_number, bill_date, due_date, vendor_id, total, status, updated_at, created_at

---

### 7. Inventory

**Route:** `GET /inventory/export`  
**Controller:** `InventoryController@export`  
**Filename:** `inventory-YYYY-MM-DD-HHmmss.csv`

**CSV Columns:**
```csv
SKU, Name, Type, Category, Unit of Measure, Standard Cost, Quantity on Hand, Reorder Point, Status, Updated At, Created At
```

**Example Data:**
```csv
SKU-001, Product A, inventory, Electronics, pcs, 100.00, 500.00, 50.00, Active, 2025-11-21 10:00:00, 2025-11-01 09:00:00
SKU-002, Service B, non_inventory, Services, hours, 50.00, 0.00, 0.00, Active, 2025-11-21 11:00:00, 2025-11-02 09:00:00
```

**Features:**
- Complete inventory item data
- Stock levels and reorder points
- Costing information
- Supports sorting by: sku, name, type, standard_cost, quantity_on_hand, is_active, updated_at, created_at

---

### 8. Fixed Assets

**Route:** `GET /fixed-assets/export`  
**Controller:** `FixedAssetController@export`  
**Filename:** `fixed-assets-YYYY-MM-DD-HHmmss.csv`

**CSV Columns:**
```csv
Asset Number, Name, Category, Purchase Date, Purchase Cost, Useful Life (Years), Depreciation Method, Accumulated Depreciation, Net Book Value, Status, Updated At, Created At
```

**Example Data:**
```csv
FA-001, Office Building, Buildings, 2025-01-15, 500000.00, 40, straight-line, 12500.00, 487500.00, active, 2025-11-21 10:00:00, 2025-01-15 09:00:00
FA-002, Company Vehicle, Vehicles, 2025-02-20, 30000.00, 5, straight-line, 5000.00, 25000.00, active, 2025-11-21 11:00:00, 2025-02-20 10:00:00
```

**Features:**
- Complete fixed asset register
- Depreciation tracking
- Net book value calculations
- Asset lifecycle status
- Supports sorting by: asset_number, name, purchase_date, purchase_cost, net_book_value, status, updated_at, created_at

---

## 🔐 Security & Permissions

**Access Control:**
- ✅ Only **Admin** and **Accountant** roles can download CSV files
- ❌ **Viewer** role cannot access export functionality
- All exports respect **tenant isolation** (multi-tenant safe)

**Route Protection:**
All export routes are protected by middleware:
```php
Route::middleware(['tenant.identify', 'auth', 'role:admin,accountant'])
```

---

## 📥 How to Download CSV Files

### For Admin/Accountant Users:

1. Navigate to any list page (e.g., `/customers`, `/invoices`, etc.)
2. Click the **green "Download CSV"** button at the top right
3. CSV file will download immediately with current date/time in filename

### Direct URL Access:

You can also access exports directly via URL:

```
http://localhost:9003/chart-of-accounts/export
http://localhost:9003/journal-entries/export
http://localhost:9003/customers/export
http://localhost:9003/invoices/export
http://localhost:9003/vendors/export
http://localhost:9003/bills/export
http://localhost:9003/inventory/export
http://localhost:9003/fixed-assets/export
```

### With Sorting Parameters:

Exports respect the current sort order:

```
http://localhost:9003/customers/export?sort_by=name&sort_dir=asc
http://localhost:9003/invoices/export?sort_by=invoice_date&sort_dir=desc
```

---

## 🌐 CSV Format Specifications

**Encoding:** UTF-8 with BOM (Excel compatible)  
**Delimiter:** Comma (`,`)  
**Quote Character:** Double quotes (`"`)  
**Line Ending:** CRLF (Windows compatible)  
**Decimal Separator:** Period (`.`)  
**Decimal Places:** 2 for all monetary amounts

**Number Formatting:**
- Monetary values: `1,234.56` (comma thousand separator, 2 decimal places)
- Quantities: `123.45` (2 decimal places)

**Date Formatting:**
- Dates: `YYYY-MM-DD` (e.g., `2025-11-21`)
- Date Times: `YYYY-MM-DD HH:mm:ss` (e.g., `2025-11-21 14:30:00`)

---

## 📊 Additional Reports (Also Exportable)

Beyond the main modules, these financial reports can also be exported:

### Dashboard Export
**Route:** `GET /dashboard/export`  
**Includes:** Financial summary, recent transactions, invoices, and bills

### Trial Balance
**Route:** `GET /reports/trial-balance/export`  
**Includes:** All accounts with debit/credit balances

### Profit & Loss Statement
**Route:** `GET /reports/profit-loss/export`  
**Includes:** Revenue and expense breakdown with totals

### Balance Sheet
**Route:** `GET /reports/balance-sheet/export`  
**Includes:** Assets, liabilities, and equity with period comparison

### Audit Logs
**Route:** `GET /logs/export`  
**Includes:** Complete audit trail of all system actions

---

## 🧪 Testing CSV Downloads

### Test as Admin:
```bash
# Login
curl -X POST http://localhost:9003/login \
  -d "email=admin@local.test" \
  -d "password=password"

# Download CSV
curl -X GET http://localhost:9003/customers/export \
  -H "Cookie: [session_cookie]" \
  -o customers.csv
```

### Verify in Browser:
1. Login as admin: `http://localhost:9003/login`
2. Go to customers: `http://localhost:9003/customers`
3. Click "Download CSV" button
4. Check downloaded file in Downloads folder

---

## 🔧 Technical Implementation

**Technology Stack:**
- **Backend:** Laravel 11 (PHP 8.4)
- **CSV Generation:** Native PHP `fputcsv()` function
- **Streaming:** Laravel's `response()->stream()` for memory efficiency
- **Encoding:** UTF-8 with BOM for Excel compatibility

**Key Features:**
- ✅ Memory efficient (streaming response, no memory limits)
- ✅ Handles large datasets (thousands of records)
- ✅ Tenant isolation (automatic via global scopes)
- ✅ Role-based access control
- ✅ Sortable exports
- ✅ Date-stamped filenames
- ✅ Excel compatible (UTF-8 BOM)

---

## 📝 Notes

1. **Tenant Isolation:** All exports automatically filter by the logged-in user's tenant_id
2. **Performance:** Exports use database streaming to handle large datasets efficiently
3. **File Size:** No practical limit (streaming response)
4. **Browser Compatibility:** Works in all modern browsers (Chrome, Firefox, Edge, Safari)
5. **Excel Compatibility:** Files open correctly in Microsoft Excel, Google Sheets, LibreOffice

---

## 🚀 Quick Reference

| Module | Route | Filename Pattern |
|--------|-------|------------------|
| Chart of Accounts | `/chart-of-accounts/export` | `chart-of-accounts-*.csv` |
| Journal Entries | `/journal-entries/export` | `journal-entries-*.csv` |
| Customers | `/customers/export` | `customers-*.csv` |
| Invoices | `/invoices/export` | `invoices-*.csv` |
| Vendors | `/vendors/export` | `vendors-*.csv` |
| Bills | `/bills/export` | `bills-*.csv` |
| Inventory | `/inventory/export` | `inventory-*.csv` |
| Fixed Assets | `/fixed-assets/export` | `fixed-assets-*.csv` |

---

**Status:** ✅ All CSV exports fully implemented and production-ready  
**Last Updated:** November 21, 2025  
**Version:** 1.0





