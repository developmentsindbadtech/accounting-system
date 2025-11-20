<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuditLogService;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sortBy = $request->get('sort_by', 'updated_at');
        $sortDir = $request->get('sort_dir', 'desc');
        
        $query = \App\Models\Item::with('category');
        
        $allowedSorts = ['sku', 'name', 'type', 'standard_cost', 'quantity_on_hand', 'is_active', 'updated_at', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'updated_at';
        }
        
        $sortDir = in_array(strtolower($sortDir), ['asc', 'desc']) ? strtolower($sortDir) : 'desc';
        
        $items = $query->orderBy($sortBy, $sortDir)->paginate(30)->withQueryString();
        return view('inventory.index', compact('items', 'sortBy', 'sortDir'));
    }
    
    public function export(Request $request)
    {
        $sortBy = $request->get('sort_by', 'updated_at');
        $sortDir = $request->get('sort_dir', 'desc');
        
        $query = \App\Models\Item::with('category');
        
        $allowedSorts = ['sku', 'name', 'type', 'standard_cost', 'quantity_on_hand', 'is_active', 'updated_at', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'updated_at';
        }
        
        $sortDir = in_array(strtolower($sortDir), ['asc', 'desc']) ? strtolower($sortDir) : 'desc';
        
        $items = $query->orderBy($sortBy, $sortDir)->get();
        
        $filename = 'inventory-' . date('Y-m-d-His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($items) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['SKU', 'Name', 'Type', 'Category', 'Unit of Measure', 'Standard Cost', 'Quantity on Hand', 'Reorder Point', 'Status', 'Updated At', 'Created At']);
            
            foreach ($items as $item) {
                fputcsv($file, [
                    $item->sku,
                    $item->name,
                    ucfirst($item->type),
                    $item->category->name ?? '',
                    $item->unit_of_measure ?? '',
                    number_format($item->standard_cost, 2),
                    number_format($item->quantity_on_hand, 2),
                    number_format($item->reorder_point ?? 0, 2),
                    $item->is_active ? 'Active' : 'Inactive',
                    $item->updated_at->format('Y-m-d H:i:s'),
                    $item->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = \App\Models\ItemCategory::all();
        return view('inventory.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_code' => 'required|string|max:100|unique:items,sku',
            'name' => 'required|string|max:255',
            'type' => 'required|in:product,service',
            'category_id' => 'nullable|exists:item_categories,id',
            'unit_of_measure' => 'nullable|string|max:50',
            'cost_price' => 'nullable|numeric|min:0',
            'quantity_on_hand' => 'nullable|numeric|min:0',
            'reorder_point' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'attachments' => 'nullable|string',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['sku'] = $validated['item_code'];
        $validated['standard_cost'] = $validated['cost_price'] ?? 0;
        $validated['track_quantity'] = $validated['type'] === 'product';
        $validated['quantity_on_hand'] = $validated['quantity_on_hand'] ?? 0;
        $validated['quantity_reserved'] = 0;
        $validated['is_active'] = $request->has('is_active');
        unset($validated['item_code'], $validated['cost_price']);

        $item = \App\Models\Item::create($validated);

        AuditLogService::log(
            'inventory',
            'create',
            "Created inventory item {$item->name}",
            ['item_id' => $item->id]
        );

        return redirect()->route('inventory.index')
            ->with('success', 'Inventory item created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = \App\Models\Item::with('category')->findOrFail($id);
        
        return view('inventory.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = \App\Models\Item::findOrFail($id);
        
        // Check if item has inventory transactions
        if ($item->inventoryTransactions()->count() > 0) {
            return redirect()->route('inventory.index')
                ->with('error', 'Cannot delete item with existing inventory transactions. Please delete transactions first.');
        }
        
        // Check if item is used in invoice lines or bill lines
        $usedInInvoices = \App\Models\InvoiceLine::where('item_id', $item->id)->exists();
        $usedInBills = \App\Models\BillLine::where('item_id', $item->id)->exists();
        
        if ($usedInInvoices || $usedInBills) {
            return redirect()->route('inventory.index')
                ->with('error', 'Cannot delete item that is used in invoices or bills. Please deactivate it instead.');
        }
        
        $item->delete();

        AuditLogService::log(
            'inventory',
            'delete',
            "Deleted inventory item {$item->name}",
            ['item_id' => $item->id]
        );
        
        return redirect()->route('inventory.index')
            ->with('success', 'Inventory item deleted successfully.');
    }

    public function toggleActive($id)
    {
        $item = \App\Models\Item::findOrFail($id);
        $item->update(['is_active' => !$item->is_active]);
        
        $status = $item->is_active ? 'activated' : 'deactivated';

        AuditLogService::log(
            'inventory',
            'status_change',
            "Inventory item {$item->name} {$status}",
            ['item_id' => $item->id]
        );

        return redirect()->route('inventory.index')
            ->with('success', "Inventory item {$status} successfully.");
    }
}
