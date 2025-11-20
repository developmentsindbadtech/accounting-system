<?php

namespace App\Http\Controllers;

use App\Models\AssetCategory;
use Illuminate\Http\Request;

class AssetCategoryController extends Controller
{
    public function index()
    {
        $categories = AssetCategory::orderBy('name')->paginate(20);
        return view('asset-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('asset-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'depreciation_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;

        AssetCategory::create($validated);

        return redirect()->route('asset-categories.index')
            ->with('success', 'Asset category created successfully.');
    }

    public function edit($id)
    {
        $category = AssetCategory::findOrFail($id);
        return view('asset-categories.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $category = AssetCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'depreciation_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $category->update($validated);

        return redirect()->route('asset-categories.index')
            ->with('success', 'Asset category updated successfully.');
    }

    public function destroy($id)
    {
        $category = AssetCategory::findOrFail($id);

        // Check if category has fixed assets
        if ($category->fixedAssets()->count() > 0) {
            return redirect()->route('asset-categories.index')
                ->with('error', 'Cannot delete category with existing fixed assets. Please reassign or delete the assets first.');
        }

        $category->delete();

        return redirect()->route('asset-categories.index')
            ->with('success', 'Asset category deleted successfully.');
    }
}

