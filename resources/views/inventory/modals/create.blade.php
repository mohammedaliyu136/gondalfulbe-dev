<div class="modal fade" id="createInventoryModal" tabindex="-1" aria-labelledby="createInventoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('inventories.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="createInventoryModalLabel">{{ __('Add New Inventory Item') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="item_name" class="form-label required">{{ __('Item Name') }}</label>
                            <input type="text" name="item_name" id="item_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label">{{ __('Category') }}</label>
                            <input type="text" name="category" id="category" class="form-control">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">{{ __('Description') }}</label>
                        <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quantity" class="form-label required">{{ __('Initial Quantity') }}</label>
                            <input type="number" name="quantity" id="quantity" class="form-control" required min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="minimum_stock" class="form-label">{{ __('Minimum Stock Level') }}</label>
                            <input type="number" name="minimum_stock" id="minimum_stock" class="form-control" min="0" value="0">
                            <small class="text-muted">{{ __('Leave at 0 to disable low stock alerts') }}</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> {{ __('Save Item') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>