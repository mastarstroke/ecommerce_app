<?php

namespace App\Http\Controllers;

use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Models\Category;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class ProductController extends Controller
{
    use LogsActivity;
    protected $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['category', 'sort', 'search', 'min_price', 'max_price']);
        $filters['per_page'] = 12;
        
        $products = $this->productRepository->getAll($filters);
        $categories = Category::where('is_active', true)->get();
        $featuredProducts = $this->productRepository->getFeaturedProducts(4);
        
        return view('products.index', compact('products', 'categories', 'featuredProducts'));
    }

    public function show($slug)
    {
        $product = $this->productRepository->findBySlug($slug);
        $relatedProducts = $this->productRepository->getProductsByCategory(
            $product->category_id, 
            4
        );
        
        return view('products.show', compact('product', 'relatedProducts'));
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        $products = $this->productRepository->searchProducts($query);
        $this->logInfo('product_search', 'Product search performed', [
            'query' => $query,
            'result_count' => $products->count(),
            'user_id' => auth()->id(),
        ]);

        $this->logActivity(
            'searched',
            'product',
            'User searched for products',
            null,
            null,
            [
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name,
                'products_count' => $products->total(),
                'products_page' => $products->currentPage()
            ],
            'success'
        );
        
        return view('products.search', compact('products', 'query'));
    }
    
}