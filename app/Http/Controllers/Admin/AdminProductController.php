<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Http\Requests\ProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminProductController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(Request $request)
    {
        $query = Product::with('category');
        
        // Search filter
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
        }
        
        // Category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        
        // Status filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status == 'active');
        }
        
        // Stock filter
        if ($request->filled('stock')) {
            if ($request->stock == 'low') {
                $query->where('stock_quantity', '<', 10);
            } elseif ($request->stock == 'out') {
                $query->where('stock_quantity', 0);
            }
        }
        
        $products = $query->latest()->paginate(15);
        $categories = Category::where('is_active', true)->get();
        
        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Show form to create new product
     */
    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(ProductRequest $request)
    {
        try {
            $data = $request->validated();
            $data['slug'] = Str::slug($data['name']) . '-' . uniqid();
            
            // Handle image uploads
            if ($request->hasFile('images')) {
                $images = [];
                foreach ($request->file('images') as $image) {
                    $path = $image->store('products', 'public');
                    $images[] = $path;
                }
                $data['images'] = $images;
            }
            
            // Handle attributes
            if ($request->filled('attributes')) {
                $data['attributes'] = json_decode($request->attributes, true) ?: [];
            }
            
            $product = Product::create($data);
            
            return response()->json([
                'success' => true,
                'message' => 'Product created successfully!',
                'product' => $product
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        return response()->json($product);
    }

    public function update(ProductRequest $request, $id)
    {
        try {
            $product = Product::findOrFail($id);
            $data = $request->validated();
            
            // Handle image uploads
            if ($request->hasFile('images')) {
                // Delete old images
                if ($product->images) {
                    foreach ($product->images as $oldImage) {
                        Storage::disk('public')->delete($oldImage);
                    }
                }
                
                $images = [];
                foreach ($request->file('images') as $image) {
                    $path = $image->store('products', 'public');
                    $images[] = $path;
                }
                $data['images'] = $images;
            }
            
            // Handle attributes
            if ($request->filled('attributes')) {
                $data['attributes'] = json_decode($request->attributes, true) ?: [];
            }
            
            $product->update($data);
            
            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully!',
                'product' => $product
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            
            // Check if product has orders
            if ($product->orderItems()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete product with existing orders.'
                ], 422);
            }
            
            // Delete images
            if ($product->images) {
                foreach ($product->images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }
            
            $product->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully!'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        $product = Product::findOrFail($id);
        $product->update(['is_active' => !$product->is_active]);
        
        if (request()->ajax()) {
            return response()->json(['success' => true, 'is_active' => $product->is_active]);
        }
        
        return redirect()->back()->with('success', 'Status updated!');
    }


    public function toggleFeatured($id)
    {
        $product = Product::findOrFail($id);
        $product->update(['is_featured' => !$product->is_featured]);
        
        if (request()->ajax()) {
            return response()->json(['success' => true, 'is_featured' => $product->is_featured]);
        }
        
        return redirect()->back()->with('success', 'Featured status updated!');
    }


    public function bulkDelete(Request $request)
    {
        $request->validate([
            'products' => 'required|array',
            'products.*' => 'exists:products,id'
        ]);
        
        $products = Product::whereIn('id', $request->products)->get();
        $deletedCount = 0;
        
        foreach ($products as $product) {
            if (!$product->orderItems()->exists()) {
                if ($product->images) {
                    foreach ($product->images as $image) {
                        Storage::disk('public')->delete($image);
                    }
                }
                $product->delete();
                $deletedCount++;
            }
        }
        
        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => "{$deletedCount} products deleted successfully"]);
        }
        
        return redirect()->back()->with('success', "{$deletedCount} products deleted successfully!");
    }
}