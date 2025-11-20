<?php

namespace App\Services;

use App\Models\Account;
use App\Models\GeneralLedgerEntry;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    public function postJournalEntry(JournalEntry $journalEntry): void
    {
        if ($journalEntry->isPosted()) {
            throw new \Exception('Journal entry already posted');
        }

        // Validate double-entry
        if (abs($journalEntry->total_debit - $journalEntry->total_credit) > 0.01) {
            throw new \Exception('Journal entry is not balanced');
        }

        DB::transaction(function () use ($journalEntry) {
            $entryNumber = $this->generateEntryNumber($journalEntry->entry_date);

            foreach ($journalEntry->lines as $line) {
                GeneralLedgerEntry::create([
                    'tenant_id' => $journalEntry->tenant_id,
                    'entry_number' => $entryNumber,
                    'entry_date' => $journalEntry->entry_date,
                    'reference_type' => JournalEntry::class,
                    'reference_id' => $journalEntry->id,
                    'description' => $line->description ?? $journalEntry->description,
                    'debit' => $line->debit,
                    'credit' => $line->credit,
                    'account_id' => $line->account_id,
                    'created_by' => $journalEntry->created_by,
                    'created_at' => now(),
                ]);

                // Update account balance
                $account = Account::find($line->account_id);
                $balanceChange = $line->debit - $line->credit;
                $account->increment('balance', $balanceChange);
            }

            $journalEntry->update([
                'status' => 'posted',
                'posted_at' => now(),
                'posted_by' => auth()->id(),
            ]);
        });
    }

    public function reverseJournalEntry(JournalEntry $journalEntry): void
    {
        if (!$journalEntry->isPosted() || $journalEntry->isReversed()) {
            throw new \Exception('Journal entry cannot be reversed');
        }

        DB::transaction(function () use ($journalEntry) {
            $entryNumber = $this->generateEntryNumber(now()->toDateString());

            foreach ($journalEntry->lines as $line) {
                GeneralLedgerEntry::create([
                    'tenant_id' => $journalEntry->tenant_id,
                    'entry_number' => $entryNumber,
                    'entry_date' => now()->toDateString(),
                    'reference_type' => JournalEntry::class,
                    'reference_id' => $journalEntry->id,
                    'description' => 'Reversal: ' . ($line->description ?? $journalEntry->description),
                    'debit' => $line->credit, // Reversed
                    'credit' => $line->debit, // Reversed
                    'account_id' => $line->account_id,
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                ]);

                // Update account balance (reversed)
                $account = Account::find($line->account_id);
                $balanceChange = $line->credit - $line->debit;
                $account->increment('balance', $balanceChange);
            }

            $journalEntry->update([
                'status' => 'reversed',
                'reversed_at' => now(),
            ]);
        });
    }

    public function postGeneralLedgerEntry(
        int $tenantId,
        string $referenceType,
        int $referenceId,
        int $accountId,
        string $description,
        float $debit,
        float $credit,
        \DateTime $entryDate
    ): GeneralLedgerEntry {
        $entryNumber = $this->generateEntryNumber($entryDate->format('Y-m-d'));

        $entry = GeneralLedgerEntry::create([
            'tenant_id' => $tenantId,
            'entry_number' => $entryNumber,
            'entry_date' => $entryDate,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description,
            'debit' => $debit,
            'credit' => $credit,
            'account_id' => $accountId,
            'created_by' => auth()->id(),
            'created_at' => now(),
        ]);

        // Update account balance
        $account = Account::find($accountId);
        $balanceChange = $debit - $credit;
        $account->increment('balance', $balanceChange);

        return $entry;
    }

    protected function generateEntryNumber(string $date): string
    {
        $datePrefix = str_replace('-', '', $date);
        $count = GeneralLedgerEntry::whereDate('entry_date', $date)->count() + 1;
        return 'GL-' . $datePrefix . '-' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }
}

