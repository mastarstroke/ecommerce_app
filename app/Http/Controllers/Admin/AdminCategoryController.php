<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(Request $request)
    {
        $query = Category::withCount('products');
        
        // Search filter
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        // Status filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status == 'active');
        }
        
        $categories = $query->latest()->paginate(15);
        
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_active' => 'boolean'
        ]);
        
        $data = $request->all();
        $data['slug'] = Str::slug($request->name);
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('categories', 'public');
            $data['image'] = $path;
        }
        
        Category::create($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Category created successfully']);
        }
        
        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully!');
    }


    public function show($id)
    {
        $category = Category::with('products')->findOrFail($id);
        return view('admin.categories.show', compact('category'));
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_active' => 'boolean'
        ]);
        
        $data = $request->all();
        $data['slug'] = Str::slug($request->name);
        
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $path = $request->file('image')->store('categories', 'public');
            $data['image'] = $path;
        }
        
        $category->update($data);
        
        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Category updated successfully']);
        }
        
        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully!');
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        
        // Check if category has products
        if ($category->products()->exists()) {
            return redirect()->back()
                            ->with('error', 'Cannot delete category with associated products. Move or delete products first.');
        }
        
        // Delete image
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }
        
        $category->delete();
        
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Category deleted successfully']);
        }
        
        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully!');
    }

    public function toggleStatus($id)
    {
        $category = Category::findOrFail($id);
        $category->update(['is_active' => !$category->is_active]);
        
        $status = $category->is_active ? 'activated' : 'deactivated';

        if (request()->ajax()) {
            return response()->json(['success' => true, 'is_active' => $category->is_active]);
        }

        return redirect()->back()->with('success', "Category {$status} successfully!");
    }

    public function getCategories(Request $request)
    {
        $search = $request->get('q', '');
        $categories = Category::where('is_active', true)
                              ->where('name', 'like', "%{$search}%")
                              ->limit(10)
                              ->get(['id', 'name']);
        
        return response()->json($categories);
    }
}