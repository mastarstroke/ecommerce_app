@extends('layouts.app')

@section('title', 'Shop')

@section('content')
<div class="container py-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-3">Filters</h5>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Categories</label>
                        <div class="list-group list-group-flush">
                            <a href="{{ route('products.index') }}" class="list-group-item list-group-item-action {{ !request('category') ? 'active' : '' }}">
                                All Products
                            </a>
                            @foreach($categories as $category)
                                <a href="{{ route('products.index', ['category' => $category->slug]) }}" 
                                   class="list-group-item list-group-item-action {{ request('category') == $category->slug ? 'active' : '' }}">
                                    {{ $category->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Products Grid -->
        <div class="col-md-9">
            <div class="row g-4">
                @forelse($products as $product)
                    <div class="col-md-6 col-lg-4">
                        <div class="card product-card h-100 shadow-sm">
                            @if($product->images && count($product->images) > 0)
                                <img src="{{ asset('storage/' . $product->images[0]) }}" 
                                     class="card-img-top product-image" 
                                     alt="{{ $product->name }}"
                                     style="height: 200px; object-fit: cover;">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            @endif
                            
                            <div class="card-body">
                                <h5 class="card-title">{{ Str::limit($product->name, 60) }}</h5>
                                <p class="card-text text-muted small">{{ Str::limit($product->short_description ?? $product->description, 80) }}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="price-tag fs-4 fw-bold text-primary">${{ number_format($product->price, 2) }}</span>
                                        @if($product->compare_price && $product->compare_price > $product->price)
                                            <span class="old-price text-muted text-decoration-line-through ms-2">${{ number_format($product->compare_price, 2) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-footer bg-transparent border-0 pb-3">
                                <div class="d-grid gap-2">
                                    <a href="{{ route('products.show', $product->slug) }}" class="btn btn-outline-primary">View Details</a>
                                    @if($product->isInStock())
                                        <button onclick="addToCart({{ $product->id }}, 1, this)" 
                                                class="btn btn-add-to-cart btn-primary w-100">
                                            <i class="fas fa-shopping-cart"></i> Add to Cart
                                        </button>
                                    @else
                                        <button class="btn btn-secondary w-100" disabled>
                                            <i class="fas fa-times-circle"></i> Out of Stock
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                            <h3>No products found</h3>
                            <p class="text-muted">Try adjusting your filters or search criteria.</p>
                        </div>
                    </div>
                @endforelse
            </div>
            
            <div class="mt-5">
                {{ $products->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection