<?php

namespace App\Http\Controllers;

use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
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
        
        return view('products.search', compact('products', 'query'));
    }
}