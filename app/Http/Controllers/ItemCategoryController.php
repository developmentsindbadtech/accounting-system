<?php

namespace App\Http\Controllers;

use App\Models\ItemCategory;
use Illuminate\Http\Request;

class ItemCategoryController extends Controller
{
    public function index()
    {
        $categories = ItemCategory::with('parent')
            ->orderBy('name')
            ->paginate(20);
        return view('item-categories.index', compact('categories'));
    }

    public function create()
    {
        $parentCategories = ItemCategory::orderBy('name')->get();
        return view('item-categories.create', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:item_categories,id',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;

        ItemCategory::create($validated);

        return redirect()->route('item-categories.index')
            ->with('success', 'Item category created successfully.');
    }

    public function edit($id)
    {
        $category = ItemCategory::findOrFail($id);
        $parentCategories = ItemCategory::where('id', '!=', $id)->orderBy('name')->get();
        return view('item-categories.edit', compact('category', 'parentCategories'));
    }

    public function update(Request $request, $id)
    {
        $category = ItemCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:item_categories,id',
        ]);

        // Prevent setting parent to self
        if (isset($validated['parent_id']) && $validated['parent_id'] == $id) {
            return back()->withErrors(['parent_id' => 'A category cannot be its own parent.'])->withInput();
        }

        // Prevent circular references (setting parent to a descendant)
        if (isset($validated['parent_id'])) {
            $descendantIds = $this->getDescendantIds($category);
            if (in_array($validated['parent_id'], $descendantIds)) {
                return back()->withErrors(['parent_id' => 'A category cannot be a parent of its own descendant.'])->withInput();
            }
        }

        $category->update($validated);

        return redirect()->route('item-categories.index')
            ->with('success', 'Item category updated successfully.');
    }

    private function getDescendantIds(ItemCategory $category): array
    {
        $ids = [];
        foreach ($category->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getDescendantIds($child));
        }
        return $ids;
    }

    public function destroy($id)
    {
        $category = ItemCategory::findOrFail($id);

        // Check if category has items
        if ($category->items()->count() > 0) {
            return redirect()->route('item-categories.index')
                ->with('error', 'Cannot delete category with existing items. Please reassign or delete the items first.');
        }

        // Check if category has children
        if ($category->children()->count() > 0) {
            return redirect()->route('item-categories.index')
                ->with('error', 'Cannot delete category with subcategories. Please delete or reassign subcategories first.');
        }

        $category->delete();

        return redirect()->route('item-categories.index')
            ->with('success', 'Item category deleted successfully.');
    }
}

