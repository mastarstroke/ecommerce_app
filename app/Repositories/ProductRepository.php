<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository implements ProductRepositoryInterface
{
    protected $model;

    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    public function getAll(array $filters = [])
    {
        $query = $this->model->where('is_active', true);

        if (!empty($filters['category'])) {
            $query->whereHas('category', function ($q) use ($filters) {
                $q->where('slug', $filters['category']);
            });
        }

        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
        }

        $sort = $filters['sort'] ?? 'latest';
        switch ($sort) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            default:
                $query->latest();
        }

        return $query->paginate($filters['per_page'] ?? 12);
    }

    public function findById(int $id)
    {
        return $this->model->with('category')->findOrFail($id);
    }

    public function findBySlug(string $slug)
    {
        return $this->model->with('category')->where('slug', $slug)->firstOrFail();
    }

    public function getFeaturedProducts(int $limit = 8)
    {
        return $this->model->where('is_featured', true)
                          ->where('is_active', true)
                          ->latest()
                          ->limit($limit)
                          ->get();
    }

    public function getProductsByCategory(int $categoryId, int $perPage = 12)
    {
        return $this->model->where('category_id', $categoryId)
                          ->where('is_active', true)
                          ->paginate($perPage);
    }

    public function searchProducts(string $query)
    {
        return $this->model->where('name', 'like', "%{$query}%")
                          ->orWhere('description', 'like', "%{$query}%")
                          ->where('is_active', true)
                          ->paginate(12);
    }

    public function updateStock(int $productId, int $quantity)
    {
        $product = $this->findById($productId);
        $product->decrement('stock_quantity', $quantity);
        return $product;
    }
}