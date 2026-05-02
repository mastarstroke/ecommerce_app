@forelse($products as $product)
<tr>
    <td>
        <input type="checkbox" class="product-checkbox" value="{{ $product->id }}">
    </td>
    <td>
        <div class="d-flex align-items-center">
            @if($product->images && count($product->images) > 0)
                <img src="{{ asset('storage/' . $product->images[0]) }}" 
                     class="rounded me-2" 
                     style="width: 50px; height: 50px; object-fit: cover;">
            @else
                <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" 
                     style="width: 50px; height: 50px;">
                    <i class="fas fa-image text-muted"></i>
                </div>
            @endif
            <div>
                <strong>{{ Str::limit($product->name, 40) }}</strong>
                <br>
                <small class="text-muted">{{ Str::limit($product->short_description ?? $product->description, 50) }}</small>
            </div>
        </div>
    </td>
    <td><code>{{ $product->sku }}</code></td>
    <td>{{ $product->category->name ?? 'N/A' }}</td>
    <td>
        <strong>${{ number_format($product->price, 2) }}</strong>
        @if($product->compare_price)
            <br>
            <small class="text-muted text-decoration-line-through">
                ${{ number_format($product->compare_price, 2) }}
            </small>
        @endif
    </td>
    <td>
        @if($product->stock_quantity <= 0)
            <span class="badge bg-danger">Out of Stock</span>
        @elseif($product->stock_quantity < 10)
            <span class="badge bg-warning">{{ $product->stock_quantity }} left</span>
        @else
            <span class="badge bg-success">{{ $product->stock_quantity }} in stock</span>
        @endif
    </td>
    <td>
        <button onclick="toggleStatus({{ $product->id }})" 
                class="badge border-0 {{ $product->is_active ? 'bg-success' : 'bg-secondary' }}">
            {{ $product->is_active ? 'Active' : 'Inactive' }}
        </button>
        <button onclick="toggleFeatured({{ $product->id }})" 
                class="badge border-0 {{ $product->is_featured ? 'bg-warning' : 'bg-light text-dark' }}">
            <i class="fas fa-star"></i> {{ $product->is_featured ? 'Featured' : 'Not Featured' }}
        </button>
    </td>
    <td>
        <div class="btn-group btn-group-sm">
            <button onclick="editProduct({{ $product->id }})" class="btn btn-primary">
                <i class="fas fa-edit"></i>
            </button>
            <button onclick="deleteProduct({{ $product->id }})" class="btn btn-danger">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="8" class="text-center py-5">
        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
        <p>No products found.</p>
    </td>
</tr>
@endforelse