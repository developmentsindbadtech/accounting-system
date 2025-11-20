<?php

namespace App\Http\Controllers;

use App\Models\JournalEntry;
use App\Models\Account;
use App\Services\AccountingService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class JournalEntryController extends Controller
{
    protected $accountingService;

    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    public function index(Request $request)
    {
        $sortBy = $request->get('sort_by', 'updated_at');
        $sortDir = $request->get('sort_dir', 'desc');
        
        $query = JournalEntry::with('createdBy');
        
        $allowedSorts = ['entry_number', 'entry_date', 'description', 'reference_number', 'total_debit', 'total_credit', 'status', 'updated_at', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'updated_at';
        }
        
        $sortDir = in_array(strtolower($sortDir), ['asc', 'desc']) ? strtolower($sortDir) : 'desc';
        
        $journalEntries = $query->orderBy($sortBy, $sortDir)->paginate(30)->withQueryString();

        return view('journal-entries.index', compact('journalEntries', 'sortBy', 'sortDir'));
    }
    
    public function export(Request $request)
    {
        $sortBy = $request->get('sort_by', 'updated_at');
        $sortDir = $request->get('sort_dir', 'desc');
        
        $query = JournalEntry::with('createdBy');
        
        $allowedSorts = ['entry_number', 'entry_date', 'description', 'reference_number', 'total_debit', 'total_credit', 'status', 'updated_at', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'updated_at';
        }
        
        $sortDir = in_array(strtolower($sortDir), ['asc', 'desc']) ? strtolower($sortDir) : 'desc';
        
        $journalEntries = $query->orderBy($sortBy, $sortDir)->get();
        
        $filename = 'journal-entries-' . date('Y-m-d-His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($journalEntries) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['Entry Number', 'Entry Date', 'Description', 'Reference Number', 'Total Debit', 'Total Credit', 'Status', 'Created By', 'Updated At', 'Created At']);
            
            foreach ($journalEntries as $entry) {
                fputcsv($file, [
                    $entry->entry_number,
                    $entry->entry_date->format('Y-m-d'),
                    $entry->description,
                    $entry->reference_number ?? '',
                    number_format($entry->total_debit, 2),
                    number_format($entry->total_credit, 2),
                    ucfirst($entry->status),
                    $entry->createdBy->name ?? 'N/A',
                    $entry->updated_at->format('Y-m-d H:i:s'),
                    $entry->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function create()
    {
        $accounts = Account::where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('journal-entries.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'entry_date' => 'required|date',
            'description' => 'required|string',
            'reference_number' => 'nullable|string',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    $account = \App\Models\Account::where('id', $value)
                        ->where('tenant_id', auth()->user()->tenant_id)
                        ->first();
                    if (!$account) {
                        $fail('The selected account is invalid or does not belong to your tenant.');
                    }
                },
            ],
            'lines.*.description' => 'nullable|string',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
        ]);

        // Validate that each line has an account and either debit or credit
        foreach ($request->lines as $index => $line) {
            if (empty($line['account_id'])) {
                return back()->withErrors(['lines' => "Line " . ($index + 1) . " must have an account selected."])
                    ->withInput();
            }
        }

        // Validate that each line has either debit or credit
        foreach ($request->lines as $index => $line) {
            $debit = (float) ($line['debit'] ?? 0);
            $credit = (float) ($line['credit'] ?? 0);
            
            if ($debit == 0 && $credit == 0) {
                return back()->withErrors(['lines' => "Line " . ($index + 1) . " must have either a debit or credit amount."])
                    ->withInput();
            }
            
            if ($debit > 0 && $credit > 0) {
                return back()->withErrors(['lines' => "Line " . ($index + 1) . " cannot have both debit and credit amounts."])
                    ->withInput();
            }
        }

        // Validate double-entry
        $totalDebit = collect($request->lines)->sum(function ($line) {
            return (float) ($line['debit'] ?? 0);
        });
        $totalCredit = collect($request->lines)->sum(function ($line) {
            return (float) ($line['credit'] ?? 0);
        });

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->withErrors(['lines' => 'Total debits must equal total credits. Current difference: SAR ' . number_format(abs($totalDebit - $totalCredit), 2)])
                ->withInput();
        }

        try {
            // Generate unique entry number with retry logic
            $entryNumber = $this->generateEntryNumber($request->entry_date);
            $attempts = 0;
            while (JournalEntry::where('entry_number', $entryNumber)->exists() && $attempts < 10) {
                $entryNumber = $this->generateEntryNumber($request->entry_date, $attempts + 1);
                $attempts++;
            }

            if ($attempts >= 10) {
                return back()->withErrors(['entry_number' => 'Unable to generate unique entry number. Please try again.'])
                ->withInput();
        }

        $journalEntry = JournalEntry::create([
                'tenant_id' => auth()->user()->tenant_id,
                'entry_number' => $entryNumber,
            'entry_date' => $request->entry_date,
            'description' => $request->description,
            'reference_number' => $request->reference_number,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'status' => 'draft',
            'created_by' => auth()->id(),
            'attachments' => $request->attachments ?? null,
        ]);

        foreach ($request->lines as $line) {
                // Skip empty lines (no account selected)
                if (empty($line['account_id'])) {
                    continue;
                }
                
                $debit = (float) ($line['debit'] ?? 0);
                $credit = (float) ($line['credit'] ?? 0);
                
                // Skip lines with both debit and credit as 0
                if ($debit == 0 && $credit == 0) {
                    continue;
                }
                
            $journalEntry->lines()->create([
                'account_id' => $line['account_id'],
                'description' => $line['description'] ?? null,
                    'debit' => $debit,
                    'credit' => $credit,
                    'created_at' => now(),
            ]);
        }

            AuditLogService::log(
                'journal_entries',
                'create',
                "Created journal entry {$journalEntry->entry_number}",
                ['journal_entry_id' => $journalEntry->id]
            );

            return redirect()->route('journal-entries.index')
                ->with('success', 'Journal entry created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())
                ->withInput();
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Journal Entry Creation Error', [
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $errorMessage = 'An error occurred while creating the journal entry.';
            $fieldErrors = [];
            
            if (str_contains($e->getMessage(), 'UNIQUE constraint')) {
                $errorMessage = 'The entry number already exists. Please try again.';
                $fieldErrors['entry_number'] = $errorMessage;
            } elseif (str_contains($e->getMessage(), 'NOT NULL constraint')) {
                // Extract the field name from the error
                if (preg_match('/NOT NULL constraint failed: (\w+\.\w+)/', $e->getMessage(), $matches)) {
                    $field = $matches[1];
                    if (str_contains($field, 'account_id')) {
                        $errorMessage = 'Please select an account for all entry lines.';
                        $fieldErrors['lines'] = $errorMessage;
                    } else {
                        $errorMessage = "Required field is missing: {$field}. Please check your input.";
                        $fieldErrors['database'] = $errorMessage;
                    }
                } else {
                    $errorMessage = 'Required fields are missing. Please ensure all accounts are selected and all required fields are filled.';
                    $fieldErrors['database'] = $errorMessage;
                }
            } elseif (str_contains($e->getMessage(), 'FOREIGN KEY constraint')) {
                $errorMessage = 'Invalid account selected. Please select valid accounts that belong to your tenant.';
                $fieldErrors['lines'] = $errorMessage;
            } else {
                $fieldErrors['database'] = $errorMessage;
            }
            
            return back()->withErrors($fieldErrors)
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Journal Entry Creation Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'An unexpected error occurred: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function show(JournalEntry $journalEntry)
    {
        $journalEntry->load(['lines.account', 'createdBy', 'postedBy']);

        return view('journal-entries.show', compact('journalEntry'));
    }

    public function edit(JournalEntry $journalEntry)
    {
        if ($journalEntry->isPosted()) {
            return redirect()->route('journal-entries.show', $journalEntry)
                ->with('error', 'Cannot edit posted journal entry.');
        }

        $accounts = Account::where('is_active', true)
            ->orderBy('code')
            ->get();

        $journalEntry->load('lines');

        return view('journal-entries.edit', compact('journalEntry', 'accounts'));
    }

    public function update(Request $request, JournalEntry $journalEntry)
    {
        if ($journalEntry->isPosted()) {
            return redirect()->route('journal-entries.show', $journalEntry)
                ->with('error', 'Cannot update posted journal entry.');
        }

        $validated = $request->validate([
            'entry_date' => 'required|date',
            'description' => 'required|string',
            'reference_number' => 'nullable|string',
            'attachments' => 'nullable|string',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    $account = \App\Models\Account::where('id', $value)
                        ->where('tenant_id', auth()->user()->tenant_id)
                        ->first();
                    if (!$account) {
                        $fail('The selected account is invalid or does not belong to your tenant.');
                    }
                },
            ],
            'lines.*.description' => 'nullable|string',
            'lines.*.debit' => 'required_without:lines.*.credit|numeric|min:0',
            'lines.*.credit' => 'required_without:lines.*.debit|numeric|min:0',
        ]);

        $totalDebit = collect($request->lines)->sum(function ($line) {
            return (float) ($line['debit'] ?? 0);
        });
        $totalCredit = collect($request->lines)->sum(function ($line) {
            return (float) ($line['credit'] ?? 0);
        });

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->withErrors(['lines' => 'Total debits must equal total credits.'])
                ->withInput();
        }

        $journalEntry->update([
            'entry_date' => $request->entry_date,
            'description' => $request->description,
            'reference_number' => $request->reference_number,
            'attachments' => $request->attachments ?? null,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
        ]);

        $journalEntry->lines()->delete();

        foreach ($request->lines as $line) {
            $journalEntry->lines()->create([
                'account_id' => $line['account_id'],
                'description' => $line['description'] ?? null,
                'debit' => $line['debit'] ?? 0,
                'credit' => $line['credit'] ?? 0,
                'created_at' => now(),
            ]);
        }

        AuditLogService::log(
            'journal_entries',
            'update',
            "Updated journal entry {$journalEntry->entry_number}",
            ['journal_entry_id' => $journalEntry->id]
        );

        return redirect()->route('journal-entries.show', $journalEntry)
            ->with('success', 'Journal entry updated successfully.');
    }

    public function destroy(JournalEntry $journalEntry)
    {
        if ($journalEntry->isPosted()) {
            return redirect()->route('journal-entries.index')
                ->with('error', 'Cannot delete posted journal entry.');
        }

        $journalEntry->delete();

        AuditLogService::log(
            'journal_entries',
            'delete',
            "Deleted journal entry {$journalEntry->entry_number}",
            ['journal_entry_id' => $journalEntry->id]
        );

        return redirect()->route('journal-entries.index')
            ->with('success', 'Journal entry deleted successfully.');
    }

    public function post($id)
    {
        try {
            $journalEntry = JournalEntry::findOrFail($id);
            
            if ($journalEntry->isPosted()) {
                return back()->with('error', 'Journal entry is already posted.');
            }
            
            $this->accountingService->postJournalEntry($journalEntry);

            AuditLogService::log(
                'journal_entries',
                'post',
                "Posted journal entry {$journalEntry->entry_number}",
                ['journal_entry_id' => $journalEntry->id]
            );
            return redirect()->route('journal-entries.index')
                ->with('success', 'Journal entry posted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reverse($id)
    {
        try {
            $journalEntry = JournalEntry::findOrFail($id);
            
            if (!$journalEntry->isPosted() || $journalEntry->isReversed()) {
                return back()->with('error', 'Journal entry cannot be reversed.');
            }
            
            $this->accountingService->reverseJournalEntry($journalEntry);

            AuditLogService::log(
                'journal_entries',
                'reverse',
                "Reversed journal entry {$journalEntry->entry_number}",
                ['journal_entry_id' => $journalEntry->id]
            );
            return redirect()->route('journal-entries.index')
                ->with('success', 'Journal entry reversed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    protected function generateEntryNumber(string $date, int $offset = 0): string
    {
        $datePrefix = str_replace('-', '', $date);
        $tenantId = auth()->user()->tenant_id;
        $count = JournalEntry::where('tenant_id', $tenantId)
            ->whereDate('entry_date', $date)
            ->count() + 1 + $offset;
        
        // Include tenant ID in the number to ensure uniqueness across tenants
        $tenantPrefix = str_pad(substr($tenantId, -3), 3, '0', STR_PAD_LEFT);
        return 'JE-' . $datePrefix . '-' . $tenantPrefix . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
