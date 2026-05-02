@forelse($categories as $category)
<div class="col-md-4 col-lg-3 mb-4">
    <div class="card h-100 shadow-sm">
        <div class="card-body text-center">
            @if($category->image)
                <img src="{{ asset('storage/' . $category->image) }}" 
                     class="rounded-circle mb-3" 
                     style="width: 80px; height: 80px; object-fit: cover;">
            @else
                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                     style="width: 80px; height: 80px;">
                    <i class="fas fa-folder fa-3x text-muted"></i>
                </div>
            @endif
            
            <h5 class="card-title">{{ $category->name }}</h5>
            <p class="card-text small text-muted">{{ Str::limit($category->description, 60) }}</p>
            
            <div class="mb-2">
                <span class="badge bg-info">{{ $category->products_count }} Products</span>
                <button onclick="toggleStatus({{ $category->id }})" 
                        class="badge border-0 {{ $category->is_active ? 'bg-success' : 'bg-secondary' }}">
                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                </button>
            </div>
            
            <div class="btn-group w-100">
                <button onclick="editCategory({{ $category->id }})" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button onclick="deleteCategory({{ $category->id }})" class="btn btn-sm btn-danger">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>
@empty
<div class="col-12">
    <div class="text-center py-5">
        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
        <p>No categories found.</p>
    </div>
</div>
@endforelse

<div class="col-12">
    {{ $categories->links() }}
</div>