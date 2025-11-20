<?php

namespace App\Http\Controllers;

use App\Models\GlossaryItem;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class GlossaryController extends Controller
{
    protected array $modules = [
        'chart_of_accounts' => 'Chart of Accounts',
        'journal_entries' => 'Journal Entries',
        'customers' => 'Customers',
        'invoices' => 'Invoices',
        'vendors' => 'Vendors',
        'bills' => 'Bills',
        'inventory' => 'Inventory',
        'fixed_assets' => 'Fixed Assets',
    ];

    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $sortBy = $request->get('sort_by', 'term');
        $sortDir = $request->get('sort_dir', 'asc');
        $module = $request->get('module', 'all');
        $ifrsOnly = $request->boolean('ifrs_only');
        $search = $request->get('search');

        $allowedSorts = ['term', 'type', 'module', 'code', 'is_ifrs', 'updated_at', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'term';
        }
        $sortDir = in_array(strtolower($sortDir), ['asc', 'desc']) ? strtolower($sortDir) : 'asc';

        $query = GlossaryItem::where('tenant_id', $tenantId);

        if ($module !== 'all' && array_key_exists($module, $this->modules)) {
            $query->where('module', $module);
        }

        if ($ifrsOnly) {
            $query->where('is_ifrs', true);
        }

        if (!empty($search)) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('term', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        $glossaryItems = $query->orderBy($sortBy, $sortDir)
            ->paginate(30)
            ->withQueryString();

        return view('glossary.index', [
            'glossaryItems' => $glossaryItems,
            'modules' => $this->modules,
            'moduleFilter' => $module,
            'ifrsOnly' => $ifrsOnly,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
            'search' => $search,
        ]);
    }

    public function create()
    {
        return view('glossary.create', [
            'modules' => $this->modules,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'term' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'module' => ['required', 'string', 'in:' . implode(',', array_keys($this->modules))],
            'code' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'is_ifrs' => ['nullable', 'boolean'],
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['is_ifrs'] = $request->boolean('is_ifrs');

        $glossaryItem = GlossaryItem::create($validated);

        // Log the creation
        AuditLogService::log(
            'glossary',
            'create',
            "Created glossary item: '{$glossaryItem->term}' ({$this->modules[$glossaryItem->module]})",
            [
                'glossary_item_id' => $glossaryItem->id,
                'term' => $glossaryItem->term,
                'module' => $glossaryItem->module,
                'is_ifrs' => $glossaryItem->is_ifrs,
            ]
        );

        return redirect()->route('glossary.index')
            ->with('success', 'Glossary entry added successfully.');
    }

    public function destroy($id)
    {
        $tenantId = auth()->user()->tenant_id;
        $item = GlossaryItem::where('tenant_id', $tenantId)->findOrFail($id);
        
        // Store item details before deletion for logging
        $term = $item->term;
        $module = $item->module;
        $isIfrs = $item->is_ifrs;
        
        $item->delete();

        // Log the deletion
        AuditLogService::log(
            'glossary',
            'delete',
            "Deleted glossary item: '{$term}' ({$this->modules[$module]})",
            [
                'glossary_item_id' => $id,
                'term' => $term,
                'module' => $module,
                'is_ifrs' => $isIfrs,
            ]
        );

        return redirect()->route('glossary.index')
            ->with('success', 'Glossary entry deleted successfully.');
    }
}
