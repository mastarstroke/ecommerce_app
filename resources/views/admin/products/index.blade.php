@extends('admin.layouts.admin')

@section('page-title', 'Product Management')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Products</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createProductModal">
            <i class="fas fa-plus"></i> Add New Product
        </button>
    </div>
</div>

<!-- Filters -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form id="filterForm" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Search by name or SKU...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Category</label>
                <select name="category" id="categoryFilter" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" id="statusFilter" class="form-select">
                    <option value="">All</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Stock</label>
                <select name="stock" id="stockFilter" class="form-select">
                    <option value="">All</option>
                    <option value="low">Low Stock (<10)</option>
                    <option value="out">Out of Stock</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-primary w-100" onclick="loadProducts()">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Products Table -->
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="50">
                            <input type="checkbox" id="select-all">
                        </th>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="productsTableBody">
                    @include('admin.products.partials.table_rows', ['products' => $products])
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <button class="btn btn-danger btn-sm" id="bulk-delete" style="display: none;">
                    <i class="fas fa-trash"></i> Delete Selected
                </button>
            </div>
            <div id="paginationLinks">
                {{ $products->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Create Product Modal -->
<div class="modal fade" id="createProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createProductForm" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Product Name *</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">SKU *</label>
                                <input type="text" name="sku" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Category *</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Stock Quantity *</label>
                                <input type="number" name="stock_quantity" class="form-control" value="0" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Price *</label>
                                <input type="number" step="0.01" name="price" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Compare Price (Optional)</label>
                                <input type="number" step="0.01" name="compare_price" class="form-control">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Short Description</label>
                        <textarea name="short_description" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Full Description</label>
                        <textarea name="description" class="form-control" rows="4" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Product Images</label>
                        <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                        <small class="text-muted">You can select multiple images</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Attributes (JSON format)</label>
                                <textarea name="attributes" class="form-control" rows="3" placeholder='{"color": "Red", "size": "Large"}'></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="is_featured" class="form-check-input" value="1">
                                    <label class="form-check-label">Featured Product</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="is_active" class="form-check-input" value="1" checked>
                                    <label class="form-check-label">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="createProductBtn" onclick="createProduct()">
                    Create Product
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editProductForm" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="product_id" id="edit_product_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Product Name *</label>
                                <input type="text" name="name" id="edit_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">SKU *</label>
                                <input type="text" name="sku" id="edit_sku" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Category *</label>
                                <select name="category_id" id="edit_category_id" class="form-select" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Stock Quantity *</label>
                                <input type="number" name="stock_quantity" id="edit_stock_quantity" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Price *</label>
                                <input type="number" step="0.01" name="price" id="edit_price" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Compare Price</label>
                                <input type="number" step="0.01" name="compare_price" id="edit_compare_price" class="form-control">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Short Description</label>
                        <textarea name="short_description" id="edit_short_description" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Full Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="4" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Product Images</label>
                        <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                        <small class="text-muted">Upload new images to replace existing ones</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Attributes (JSON format)</label>
                                <textarea name="attributes" id="edit_attributes" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="is_featured" id="edit_is_featured" class="form-check-input" value="1">
                                    <label class="form-check-label">Featured Product</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="is_active" id="edit_is_active" class="form-check-input" value="1">
                                    <label class="form-check-label">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="updateProductBtn" onclick="updateProduct()">
                    Update Product
                </button>
            </div>

            <div class="mb-3" id="current_images_container" style="display: none;">
                <label class="form-label">Current Images</label>
                <div id="current_images"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentPage = 1;

// Load products with filters
function loadProducts(page = 1) {
    currentPage = page;
    const filters = {
        search: $('#search').val(),
        category: $('#categoryFilter').val(),
        status: $('#statusFilter').val(),
        stock: $('#stockFilter').val(),
        page: page
    };
    
    $.get('{{ route("admin.products.index") }}', filters, function(response) {
        $('#productsTableBody').html($(response).find('#productsTableBody').html());
        $('#paginationLinks').html($(response).find('#paginationLinks').html());
    });
}

// Create product
function createProduct() {
    // Check if already submitting
    if ($('#createProductBtn').prop('disabled')) {
        return;
    }
    
    const formData = new FormData($('#createProductForm')[0]);
    const $btn = $('#createProductBtn');
    const originalHtml = $btn.html();
    
    // Disable button and show loading state
    $btn.html('<span class="loading-spinner"></span> Creating...').prop('disabled', true);
    
    $.ajax({
        url: '{{ route("admin.products.store") }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                // Reset form
                $('#createProductForm')[0].reset();
                $('#createProductModal').modal('hide');
                
                // Reload products
                loadProducts(currentPage);
                
                // Show success toast
                showToast(response.message || 'Product created successfully!', 'success');
            }
        },
        error: function(xhr) {
            let errorMsg = '';
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                const errors = xhr.responseJSON.errors;
                $.each(errors, function(key, value) {
                    errorMsg += value[0] + '\n';
                });
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            } else {
                errorMsg = 'Failed to create product. Please try again.';
            }
            showToast(errorMsg, 'danger');
        },
        complete: function() {
            // Reset button state
            $btn.html(originalHtml).prop('disabled', false);
        }
    });
}

