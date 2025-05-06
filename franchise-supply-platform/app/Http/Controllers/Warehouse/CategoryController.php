<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller 
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('name')->get();
        
        // Check if this is a warehouse route
        $routeName = request()->route()->getName();
        if (strpos($routeName, 'warehouse.') === 0) {
            return view('warehouse.categories.index', compact('categories'));
        }

        return view('admin.categories.index', compact('categories'));
    }
    
    public function create()
    {
        // Check if this is a warehouse route
        $routeName = request()->route()->getName();
        if (strpos($routeName, 'warehouse.') === 0) {
            return view('warehouse.categories.create');
        }
        
        return view('admin.categories.create');
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories',
            'description' => 'nullable|string',
        ]);
        
        Category::create($validated);
        
        // Check if this is a warehouse route
        $routeName = $request->route()->getName();
        if (strpos($routeName, 'warehouse.') === 0) {
            return redirect()->route('warehouse.categories.index')
                ->with('success', 'Category created successfully.');
        }
        
        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }
    
    public function show(Category $category)
    {
        // Eager load products with their images and variants for better performance
        $category->load(['products.images', 'products.variants']);
        
        // Check if this is a warehouse route
        $routeName = request()->route()->getName();
        if (strpos($routeName, 'warehouse.') === 0) {
            return view('warehouse.categories.show', compact('category'));
        }
        
        return view('admin.categories.show', compact('category'));
    }
    
    public function edit(Category $category)
    {
        // Check if this is a warehouse route
        $routeName = request()->route()->getName();
        if (strpos($routeName, 'warehouse.') === 0) {
            return view('warehouse.categories.edit', compact('category'));
        }
        
        return view('admin.categories.edit', compact('category'));
    }
    
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
        ]);
        
        $category->update($validated);
        
        // Check if this is a warehouse route
        $routeName = $request->route()->getName();
        if (strpos($routeName, 'warehouse.') === 0) {
            return redirect()->route('warehouse.categories.index')
                ->with('success', 'Category updated successfully.');
        }
        
        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }
    
    public function destroy(Category $category)
    {
        // Delete the category
        $category->delete();
        
        // Check if this is a warehouse route
        $routeName = request()->route()->getName();
        if (strpos($routeName, 'warehouse.') === 0) {
            return redirect()->route('warehouse.categories.index')
                ->with('success', 'Category deleted successfully.');
        }
        
        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}