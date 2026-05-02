<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Http\Requests\ProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Traits\LogsActivity;

class AdminProductController extends Controller
{
    use LogsActivity;

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
        
        // Log view action
        $this->logActivity(
            'viewed',
            'product',
            'Admin viewed products list',
            null,
            null,
            ['filters' => $request->all(), 'total' => $products->total()],
            'success'
        );
        
        return view('admin.products.index', compact('products', 'categories'));
    }


    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        
        $this->logActivity(
            'viewed',
            'product',
            'Admin accessed product creation form',
            null,
            null,
            null,
            'success'
        );
        
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
            
            // Log the creation with proper data
            $this->logActivity(
                'created',
                'product',
                "Product '{$product->name}' was created successfully",
                null,
                $product->toArray(),
                ['sku' => $product->sku, 'price' => $product->price],
                'success'
            );

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully!',
                'product' => $product
            ]);
            
        } catch (\Exception $e) {
            // Log the error
            $this->logActivity(
                'failed',
                'product',
                "Failed to create product: {$e->getMessage()}",
                $request->all(),
                null,
                ['error' => $e->getMessage()],
                'failed'
            );
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        try {
            $product = Product::findOrFail($id);
            
            $this->logActivity(
                'viewed',
                'product',
                "Admin viewed edit form for product '{$product->name}'",
                null,
                null,
                ['product_id' => $id],
                'success'
            );
            
            return response()->json($product);
        } catch (\Exception $e) {
            $this->logActivity(
                'failed',
                'product',
                "Failed to load product for editing: {$e->getMessage()}",
                null,
                null,
                ['product_id' => $id, 'error' => $e->getMessage()],
                'failed'
            );
            
            return response()->json(['error' => 'Product not found'], 404);
        }
    }

    public function update(ProductRequest $request, $id)
    {
        try {
            $product = Product::findOrFail($id);
            
            // Store old data before update
            $oldData = $product->toArray();
            
            $data = $request->validated();
            
            // Track changes for logging
            $changes = [];
            
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
                $changes['images'] = 'Updated';
            }
            
            // Handle attributes
            if ($request->filled('attributes')) {
                $data['attributes'] = json_decode($request->attributes, true) ?: [];
                if ($oldData['attributes'] != $data['attributes']) {
                    $changes['attributes'] = 'Updated';
                }
            }
            
            // Check what fields changed
            foreach ($data as $key => $value) {
                if (isset($oldData[$key]) && $oldData[$key] != $value) {
                    $changes[$key] = [
                        'old' => $oldData[$key],
                        'new' => $value
                    ];
                }
            }
            
            $product->update($data);
            
            // Log the update with old and new data
            $this->logActivity(
                'updated',
                'product',
                "Product '{$product->name}' was updated successfully",
                $oldData,
                $product->toArray(),
                ['changes' => $changes, 'product_id' => $product->id],
                'success'
            );

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully!',
                'product' => $product
            ]);
            
        } catch (\Exception $e) {
            $this->logActivity(
                'failed',
                'product',
                "Failed to update product: {$e->getMessage()}",
                $request->all(),
                null,
                ['product_id' => $id, 'error' => $e->getMessage()],
                'failed'
            );
            
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
                $this->logActivity(
                    'failed',
                    'product',
                    "Failed to delete product '{$product->name}' - has existing orders",
                    $product->toArray(),
                    null,
                    ['product_id' => $id, 'reason' => 'has_orders'],
                    'failed'
                );
                
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete product with existing orders.'
                ], 422);
            }
            
            $productData = $product->toArray();
            $productName = $product->name;
            
            // Delete images
            if ($product->images) {
                foreach ($product->images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }
            
            $product->delete();
            
            // Log the deletion with the deleted data
            $this->logActivity(
                'deleted',
                'product',
                "Product '{$productName}' was deleted successfully",
                $productData,
                null,
                ['product_id' => $id, 'sku' => $productData['sku']],
                'success'
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully!'
            ]);
            
        } catch (\Exception $e) {
            $this->logActivity(
                'failed',
                'product',
                "Failed to delete product: {$e->getMessage()}",
                null,
                null,
                ['product_id' => $id, 'error' => $e->getMessage()],
                'failed'
            );
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $product = Product::findOrFail($id);
            $oldStatus = $product->is_active;
            $oldData = $product->toArray();
            
            $product->update(['is_active' => !$product->is_active]);
            $statusText = $product->is_active ? 'activated' : 'deactivated';
            
            // Log the status change
            $this->logActivity(
                'updated',
                'product',
                "Product '{$product->name}' was {$statusText}",
                ['is_active' => $oldStatus],
                ['is_active' => $product->is_active],
                ['product_id' => $id, 'action' => 'toggle_status'],
                'success'
            );
            
            if (request()->ajax()) {
                return response()->json(['success' => true, 'is_active' => $product->is_active]);
            }
            
            return redirect()->back()->with('success', "Product {$statusText} successfully!");
            
        } catch (\Exception $e) {
            $this->logActivity(
                'failed',
                'product',
                "Failed to toggle product status: {$e->getMessage()}",
                null,
                null,
                ['product_id' => $id, 'error' => $e->getMessage()],
                'failed'
            );
            
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to update status');
        }
    }

    public function toggleFeatured($id)
    {
        try {
            $product = Product::findOrFail($id);
            $oldStatus = $product->is_featured;
            
            $product->update(['is_featured' => !$product->is_featured]);
            $statusText = $product->is_featured ? 'featured' : 'unfeatured';
            
            // Log the featured status change
            $this->logActivity(
                'updated',
                'product',
                "Product '{$product->name}' was marked as {$statusText}",
                ['is_featured' => $oldStatus],
                ['is_featured' => $product->is_featured],
                ['product_id' => $id, 'action' => 'toggle_featured'],
                'success'
            );
            
            if (request()->ajax()) {
                return response()->json(['success' => true, 'is_featured' => $product->is_featured]);
            }
            
            return redirect()->back()->with('success', "Product marked as {$statusText}!");
            
        } catch (\Exception $e) {
            $this->logActivity(
                'failed',
                'product',
                "Failed to toggle product featured status: {$e->getMessage()}",
                null,
                null,
                ['product_id' => $id, 'error' => $e->getMessage()],
                'failed'
            );
            
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to update featured status');
        }
    }

    public function bulkDelete(Request $request)
    {
        try {
            $request->validate([
                'products' => 'required|array',
                'products.*' => 'exists:products,id'
            ]);
            
            $products = Product::whereIn('id', $request->products)->get();
            $deletedCount = 0;
            $deletedProducts = [];
            $failedProducts = [];
            
            foreach ($products as $product) {
                if (!$product->orderItems()->exists()) {
                    $productData = $product->toArray();
                    $deletedProducts[] = $productData;
                    
                    if ($product->images) {
                        foreach ($product->images as $image) {
                            Storage::disk('public')->delete($image);
                        }
                    }
                    $product->delete();
                    $deletedCount++;
                } else {
                    $failedProducts[] = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'reason' => 'has_orders'
                    ];
                }
            }
            
            // Log the bulk deletion
            $this->logActivity(
                'deleted',
                'product',
                "Bulk delete operation: {$deletedCount} products deleted",
                null,
                null,
                [
                    'deleted_count' => $deletedCount,
                    'deleted_products' => $deletedProducts,
                    'failed_products' => $failedProducts
                ],
                $deletedCount > 0 ? 'success' : 'failed'
            );
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true, 
                    'message' => "{$deletedCount} products deleted successfully",
                    'failed' => $failedProducts
                ]);
            }
            
            return redirect()->back()->with('success', "{$deletedCount} products deleted successfully!");
            
        } catch (\Exception $e) {
            $this->logActivity(
                'failed',
                'product',
                "Bulk delete operation failed: {$e->getMessage()}",
                $request->all(),
                null,
                ['error' => $e->getMessage()],
                'failed'
            );
            
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to delete products');
        }
    }
}