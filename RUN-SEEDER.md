# Run Sample Data Seeder

## Quick Command

```bash
cd accounting-system
php artisan db:seed
```

This will:
1. ✅ Clear all existing data
2. ✅ Create IFRS-compliant Chart of Accounts
3. ✅ Create 3 sample customers
4. ✅ Create 2 sample vendors
5. ✅ Create 5 journal entries
6. ✅ Create 3 invoices
7. ✅ Create 2 bills
8. ✅ Post all transactions to General Ledger

## What You'll Get

### Financial Data Summary:
- **Starting Capital:** SAR 850,000
- **Cash:** SAR 500,000 (after equipment/software purchases)
- **Equipment:** SAR 150,000
- **Software:** SAR 200,000
- **Revenue (Invoices):** SAR 126,500
- **Expenses:** SAR 145,000+ (salaries, rent, software, utilities)

### Test Reports:

1. **Trial Balance** (`/reports/trial-balance`)
   - All accounts with balances
   - Debits = Credits (balanced)

2. **Profit & Loss** (`/reports/profit-loss`)
   - Revenue: SAR 126,500
   - Expenses: SAR 145,000+
   - Net Income/Loss calculated

3. **Balance Sheet** (`/reports/balance-sheet`)
   - Current Assets: Cash + AR
   - Non-Current Assets: Equipment + Software
   - Liabilities: AP + Accrued + VAT
   - Equity: Share Capital + Retained Earnings

## Verify It Worked

After running the seeder, check:
- Chart of Accounts page - should show 15 accounts
- Customers page - should show 3 customers
- Vendors page - should show 2 vendors
- Journal Entries page - should show 5 entries (all posted)
- Invoices page - should show 3 invoices
- Bills page - should show 2 bills

## Troubleshooting

If you get errors:
1. Make sure migrations are run: `php artisan migrate`
2. Make sure you're using tenant_id = 1 (default)
3. Check database connection in `.env`

## Reset Data

To clear and reseed:
```bash
php artisan db:seed
```

The seeder automatically clears all data before creating new sample data.

