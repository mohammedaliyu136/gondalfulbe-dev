<div class="modal fade" id="editInventoryModal" tabindex="-1" aria-labelledby="editInventoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editInventoryForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_inventory_id" name="id">
                
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editInventoryModalLabel">{{ __('Edit Inventory Item') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_item_name" class="form-label required">{{ __('Item Name') }}</label>
                            <input type="text" name="item_name" id="edit_item_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_category" class="form-label">{{ __('Category') }}</label>
                            <input type="text" name="category" id="edit_category" class="form-control">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">{{ __('Description') }}</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_quantity" class="form-label required">{{ __('Current Quantity') }}</label>
                            <input type="number" name="quantity" id="edit_quantity" class="form-control" required min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_minimum_stock" class="form-label">{{ __('Minimum Stock Level') }}</label>
                            <input type="number" name="minimum_stock" id="edit_minimum_stock" class="form-control" min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> {{ __('Update Item') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>