
            <form action="{{ route('inventory-stock.store') }}" method="POST">
                @csrf

                <div class="modal-body">
                    <input type="hidden" name="inventory_id" value="{{$inventory->id}}">
                    <div class="mb-3">
                        <label>{{ __('Item') }}</label>
                        <input type="text" id="stockItemName" class="form-control" value="{{$inventory->item_name}}" readonly>
                    </div>
                    <div class="mb-3">
                        <label>{{ __('Supplier') }}</label>
                        <input type="text" name="supplier" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>{{ __('Quantity to Add') }}</label>
                        <input type="number" name="quantity_added" class="form-control" required min="1">
                    </div>
                    <div class="mb-3">
                        <label>{{ __('Purchase Price') }}</label>
                        <input type="number" name="purchase_price" class="form-control" required min="1">
                    </div>
                    <div class="mb-3">
                        <label>{{ __('Note') }}</label>
                        <textarea name="note" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-info">{{ __('Add Stock') }}</button>
                </div>
            </form>
 