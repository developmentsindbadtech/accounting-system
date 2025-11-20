<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Bill;
use App\Models\BillLine;
use App\Models\GeneralLedgerEntry;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\FixedAsset;
use App\Models\AssetCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class FinTechCompanySampleDataSeeder extends Seeder
{
    /**
     * Seed comprehensive FinTech company data for testing dashboard and reports
     */
    public function run(): void
    {
        $tenantId = 1; // Default tenant ID
        $userId = 1; // Default user ID

        $this->command->info('🗑️  Clearing existing data...');
        $this->clearAllData($tenantId);

        $this->command->info('📊 Creating Chart of Accounts...');
        $accounts = $this->createChartOfAccounts($tenantId);

        $this->command->info('👥 Creating Customers...');
        $customers = $this->createCustomers($tenantId);

        $this->command->info('🏢 Creating Vendors...');
        $vendors = $this->createVendors($tenantId);

        $this->command->info('📝 Creating Journal Entries (12 months)...');
        $this->createJournalEntries($tenantId, $userId, $accounts);

        $this->command->info('🧾 Creating Invoices (12 months)...');
        $this->createInvoices($tenantId, $userId, $customers, $accounts);

        $this->command->info('📄 Creating Bills (12 months)...');
        $this->createBills($tenantId, $userId, $vendors, $accounts);

        $this->command->info('📦 Creating Inventory Items...');
        $this->createInventoryItems($tenantId, $accounts);

        $this->command->info('🏗️  Creating Fixed Assets...');
        $this->createFixedAssets($tenantId, $accounts);

        $this->command->info('✅ Comprehensive FinTech company data created successfully!');
        $this->command->info('💡 Dashboard should now show complete data for all metrics.');
    }

    protected function clearAllData($tenantId)
    {
        GeneralLedgerEntry::where('tenant_id', $tenantId)->delete();
        
        $journalEntryIds = JournalEntry::where('tenant_id', $tenantId)->pluck('id');
        if ($journalEntryIds->isNotEmpty()) {
            JournalEntryLine::whereIn('journal_entry_id', $journalEntryIds)->delete();
        }
        JournalEntry::where('tenant_id', $tenantId)->delete();
        
        $invoiceIds = Invoice::where('tenant_id', $tenantId)->pluck('id');
        if ($invoiceIds->isNotEmpty()) {
            InvoiceLine::whereIn('invoice_id', $invoiceIds)->delete();
        }
        Invoice::where('tenant_id', $tenantId)->delete();
        
        $billIds = Bill::where('tenant_id', $tenantId)->pluck('id');
        if ($billIds->isNotEmpty()) {
            BillLine::whereIn('bill_id', $billIds)->delete();
        }
        Bill::where('tenant_id', $tenantId)->delete();
        
        Item::where('tenant_id', $tenantId)->delete();
        ItemCategory::where('tenant_id', $tenantId)->delete();
        
        FixedAsset::where('tenant_id', $tenantId)->delete();
        AssetCategory::where('tenant_id', $tenantId)->delete();
        
        Customer::where('tenant_id', $tenantId)->delete();
        Vendor::where('tenant_id', $tenantId)->delete();
        
        Account::where('tenant_id', $tenantId)->whereNotNull('parent_id')->update(['parent_id' => null]);
        Account::where('tenant_id', $tenantId)->delete();
    }

    protected function createAccountData($tenantId, $code, $name, $type, $subType = null, $description = null, $balance = 0, $openingBalance = 0)
    {
        $data = [
            'tenant_id' => $tenantId,
            'code' => $code,
            'name' => $name,
            'type' => $type,
            'parent_id' => null,
            'level' => 1,
            'is_active' => true,
            'balance' => $balance,
        ];
        
        if ($subType && Schema::hasColumn('accounts', 'sub_type')) {
            $data['sub_type'] = $subType;
        }
        if ($description && Schema::hasColumn('accounts', 'description')) {
            $data['description'] = $description;
        }
        if (Schema::hasColumn('accounts', 'opening_balance')) {
            $data['opening_balance'] = $openingBalance;
        }
        
        return $data;
    }

    protected function createChartOfAccounts($tenantId)
    {
        $accounts = [];

        // ASSETS - Current Assets
        $accounts['cash'] = Account::create($this->createAccountData(
            $tenantId, '1000', 'Cash and Cash Equivalents', 'Asset', 
            'Current Asset', 'Bank accounts and cash on hand', 
            975000.00, 1000000.00
        ));

        $accounts['ar'] = Account::create($this->createAccountData(
            $tenantId, '1100', 'Accounts Receivable', 'Asset',
            'Current Asset', 'Amounts owed by customers'
        ));

        $accounts['prepaid'] = Account::create($this->createAccountData(
            $tenantId, '1200', 'Prepaid Expenses', 'Asset',
            'Current Asset', 'Prepaid software licenses and subscriptions'
        ));

        // ASSETS - Non-Current Assets
        $accounts['equipment'] = Account::create($this->createAccountData(
            $tenantId, '1500', 'Computer Equipment', 'Asset',
            'Non-Current Asset', 'Servers, laptops, and IT equipment',
            0, 150000.00
        ));

        $accounts['software'] = Account::create($this->createAccountData(
            $tenantId, '1600', 'Software and Intangible Assets', 'Asset',
            'Non-Current Asset', 'Software licenses and development costs',
            0, 200000.00
        ));

        // LIABILITIES - Current Liabilities
        $accounts['ap'] = Account::create($this->createAccountData(
            $tenantId, '2000', 'Accounts Payable', 'Liability',
            'Current Liability', 'Amounts owed to vendors'
        ));

        $accounts['accrued'] = Account::create($this->createAccountData(
            $tenantId, '2100', 'Accrued Expenses', 'Liability',
            'Current Liability', 'Accrued salaries and benefits'
        ));

        $accounts['vat_payable'] = Account::create($this->createAccountData(
            $tenantId, '2200', 'VAT Payable', 'Liability',
            'Current Liability', 'VAT collected from customers'
        ));

        // EQUITY
        $accounts['equity'] = Account::create($this->createAccountData(
            $tenantId, '3000', 'Share Capital', 'Equity',
            'Equity', 'Initial investment capital',
            0, 850000.00
        ));

        // REVENUE
        $accounts['revenue'] = Account::create($this->createAccountData(
            $tenantId, '4000', 'Software Subscription Revenue', 'Revenue',
            'Revenue', 'Monthly and annual subscription fees'
        ));

        $accounts['consulting'] = Account::create($this->createAccountData(
            $tenantId, '4100', 'Consulting Revenue', 'Revenue',
            'Revenue', 'Professional services and consulting'
        ));

        // EXPENSES
        $accounts['salaries'] = Account::create($this->createAccountData(
            $tenantId, '5000', 'Salaries and Wages', 'Expense',
            'Expense', 'Employee salaries'
        ));

        $accounts['rent'] = Account::create($this->createAccountData(
            $tenantId, '5100', 'Office Rent', 'Expense',
            'Expense', 'Monthly office rent'
        ));

        $accounts['software_expense'] = Account::create($this->createAccountData(
            $tenantId, '5200', 'Software Subscriptions', 'Expense',
            'Expense', 'SaaS subscriptions and licenses'
        ));

        $accounts['marketing'] = Account::create($this->createAccountData(
            $tenantId, '5300', 'Marketing and Advertising', 'Expense',
            'Expense', 'Digital marketing and advertising costs'
        ));

        $accounts['utilities'] = Account::create($this->createAccountData(
            $tenantId, '5400', 'Utilities', 'Expense',
            'Expense', 'Electricity, internet, phone'
        ));

        return $accounts;
    }

    protected function createCustomers($tenantId)
    {
        $customers = [];

        $customers[] = Customer::create([
            'tenant_id' => $tenantId,
            'code' => 'CUST001',
            'name' => 'TechCorp Solutions',
            'email' => 'ahmed@techcorp.sa',
            'phone' => '+966-11-123-4567',
            'address' => 'King Fahd Road, Al Olaya, Riyadh 12211, Saudi Arabia',
            'tax_id' => '310123456700003',
            'credit_limit' => 100000.00,
            'balance' => 0,
            'is_active' => true,
        ]);

        $customers[] = Customer::create([
            'tenant_id' => $tenantId,
            'code' => 'CUST002',
            'name' => 'Digital Ventures Ltd',
            'email' => 'fatima@digitalventures.sa',
            'phone' => '+966-11-234-5678',
            'address' => 'Prince Sultan Street, Al Malaz, Riyadh 11564, Saudi Arabia',
            'tax_id' => '310234567800003',
            'credit_limit' => 75000.00,
            'balance' => 0,
            'is_active' => true,
        ]);

        $customers[] = Customer::create([
            'tenant_id' => $tenantId,
            'code' => 'CUST003',
            'name' => 'Innovation Hub',
            'email' => 'khalid@innovationhub.sa',
            'phone' => '+966-11-345-6789',
            'address' => 'King Abdullah Financial District, Riyadh 13519, Saudi Arabia',
            'tax_id' => '310345678900003',
            'credit_limit' => 50000.00,
            'balance' => 0,
            'is_active' => true,
        ]);

        $customers[] = Customer::create([
            'tenant_id' => $tenantId,
            'code' => 'CUST004',
            'name' => 'Saudi FinTech Group',
            'email' => 'sara@saudifintech.sa',
            'phone' => '+966-11-456-7890',
            'address' => 'Al Tahlia Street, Riyadh 12211, Saudi Arabia',
            'tax_id' => '310456789000003',
            'credit_limit' => 150000.00,
            'balance' => 0,
            'is_active' => true,
        ]);

        $customers[] = Customer::create([
            'tenant_id' => $tenantId,
            'code' => 'CUST005',
            'name' => 'Cloud Solutions Arabia',
            'email' => 'omar@cloudsolutions.sa',
            'phone' => '+966-11-567-8901',
            'address' => 'King Abdulaziz Road, Jeddah 21432, Saudi Arabia',
            'tax_id' => '310567890100003',
            'credit_limit' => 80000.00,
            'balance' => 0,
            'is_active' => true,
        ]);

        return $customers;
    }

    protected function createVendors($tenantId)
    {
        $vendors = [];

        $vendors[] = Vendor::create([
            'tenant_id' => $tenantId,
            'code' => 'VEND001',
            'name' => 'Cloud Services Provider',
            'email' => 'mohammed@cloudservices.sa',
            'phone' => '+966-11-456-7890',
            'address' => 'Al Khobar Corniche, Al Khobar 34428, Saudi Arabia',
            'tax_id' => '310456789000003',
            'payment_terms' => 'Net 30',
            'balance' => 0,
            'is_active' => true,
        ]);

        $vendors[] = Vendor::create([
            'tenant_id' => $tenantId,
            'code' => 'VEND002',
            'name' => 'Office Supplies Co.',
            'email' => 'sara@officesupplies.sa',
            'phone' => '+966-11-567-8901',
            'address' => 'King Abdulaziz Road, Jeddah 21432, Saudi Arabia',
            'tax_id' => '310567890100003',
            'payment_terms' => 'Net 15',
            'balance' => 0,
            'is_active' => true,
        ]);

        $vendors[] = Vendor::create([
            'tenant_id' => $tenantId,
            'code' => 'VEND003',
            'name' => 'Marketing Agency Pro',
            'email' => 'layla@marketingpro.sa',
            'phone' => '+966-11-678-9012',
            'address' => 'Olaya Street, Riyadh 12211, Saudi Arabia',
            'tax_id' => '310678901200003',
            'payment_terms' => 'Net 30',
            'balance' => 0,
            'is_active' => true,
        ]);

        return $vendors;
    }

    protected function createJournalEntries($tenantId, $userId, $accounts)
    {
        $now = Carbon::now();

        // Initial Capital Investment (3 months ago)
        $je1 = JournalEntry::create([
            'tenant_id' => $tenantId,
            'entry_number' => 'JE-' . $now->copy()->subMonths(3)->format('Ymd') . '-001',
            'entry_date' => $now->copy()->subMonths(3)->startOfMonth(),
            'description' => 'Initial capital investment',
            'reference_number' => 'INV-001',
            'total_debit' => 850000.00,
            'total_credit' => 850000.00,
            'status' => 'posted',
            'posted_at' => $now->copy()->subMonths(3)->startOfMonth(),
            'posted_by' => $userId,
            'created_by' => $userId,
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $je1->id,
            'account_id' => $accounts['cash']->id,
            'description' => 'Cash received from investors',
            'debit' => 850000.00,
            'credit' => 0,
            'created_at' => $now->copy()->subMonths(3)->startOfMonth(),
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $je1->id,
            'account_id' => $accounts['equity']->id,
            'description' => 'Share capital issued',
            'debit' => 0,
            'credit' => 850000.00,
            'created_at' => $now->copy()->subMonths(3)->startOfMonth(),
        ]);

        // Equipment Purchase (2 months ago)
        $je2 = JournalEntry::create([
            'tenant_id' => $tenantId,
            'entry_number' => 'JE-' . $now->copy()->subMonths(2)->format('Ymd') . '-001',
            'entry_date' => $now->copy()->subMonths(2)->startOfMonth(),
            'description' => 'Purchase of computer equipment',
            'reference_number' => 'PO-001',
            'total_debit' => 150000.00,
            'total_credit' => 150000.00,
            'status' => 'posted',
            'posted_at' => $now->copy()->subMonths(2)->startOfMonth(),
            'posted_by' => $userId,
            'created_by' => $userId,
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $je2->id,
            'account_id' => $accounts['equipment']->id,
            'description' => 'Computer equipment purchase',
            'debit' => 150000.00,
            'credit' => 0,
            'created_at' => $now->copy()->subMonths(2)->startOfMonth(),
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $je2->id,
            'account_id' => $accounts['cash']->id,
            'description' => 'Payment for equipment',
            'debit' => 0,
            'credit' => 150000.00,
            'created_at' => $now->copy()->subMonths(2)->startOfMonth(),
        ]);

        // Software Purchase (2 months ago)
        $je3 = JournalEntry::create([
            'tenant_id' => $tenantId,
            'entry_number' => 'JE-' . $now->copy()->subMonths(2)->format('Ymd') . '-002',
            'entry_date' => $now->copy()->subMonths(2)->startOfMonth()->addDays(5),
            'description' => 'Software licenses purchase',
            'reference_number' => 'PO-002',
            'total_debit' => 200000.00,
            'total_credit' => 200000.00,
            'status' => 'posted',
            'posted_at' => $now->copy()->subMonths(2)->startOfMonth()->addDays(5),
            'posted_by' => $userId,
            'created_by' => $userId,
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $je3->id,
            'account_id' => $accounts['software']->id,
            'description' => 'Software licenses',
            'debit' => 200000.00,
            'credit' => 0,
            'created_at' => $now->copy()->subMonths(2)->startOfMonth()->addDays(5),
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $je3->id,
            'account_id' => $accounts['cash']->id,
            'description' => 'Payment for software',
            'debit' => 0,
            'credit' => 200000.00,
            'created_at' => $now->copy()->subMonths(2)->startOfMonth()->addDays(5),
        ]);

        // Monthly recurring entries for last 12 months
        for ($i = 11; $i >= 0; $i--) {
            $monthDate = $now->copy()->subMonths($i);
            
            // Monthly Salaries
            $jeSal = JournalEntry::create([
                'tenant_id' => $tenantId,
                'entry_number' => 'JE-' . $monthDate->format('Ymd') . '-SAL',
                'entry_date' => $monthDate->endOfMonth(),
                'description' => 'Monthly salaries - ' . $monthDate->format('F Y'),
                'reference_number' => 'SAL-' . $monthDate->format('Ym'),
                'total_debit' => 120000.00,
                'total_credit' => 120000.00,
                'status' => 'posted',
                'posted_at' => $monthDate->endOfMonth(),
                'posted_by' => $userId,
                'created_by' => $userId,
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $jeSal->id,
                'account_id' => $accounts['salaries']->id,
                'description' => 'Employee salaries',
                'debit' => 120000.00,
                'credit' => 0,
                'created_at' => $monthDate->endOfMonth(),
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $jeSal->id,
                'account_id' => $accounts['accrued']->id,
                'description' => 'Accrued salaries payable',
                'debit' => 0,
                'credit' => 120000.00,
                'created_at' => $monthDate->endOfMonth(),
            ]);

            // Monthly Rent
            $jeRent = JournalEntry::create([
                'tenant_id' => $tenantId,
                'entry_number' => 'JE-' . $monthDate->format('Ymd') . '-RENT',
                'entry_date' => $monthDate->startOfMonth(),
                'description' => 'Office rent - ' . $monthDate->format('F Y'),
                'reference_number' => 'RENT-' . $monthDate->format('Ym'),
                'total_debit' => 25000.00,
                'total_credit' => 25000.00,
                'status' => 'posted',
                'posted_at' => $monthDate->startOfMonth(),
                'posted_by' => $userId,
                'created_by' => $userId,
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $jeRent->id,
                'account_id' => $accounts['rent']->id,
                'description' => 'Office rent expense',
                'debit' => 25000.00,
                'credit' => 0,
                'created_at' => $monthDate->startOfMonth(),
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $jeRent->id,
                'account_id' => $accounts['cash']->id,
                'description' => 'Rent payment',
                'debit' => 0,
                'credit' => 25000.00,
                'created_at' => $monthDate->startOfMonth(),
            ]);

            // Post to General Ledger
            foreach ([$jeSal, $jeRent] as $je) {
                foreach ($je->lines as $line) {
                    GeneralLedgerEntry::create([
                        'tenant_id' => $tenantId,
                        'entry_number' => $je->entry_number,
                        'entry_date' => $je->entry_date,
                        'reference_type' => JournalEntry::class,
                        'reference_id' => $je->id,
                        'description' => $line->description ?? $je->description,
                        'debit' => $line->debit,
                        'credit' => $line->credit,
                        'account_id' => $line->account_id,
                        'created_by' => $userId,
                        'created_at' => $je->entry_date,
                    ]);

                    $account = Account::find($line->account_id);
                    $balanceChange = $line->debit - $line->credit;
                    $account->increment('balance', $balanceChange);
                }
            }
        }

        // Post initial entries to General Ledger
        foreach ([$je1, $je2, $je3] as $je) {
            foreach ($je->lines as $line) {
                GeneralLedgerEntry::create([
                    'tenant_id' => $tenantId,
                    'entry_number' => $je->entry_number,
                    'entry_date' => $je->entry_date,
                    'reference_type' => JournalEntry::class,
                    'reference_id' => $je->id,
                    'description' => $line->description ?? $je->description,
                    'debit' => $line->debit,
                    'credit' => $line->credit,
                    'account_id' => $line->account_id,
                    'created_by' => $userId,
                    'created_at' => $je->entry_date,
                ]);

                $account = Account::find($line->account_id);
                $balanceChange = $line->debit - $line->credit;
                $account->increment('balance', $balanceChange);
            }
        }
    }

    protected function createInvoices($tenantId, $userId, $customers, $accounts)
    {
        $now = Carbon::now();
        $revenueAccount = $accounts['revenue'];
        $invoiceCounter = 1;

        // Create invoices for each of the last 12 months
        for ($i = 11; $i >= 0; $i--) {
            $monthDate = $now->copy()->subMonths($i);
            $monthStart = $monthDate->copy()->startOfMonth();
            
            // 2-4 invoices per month
            $invoicesPerMonth = rand(2, 4);
            
            for ($j = 0; $j < $invoicesPerMonth; $j++) {
                $customer = $customers[array_rand($customers)];
                $invoiceDate = $monthStart->copy()->addDays(rand(1, 25));
                $dueDate = $invoiceDate->copy()->addDays(30);
                
                // Determine status based on date
                $daysSinceDue = $now->diffInDays($dueDate, false);
                if ($daysSinceDue < -60) {
                    $status = 'overdue';
                } elseif ($daysSinceDue < 0) {
                    $status = rand(0, 1) ? 'sent' : 'overdue';
                } elseif ($i <= 1 && rand(0, 2) === 0) {
                    $status = 'paid';
                } else {
                    $status = 'sent';
                }
                
                $subtotal = rand(15000, 60000);
                $vatAmount = $subtotal * 0.15;
                $total = $subtotal + $vatAmount;
                
                $invoice = Invoice::create([
                    'tenant_id' => $tenantId,
                    'invoice_number' => 'INV-' . $invoiceDate->format('Ymd') . '-' . str_pad($invoiceCounter++, 3, '0', STR_PAD_LEFT),
                    'customer_id' => $customer->id,
                    'invoice_date' => $invoiceDate,
                    'due_date' => $dueDate,
                    'subtotal' => $subtotal,
                    'vat_amount' => $vatAmount,
                    'total' => $total,
                    'status' => $status,
                    'payment_terms' => 'Net 30',
                    'created_by' => $userId,
                ]);

                if ($status === 'paid') {
                    $invoice->update([
                        'amount_paid' => $total,
                        'balance_due' => 0,
                    ]);
                } else {
                    $invoice->update([
                        'amount_paid' => 0,
                        'balance_due' => $total,
                    ]);
                }

                InvoiceLine::create([
                    'invoice_id' => $invoice->id,
                    'description' => 'Software Subscription - ' . ['Premium Plan', 'Business Plan', 'Enterprise Plan'][rand(0, 2)],
                    'quantity' => 1,
                    'unit_price' => $subtotal,
                    'vat_percent' => 15,
                    'vat_amount' => $vatAmount,
                    'line_total' => $total,
                    'account_id' => rand(0, 1) ? $revenueAccount->id : $accounts['consulting']->id,
                ]);

                // Post to General Ledger
                foreach ($invoice->lines as $line) {
                    // Debit AR, Credit Revenue
                    GeneralLedgerEntry::create([
                        'tenant_id' => $tenantId,
                        'entry_number' => $invoice->invoice_number,
                        'entry_date' => $invoice->invoice_date,
                        'reference_type' => Invoice::class,
                        'reference_id' => $invoice->id,
                        'description' => "Invoice {$invoice->invoice_number} - {$line->description}",
                        'debit' => $line->line_total,
                        'credit' => 0,
                        'account_id' => $accounts['ar']->id,
                        'created_by' => $userId,
                        'created_at' => $invoice->invoice_date,
                    ]);

                    GeneralLedgerEntry::create([
                        'tenant_id' => $tenantId,
                        'entry_number' => $invoice->invoice_number,
                        'entry_date' => $invoice->invoice_date,
                        'reference_type' => Invoice::class,
                        'reference_id' => $invoice->id,
                        'description' => "Invoice {$invoice->invoice_number} - {$line->description}",
                        'debit' => 0,
                        'credit' => $line->line_total - $line->vat_amount,
                        'account_id' => $line->account_id,
                        'created_by' => $userId,
                        'created_at' => $invoice->invoice_date,
                    ]);

                    // VAT Entry
                    if ($line->vat_amount > 0) {
                        GeneralLedgerEntry::create([
                            'tenant_id' => $tenantId,
                            'entry_number' => $invoice->invoice_number,
                            'entry_date' => $invoice->invoice_date,
                            'reference_type' => Invoice::class,
                            'reference_id' => $invoice->id,
                            'description' => "VAT Output - Invoice {$invoice->invoice_number}",
                            'debit' => 0,
                            'credit' => $line->vat_amount,
                            'account_id' => $accounts['vat_payable']->id,
                            'created_by' => $userId,
                            'created_at' => $invoice->invoice_date,
                        ]);
                    }

                    // Update account balances
                    $accounts['ar']->increment('balance', $line->line_total);
                    Account::find($line->account_id)->increment('balance', -($line->line_total - $line->vat_amount));
                    if ($line->vat_amount > 0) {
                        $accounts['vat_payable']->increment('balance', -$line->vat_amount);
                    }
                }

                // Update customer balance
                if ($status !== 'paid') {
                    $customer->increment('balance', $invoice->total);
                }
            }
        }
    }

    protected function createBills($tenantId, $userId, $vendors, $accounts)
    {
        $now = Carbon::now();
        $billCounter = 1;

        // Create bills for each of the last 12 months
        for ($i = 11; $i >= 0; $i--) {
            $monthDate = $now->copy()->subMonths($i);
            $monthStart = $monthDate->copy()->startOfMonth();
            
            // 1-3 bills per month
            $billsPerMonth = rand(1, 3);
            
            for ($j = 0; $j < $billsPerMonth; $j++) {
                $vendor = $vendors[array_rand($vendors)];
                $billDate = $monthStart->copy()->addDays(rand(1, 25));
                $dueDate = $billDate->copy()->addDays(rand(15, 30));
                
                // Determine status
                $daysSinceDue = $now->diffInDays($dueDate, false);
                if ($daysSinceDue < -30) {
                    $status = 'overdue';
                } elseif ($daysSinceDue < 0) {
                    $status = rand(0, 1) ? 'received' : 'overdue';
                } elseif ($i <= 1 && rand(0, 2) === 0) {
                    $status = 'paid';
                } else {
                    $status = 'received';
                }
                
                $subtotal = rand(5000, 25000);
                $vatAmount = $subtotal * 0.15;
                $total = $subtotal + $vatAmount;
                
                $expenseAccounts = [$accounts['software_expense'], $accounts['marketing'], $accounts['utilities']];
                $expenseAccount = $expenseAccounts[array_rand($expenseAccounts)];
                
                $bill = Bill::create([
                    'tenant_id' => $tenantId,
                    'bill_number' => 'BILL-' . $billDate->format('Ymd') . '-' . str_pad($billCounter++, 3, '0', STR_PAD_LEFT),
                    'vendor_id' => $vendor->id,
                    'bill_date' => $billDate,
                    'due_date' => $dueDate,
                    'subtotal' => $subtotal,
                    'vat_amount' => $vatAmount,
                    'total' => $total,
                    'status' => $status,
                    'created_by' => $userId,
                ]);

                if ($status === 'paid') {
                    $bill->update([
                        'amount_paid' => $total,
                        'balance_due' => 0,
                    ]);
                } else {
                    $bill->update([
                        'amount_paid' => 0,
                        'balance_due' => $total,
                    ]);
                }

                BillLine::create([
                    'bill_id' => $bill->id,
                    'description' => ['Cloud Infrastructure Services', 'Marketing Campaign', 'Office Supplies', 'Utilities'][rand(0, 3)],
                    'quantity' => 1,
                    'unit_price' => $subtotal,
                    'vat_percent' => 15,
                    'vat_amount' => $vatAmount,
                    'line_total' => $total,
                    'account_id' => $expenseAccount->id,
                ]);

                // Post to General Ledger
                foreach ($bill->lines as $line) {
                    // Debit Expense, Credit AP
                    GeneralLedgerEntry::create([
                        'tenant_id' => $tenantId,
                        'entry_number' => $bill->bill_number,
                        'entry_date' => $bill->bill_date,
                        'reference_type' => Bill::class,
                        'reference_id' => $bill->id,
                        'description' => "Bill {$bill->bill_number} - {$line->description}",
                        'debit' => $line->line_total - $line->vat_amount,
                        'credit' => 0,
                        'account_id' => $line->account_id,
                        'created_by' => $userId,
                        'created_at' => $bill->bill_date,
                    ]);

                    GeneralLedgerEntry::create([
                        'tenant_id' => $tenantId,
                        'entry_number' => $bill->bill_number,
                        'entry_date' => $bill->bill_date,
                        'reference_type' => Bill::class,
                        'reference_id' => $bill->id,
                        'description' => "Bill {$bill->bill_number} - {$line->description}",
                        'debit' => 0,
                        'credit' => $line->line_total,
                        'account_id' => $accounts['ap']->id,
                        'created_by' => $userId,
                        'created_at' => $bill->bill_date,
                    ]);

                    // VAT Entry (Input VAT)
                    if ($line->vat_amount > 0) {
                        GeneralLedgerEntry::create([
                            'tenant_id' => $tenantId,
                            'entry_number' => $bill->bill_number,
                            'entry_date' => $bill->bill_date,
                            'reference_type' => Bill::class,
                            'reference_id' => $bill->id,
                            'description' => "VAT Input - Bill {$bill->bill_number}",
                            'debit' => $line->vat_amount,
                            'credit' => 0,
                            'account_id' => $accounts['vat_payable']->id,
                            'created_by' => $userId,
                            'created_at' => $bill->bill_date,
                        ]);
                    }

                    // Update account balances
                    Account::find($line->account_id)->increment('balance', $line->line_total - $line->vat_amount);
                    $accounts['ap']->increment('balance', -$line->line_total);
                    if ($line->vat_amount > 0) {
                        $accounts['vat_payable']->increment('balance', $line->vat_amount);
                    }
                }

                // Update vendor balance
                if ($status !== 'paid') {
                    $vendor->increment('balance', $bill->total);
                }
            }
        }
    }

    protected function createInventoryItems($tenantId, $accounts)
    {
        $softwareCategory = ItemCategory::create([
            'tenant_id' => $tenantId,
            'name' => 'Software Products',
        ]);

        $hardwareCategory = ItemCategory::create([
            'tenant_id' => $tenantId,
            'name' => 'Hardware',
        ]);

        $serviceCategory = ItemCategory::create([
            'tenant_id' => $tenantId,
            'name' => 'Services',
        ]);

        $revenueAccount = $accounts['revenue'];
        $expenseAccount = $accounts['software_expense'];
        $inventoryAccount = $accounts['prepaid'];

        Item::create([
            'tenant_id' => $tenantId,
            'sku' => 'SW-PREMIUM-001',
            'name' => 'Premium Software License',
            'description' => 'Annual premium software subscription license',
            'type' => 'product',
            'category_id' => $softwareCategory->id,
            'purchase_account_id' => $expenseAccount->id,
            'sales_account_id' => $revenueAccount->id,
            'inventory_account_id' => $inventoryAccount->id,
            'unit_of_measure' => 'License',
            'track_quantity' => true,
            'quantity_on_hand' => 50.00,
            'quantity_reserved' => 0,
            'reorder_point' => 10.00,
            'standard_cost' => 5000.00,
            'is_active' => true,
        ]);

        Item::create([
            'tenant_id' => $tenantId,
            'sku' => 'SW-BASIC-001',
            'name' => 'Basic Software License',
            'description' => 'Annual basic software subscription license',
            'type' => 'product',
            'category_id' => $softwareCategory->id,
            'purchase_account_id' => $expenseAccount->id,
            'sales_account_id' => $revenueAccount->id,
            'inventory_account_id' => $inventoryAccount->id,
            'unit_of_measure' => 'License',
            'track_quantity' => true,
            'quantity_on_hand' => 100.00,
            'quantity_reserved' => 0,
            'reorder_point' => 20.00,
            'standard_cost' => 2500.00,
            'is_active' => true,
        ]);

        Item::create([
            'tenant_id' => $tenantId,
            'sku' => 'SRV-CONSULT-001',
            'name' => 'Consulting Services',
            'description' => 'Professional consulting services per hour',
            'type' => 'service',
            'category_id' => $serviceCategory->id,
            'purchase_account_id' => $expenseAccount->id,
            'sales_account_id' => $accounts['consulting']->id,
            'inventory_account_id' => null,
            'unit_of_measure' => 'Hour',
            'track_quantity' => false,
            'quantity_on_hand' => 0,
            'quantity_reserved' => 0,
            'reorder_point' => null,
            'standard_cost' => 500.00,
            'is_active' => true,
        ]);
    }

    protected function createFixedAssets($tenantId, $accounts)
    {
        $itCategory = AssetCategory::create([
            'tenant_id' => $tenantId,
            'name' => 'IT Equipment',
            'depreciation_rate' => 20.00,
        ]);

        $furnitureCategory = AssetCategory::create([
            'tenant_id' => $tenantId,
            'name' => 'Office Furniture',
            'depreciation_rate' => 10.00,
        ]);

        $assetAccount = $accounts['equipment'];
        $depreciationExpenseAccount = $accounts['software_expense'];
        $accumulatedDepreciationAccount = $accounts['equipment'];

        FixedAsset::create([
            'tenant_id' => $tenantId,
            'asset_number' => 'FA-IT-001',
            'name' => 'Production Server Rack',
            'category_id' => $itCategory->id,
            'purchase_date' => Carbon::now()->subMonths(6)->startOfMonth(),
            'purchase_cost' => 75000.00,
            'useful_life_years' => 5,
            'depreciation_method' => 'straight-line',
            'salvage_value' => 5000.00,
            'accumulated_depreciation' => 7000.00,
            'net_book_value' => 68000.00,
            'asset_account_id' => $assetAccount->id,
            'depreciation_expense_account_id' => $depreciationExpenseAccount->id,
            'accumulated_depreciation_account_id' => $accumulatedDepreciationAccount->id,
            'status' => 'active',
        ]);

        FixedAsset::create([
            'tenant_id' => $tenantId,
            'asset_number' => 'FA-IT-002',
            'name' => 'Development Laptops (10 units)',
            'category_id' => $itCategory->id,
            'purchase_date' => Carbon::now()->subMonths(3)->startOfMonth(),
            'purchase_cost' => 45000.00,
            'useful_life_years' => 3,
            'depreciation_method' => 'straight-line',
            'salvage_value' => 5000.00,
            'accumulated_depreciation' => 3333.33,
            'net_book_value' => 41666.67,
            'asset_account_id' => $assetAccount->id,
            'depreciation_expense_account_id' => $depreciationExpenseAccount->id,
            'accumulated_depreciation_account_id' => $accumulatedDepreciationAccount->id,
            'status' => 'active',
        ]);

        FixedAsset::create([
            'tenant_id' => $tenantId,
            'asset_number' => 'FA-FURN-001',
            'name' => 'Office Furniture Set',
            'category_id' => $furnitureCategory->id,
            'purchase_date' => Carbon::now()->subMonths(2)->startOfMonth(),
            'purchase_cost' => 30000.00,
            'useful_life_years' => 10,
            'depreciation_method' => 'straight-line',
            'salvage_value' => 3000.00,
            'accumulated_depreciation' => 450.00,
            'net_book_value' => 29550.00,
            'asset_account_id' => $assetAccount->id,
            'depreciation_expense_account_id' => $depreciationExpenseAccount->id,
            'accumulated_depreciation_account_id' => $accumulatedDepreciationAccount->id,
            'status' => 'active',
        ]);
    }
}