// Edit product - load data into modal
function editProduct(id) {
    $.get('{{ url("admin/products") }}/' + id + '/edit', function(product) {
        $('#edit_product_id').val(product.id);
        $('#edit_name').val(product.name);
        $('#edit_sku').val(product.sku);
        $('#edit_category_id').val(product.category_id);
        $('#edit_price').val(product.price);
        $('#edit_compare_price').val(product.compare_price || '');
        $('#edit_stock_quantity').val(product.stock_quantity);
        $('#edit_short_description').val(product.short_description);
        $('#edit_description').val(product.description);
        
        // Handle attributes
        if (product.attributes) {
            $('#edit_attributes').val(JSON.stringify(product.attributes, null, 2));
        } else {
            $('#edit_attributes').val('');
        }
        
        $('#edit_is_featured').prop('checked', product.is_featured == 1);
        $('#edit_is_active').prop('checked', product.is_active == 1);
        
        // Show current images if any
        if (product.images && product.images.length > 0) {
            let imagesHtml = '';
            product.images.forEach(image => {
                imagesHtml += `<img src="/storage/${image}" class="img-thumbnail me-2" style="width: 60px; height: 60px; object-fit: cover;">`;
            });
            $('#current_images').html(imagesHtml);
        }
        
        $('#editProductModal').modal('show');
    }).fail(function(xhr) {
        showToast('Failed to load product data', 'danger');
    });
}

// Update product
function updateProduct() {
    // Check if already submitting
    if ($('#updateProductBtn').prop('disabled')) {
        return;
    }
    
    const id = $('#edit_product_id').val();
    const formData = new FormData($('#editProductForm')[0]);
    const $btn = $('#updateProductBtn');
    const originalHtml = $btn.html();
    
    // Disable button and show loading state
    $btn.html('<span class="loading-spinner"></span> Updating...').prop('disabled', true);
    
    $.ajax({
        url: '{{ url("admin/products") }}/' + id,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                $('#editProductModal').modal('hide');
                loadProducts(currentPage);
                showToast(response.message || 'Product updated successfully!', 'success');
            }
        },
        error: function(xhr) {
            let errorMsg = '';
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                const errors = xhr.responseJSON.errors;
                $.each(errors, function(key, value) {
                    errorMsg += value[0] + '\n';
                });
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            } else {
                errorMsg = 'Failed to update product. Please try again.';
            }
            showToast(errorMsg, 'danger');
        },
        complete: function() {
            $btn.html(originalHtml).prop('disabled', false);
        }
    });
}

// Delete product
function deleteProduct(id) {
    Swal.fire({
        title: 'Delete Product?',
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return $.ajax({
                url: '{{ url("admin/products") }}/' + id,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' }
            }).catch(error => {
                Swal.showValidationMessage('Failed to delete product: ' + error.responseJSON?.message || 'Unknown error');
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            loadProducts(currentPage);
            showToast('Product deleted successfully!', 'success');
        }
    });
}

// Toggle status
function toggleStatus(id) {
    $.get('{{ url("admin/products/toggle-status") }}/' + id, function(response) {
        if (response.success) {
            loadProducts(currentPage);
            showToast('Status updated!', 'success');
        }
    });
}

// Toggle featured
function toggleFeatured(id) {
    $.get('{{ url("admin/products/toggle-featured") }}/' + id, function(response) {
        if (response.success) {
            loadProducts(currentPage);
            showToast('Featured status updated!', 'success');
        }
    });
}

// Filter on enter key
$('#search').keypress(function(e) {
    if (e.which == 13) {
        loadProducts();
    }
});

// Select all checkbox
$('#select-all').change(function() {
    $('.product-checkbox').prop('checked', $(this).prop('checked'));
    $('#bulk-delete').toggle($('.product-checkbox:checked').length > 0);
});

// Bulk delete
$('#bulk-delete').click(function() {
    if (confirm('Delete selected products?')) {
        const ids = [];
        $('.product-checkbox:checked').each(function() {
            ids.push($(this).val());
        });
        
        $.ajax({
            url: '{{ route("admin.products.bulk-delete") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                products: ids
            },
            success: function(response) {
                if (response.success) {
                    loadProducts(currentPage);
                    showToast(response.message, 'success');
                }
            }
        });
    }
});

// Show toast notification
function showToast(message, type) {
    const toast = $(`
        <div class="toast align-items-center text-white bg-${type} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `);
    $('.toast-container').append(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    setTimeout(() => toast.remove(), 3000);
}
</script>
@endpush
@endsection