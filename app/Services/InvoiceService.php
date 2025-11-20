<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\VatTransaction;
use App\Models\VatCode;
use App\Models\Account;
use App\Services\AccountingService;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    protected $accountingService;

    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    public function createInvoice(array $data, array $lines): Invoice
    {
        return DB::transaction(function () use ($data, $lines) {
            $subtotal = collect($lines)->sum('line_total');
            $vatAmount = collect($lines)->sum('vat_amount');
            $total = $subtotal + $vatAmount;

            $invoice = Invoice::create([
                'tenant_id' => $data['tenant_id'],
                'invoice_number' => $this->generateInvoiceNumber(),
                'customer_id' => $data['customer_id'],
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'] ?? null,
                'subtotal' => $subtotal,
                'vat_amount' => $vatAmount,
                'total' => $total,
                'status' => 'draft',
                'payment_terms' => $data['payment_terms'] ?? null,
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($lines as $line) {
                InvoiceLine::create([
                    'invoice_id' => $invoice->id,
                    'item_id' => $line['item_id'] ?? null,
                    'description' => $line['description'],
                    'quantity' => $line['quantity'] ?? 1,
                    'unit_price' => $line['unit_price'],
                    'discount_percent' => $line['discount_percent'] ?? 0,
                    'line_total' => $line['line_total'],
                    'vat_percent' => $line['vat_percent'] ?? 15,
                    'vat_amount' => $line['vat_amount'],
                    'account_id' => $line['account_id'],
                ]);
            }

            return $invoice;
        });
    }

    public function postInvoice(Invoice $invoice): void
    {
        if ($invoice->status !== 'draft') {
            throw new \Exception('Invoice can only be posted from draft status');
        }

        DB::transaction(function () use ($invoice) {
            $invoice->load('lines');

            // Get AR account (assuming it's configured)
            $arAccount = Account::where('tenant_id', $invoice->tenant_id)
                ->where('code', '1200') // Accounts Receivable
                ->first();

            if (!$arAccount) {
                throw new \Exception('Accounts Receivable account not found');
            }

            // Post invoice lines to GL
            foreach ($invoice->lines as $line) {
                // Debit AR, Credit Revenue
                $this->accountingService->postGeneralLedgerEntry(
                    $invoice->tenant_id,
                    Invoice::class,
                    $invoice->id,
                    $arAccount->id,
                    "Invoice {$invoice->invoice_number} - {$line->description}",
                    $line->line_total + $line->vat_amount,
                    0,
                    $invoice->invoice_date
                );

                $this->accountingService->postGeneralLedgerEntry(
                    $invoice->tenant_id,
                    Invoice::class,
                    $invoice->id,
                    $line->account_id,
                    "Invoice {$invoice->invoice_number} - {$line->description}",
                    0,
                    $line->line_total,
                    $invoice->invoice_date
                );

                // Post VAT
                if ($line->vat_amount > 0) {
                    $vatAccount = Account::where('tenant_id', $invoice->tenant_id)
                        ->where('code', '2300') // VAT Output
                        ->first();

                    if ($vatAccount) {
                        $this->accountingService->postGeneralLedgerEntry(
                            $invoice->tenant_id,
                            Invoice::class,
                            $invoice->id,
                            $vatAccount->id,
                            "VAT Output - Invoice {$invoice->invoice_number}",
                            0,
                            $line->vat_amount,
                            $invoice->invoice_date
                        );
                    }

                    // Record VAT transaction
                    $vatCode = VatCode::where('tenant_id', $invoice->tenant_id)
                        ->where('rate', $line->vat_percent)
                        ->first();

                    if ($vatCode) {
                        VatTransaction::create([
                            'tenant_id' => $invoice->tenant_id,
                            'vat_code_id' => $vatCode->id,
                            'transaction_type' => 'invoice',
                            'reference_type' => Invoice::class,
                            'reference_id' => $invoice->id,
                            'vat_amount' => $line->vat_amount,
                            'net_amount' => $line->line_total,
                            'gross_amount' => $line->line_total + $line->vat_amount,
                            'transaction_date' => $invoice->invoice_date,
                            'created_at' => now(),
                        ]);
                    }
                }
            }

            $invoice->update([
                'status' => 'sent',
            ]);

            // Update customer balance
            $invoice->customer->increment('balance', $invoice->total);
        });
    }

    protected function generateInvoiceNumber(): string
    {
        $datePrefix = now()->format('Ymd');
        $count = Invoice::where('tenant_id', auth()->user()->tenant_id)
            ->whereDate('invoice_date', now())
            ->count() + 1;
        return 'INV-' . $datePrefix . '-' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }
}

