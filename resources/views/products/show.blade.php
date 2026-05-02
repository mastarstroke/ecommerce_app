@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="container py-5">
    <div class="row">
        <!-- Product Images -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    @if($product->images && count($product->images) > 0)
                        <img src="{{ asset('storage/' . $product->images[0]) }}" 
                             class="img-fluid" 
                             alt="{{ $product->name }}"
                             id="main-product-image"
                             style="max-height: 400px; object-fit: contain;">
                    @else
                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 400px;">
                            <i class="fas fa-image fa-5x text-muted"></i>
                        </div>
                    @endif
                </div>
            </div>
            
            @if($product->images && count($product->images) > 1)
                <div class="mt-3">
                    <div class="row g-2">
                        @foreach($product->images as $image)
                            <div class="col-3">
                                <img src="{{ asset('storage/' . $image) }}" 
                                     class="img-thumbnail product-thumbnail" 
                                     style="height: 80px; width: 100%; object-fit: cover; cursor: pointer;"
                                     onclick="document.getElementById('main-product-image').src = this.src">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
        
        <!-- Product Details -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="display-5 fw-bold mb-3">{{ $product->name }}</h1>
                    
                    <div class="mb-3">
                        <span class="text-warning">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </span>
                        <span class="text-muted">(24 reviews)</span>
                    </div>
                    
                    <div class="mb-3">
                        <span class="display-6 fw-bold text-primary">${{ number_format($product->price, 2) }}</span>
                        @if($product->compare_price && $product->compare_price > $product->price)
                            <span class="text-muted text-decoration-line-through ms-2 fs-5">${{ number_format($product->compare_price, 2) }}</span>
                            <span class="badge bg-success ms-2">Save {{ round((($product->compare_price - $product->price) / $product->compare_price) * 100) }}%</span>
                        @endif
                    </div>
                    
                    <div class="mb-3">
                        <h6>Availability: 
                            @if($product->isInStock())
                                <span class="text-success">
                                    <i class="fas fa-check-circle"></i> In Stock ({{ $product->stock_quantity }} available)
                                </span>
                            @else
                                <span class="text-danger">
                                    <i class="fas fa-times-circle"></i> Out of Stock
                                </span>
                            @endif
                        </h6>
                        <h6>SKU: <span class="text-muted">{{ $product->sku }}</span></h6>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-muted">{{ $product->description }}</p>
                    </div>
                    
                    @if($product->isInStock())
                        <div class="row g-3 align-items-end">
                            <div class="col-auto">
                                <label class="form-label fw-semibold">Quantity</label>
                                <div class="input-group">
                                    <button class="btn btn-outline-secondary" type="button" onclick="decrementQuantity()">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" 
                                           id="quantity" 
                                           class="form-control text-center" 
                                           value="1" 
                                           min="1" 
                                           max="{{ $product->stock_quantity }}"
                                           style="width: 70px;">
                                    <button class="btn btn-outline-secondary" type="button" onclick="incrementQuantity()">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col">
                                <button onclick="addToCart({{ $product->id }}, $('#quantity').val(), this)" 
                                        class="btn btn-add-to-cart btn-primary btn-lg w-100">
                                    <i class="fas fa-shopping-cart"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function incrementQuantity() {
        let input = $('#quantity');
        let max = parseInt(input.attr('max'));
        let current = parseInt(input.val());
        if (current < max) {
            input.val(current + 1);
        }
    }
    
    function decrementQuantity() {
        let input = $('#quantity');
        let current = parseInt(input.val());
        if (current > 1) {
            input.val(current - 1);
        }
    }
</script>
@endsection