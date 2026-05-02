<?php

namespace App\Repositories\Interfaces;

interface ProductRepositoryInterface
{
    public function getAll(array $filters = []);
    public function findById(int $id);
    public function findBySlug(string $slug);
    public function getFeaturedProducts(int $limit = 8);
    public function getProductsByCategory(int $categoryId, int $perPage = 12);
    public function searchProducts(string $query);
    public function updateStock(int $productId, int $quantity);
}