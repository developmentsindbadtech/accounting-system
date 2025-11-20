# Seed Sample FinTech Company Data

This guide explains how to populate the database with realistic FinTech company sample data for testing financial reports.

## Quick Start

```bash
cd accounting-system
php artisan db:seed
```

Or run the seeder directly:

```bash
php artisan db:seed --class=FinTechCompanySampleDataSeeder
```

## What Gets Created

### Chart of Accounts (IFRS Compliant)

**Current Assets:**
- Cash and Cash Equivalents (1000) - Opening: SAR 500,000
- Accounts Receivable (1100)
- Prepaid Expenses (1200)

**Non-Current Assets:**
- Computer Equipment (1500) - Opening: SAR 150,000
- Software and Intangible Assets (1600) - Opening: SAR 200,000

**Current Liabilities:**
- Accounts Payable (2000)
- Accrued Expenses (2100)
- VAT Payable (2200)

**Equity:**
- Share Capital (3000) - Opening: SAR 850,000

**Revenue:**
- Software Subscription Revenue (4000)
- Consulting Revenue (4100)

**Expenses:**
- Salaries and Wages (5000)
- Office Rent (5100)
- Software Subscriptions (5200)
- Marketing and Advertising (5300)
- Utilities (5400)

### Sample Data Includes

1. **3 Customers:**
   - TechCorp Solutions
   - Digital Ventures Ltd
   - Innovation Hub

2. **2 Vendors:**
   - Cloud Services Provider
   - Office Supplies Co.

3. **5 Journal Entries:**
   - Initial capital investment
   - Equipment purchase
   - Software purchase
   - Monthly salaries
   - Office rent

4. **3 Invoices:**
   - 2 sent invoices (last month)
   - 1 paid invoice (this month)
   - Total Revenue: SAR 126,500

5. **2 Bills:**
   - Cloud services bill
   - Office supplies bill
   - Total Expenses: SAR 23,000

## Testing Financial Reports

After seeding, you can test:

### Trial Balance
- Navigate to: `/reports/trial-balance`
- Should show all accounts with balanced debits and credits

### Profit & Loss
- Navigate to: `/reports/profit-loss`
- Should show:
  - Revenue: SAR 126,500
  - Expenses: SAR 145,000 (salaries, rent, software, utilities)
  - Net Income/Loss: Calculated automatically

### Balance Sheet
- Navigate to: `/reports/balance-sheet`
- Should show:
  - Current Assets: Cash + AR
  - Non-Current Assets: Equipment + Software
  - Current Liabilities: AP + Accrued + VAT Payable
  - Equity: Share Capital + Retained Earnings

## Clearing Data

The seeder automatically clears all existing data before creating new sample data. This ensures a clean state for testing.

## Notes

- All amounts are in SAR (Saudi Riyal)
- Dates are set relative to current date (last month, this month)
- All journal entries are posted
- Invoices and bills are properly linked to customers/vendors
- General Ledger entries are created for all transactions
- Account balances are automatically calculated

## Expected Results

After seeding, you should see:
- **Total Assets:** ~SAR 1,000,000+
- **Total Revenue:** SAR 126,500
- **Total Expenses:** SAR 145,000+
- **Net Income:** Negative (startup phase)
- **Trial Balance:** Balanced (debits = credits)

