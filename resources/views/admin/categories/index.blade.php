@extends('admin.layouts.admin')

@section('page-title', 'Category Management')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Categories</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
            <i class="fas fa-plus"></i> Add New Category
        </button>
    </div>
</div>

<!-- Categories Grid -->
<div class="row" id="categoriesGrid">
    @include('admin.categories.partials.category_cards', ['categories' => $categories])
</div>

<!-- Create Category Modal -->
<div class="modal fade" id="createCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createCategoryForm" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Category Name *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <div id="createImagePreview" class="mt-2 text-center"></div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" value="1" checked>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createCategory()">Create Category</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editCategoryForm" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="category_id" id="edit_category_id">
                    <div class="mb-3">
                        <label class="form-label">Category Name *</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <div id="editImagePreview" class="mt-2 text-center"></div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" id="edit_is_active" class="form-check-input" value="1">
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateCategory()">Update Category</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Image preview for create modal
$('input[name="image"]').change(function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#createImagePreview').html(`<img src="${e.target.result}" class="img-fluid rounded" style="max-height: 150px;">`);
        };
        reader.readAsDataURL(file);
    }
});

// Create category
function createCategory() {
    const formData = new FormData($('#createCategoryForm')[0]);
    
    $.ajax({
        url: '{{ route("admin.categories.store") }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                $('#createCategoryModal').modal('hide');
                $('#createCategoryForm')[0].reset();
                $('#createImagePreview').html('');
                loadCategories();
                showToast('Category created successfully!', 'success');
            }
        },
        error: function(xhr) {
            showToast(xhr.responseJSON?.message || 'Error creating category', 'danger');
        }
    });
}

// Edit category
function editCategory(id) {
    $.get('{{ url("admin/categories") }}/' + id, function(category) {
        $('#edit_category_id').val(category.id);
        $('#edit_name').val(category.name);
        $('#edit_description').val(category.description);
        $('#edit_is_active').prop('checked', category.is_active == 1);
        
        if (category.image) {
            $('#editImagePreview').html(`<img src="/storage/${category.image}" class="img-fluid rounded" style="max-height: 150px;">`);
        }
        
        $('#editCategoryModal').modal('show');
    });
}

// Update category
function updateCategory() {
    const id = $('#edit_category_id').val();
    const formData = new FormData($('#editCategoryForm')[0]);
    
    $.ajax({
        url: '{{ url("admin/categories") }}/' + id,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                $('#editCategoryModal').modal('hide');
                loadCategories();
                showToast('Category updated successfully!', 'success');
            }
        }
    });
}

// Delete category
function deleteCategory(id) {
    if (confirm('Are you sure? This will also delete all products in this category?')) {
        $.ajax({
            url: '{{ url("admin/categories") }}/' + id,
            method: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    loadCategories();
                    showToast('Category deleted successfully!', 'success');
                } else {
                    showToast(response.message, 'danger');
                }
            }
        });
    }
}

// Toggle category status
function toggleStatus(id) {
    $.get('{{ url("admin/categories/toggle-status") }}/' + id, function(response) {
        if (response.success) {
            loadCategories();
            showToast('Status updated!', 'success');
        }
    });
}

// Load categories via AJAX
function loadCategories() {
    $.get('{{ route("admin.categories.index") }}', function(response) {
        $('#categoriesGrid').html($(response).find('#categoriesGrid').html());
    });
}
</script>
@endpush
@endsection