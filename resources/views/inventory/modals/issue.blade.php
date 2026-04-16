

            <form action="{{ route('inventory-issues.store') }}" method="POST">
                @csrf
                <input type="hidden" name="inventory_id" id="issue_inventory_id">

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Item') }}</label>
                        <input type="text" id="issueItemName" class="form-control" value="{{$inventory->item_name}}" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="issued_to" class="form-label required">{{ __('Issued To') }}</label>
                        <input type="text" name="issued_to" id="issued_to" class="form-control" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="issue_date" class="form-label required">{{ __('Issue Date') }}</label>
                            <input type="date" name="issue_date" id="issue_date" class="form-control" required value="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="issue_quantity" class="form-label required">{{ __('Quantity') }}</label>
                            <input type="number" name="quantity" id="issue_quantity" class="form-control" required min="1">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">{{ __('Notes') }}</label>
                        <textarea name="notes" id="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-box-arrow-up me-1"></i> {{ __('Issue Item') }}
                    </button>
                </div>
            </form>
        