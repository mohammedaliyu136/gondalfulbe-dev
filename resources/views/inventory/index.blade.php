@extends('layouts.admin')

@section('page-title')
    {{ __('Inventory') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Inventory Management') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <!-- Card Header with Title and Action Buttons -->
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ __('Inventories') }}</h5>
                <div class="btn-group">
                    {{-- Add New Item --}}'
                    @can('create inventory')
                    <button type="button" 
                            class="btn btn-sm btn-primary" 
                            data-bs-toggle="modal" 
                            data-bs-target="#addInventoryModal">
                        <i class="ti ti-plus"></i> {{ __('Add Item') }}
                    </button> 
                    @endcan
                    @can('add stock inventory')
                    {{-- Add Stock --}}
                    <button type="button" 
                            class="btn btn-sm btn-info"
                            data-bs-toggle="modal" 
                            data-bs-target="#addStockModal">
                        <i class="ti ti-box-seam"></i> {{ __('Add Stock') }}
                    </button>
                    @endcan
                    @can('issue stock inventory')
                   {{-- Issue Item --}}
                        <button type="button" 
                                class="btn btn-sm btn-success" 
                                data-bs-toggle="modal" 
                                data-bs-target="#issueModal">
                            <i class="ti ti-box-arrow-up"></i> {{ __('Issue Item') }}
                        </button>
                    @endcan
                </div>

            </div>

            <!-- Card Body with Table -->
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th>{{ __('Item Name') }}</th>
                                <th>{{ __('Unit Quantity') }}</th>
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Total Quantity') }}</th>
                                 <th>{{ __('Re-Order level') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inventories as $inv)
                                <tr>
                                    <td class="font-style">{{ $inv->item_name }}</td>
                                    <td class="font-style">{{ $inv->description ?: '-' }}</td>
                                    <td class="font-style">{{ $inv->category ?: '-' }}</td>
                                    <td class="font-style">{{ $inv->quantity }}</td>
                                    <td class="font-style">{{ $inv->reorder_level }}</td>

                                    <td class="d-flex align-items-center">
                                        {{-- Show Inventory --}}
                                        <div class="action-btn">
                                            <a href="#"
                                               class="mx-3 btn btn-sm align-items-center bg-primary"
                                               data-url="{{ URL::to('inventories/'.$inv->id) }}"
                                               data-ajax-popup="true"
                                               data-title="{{ __('Inventory Details') }}"
                                               data-bs-toggle="tooltip"
                                               title="{{ __('Show') }}">
                                                <i class="ti ti-eye text-white"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">{{ __('No inventory items found.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div> <!-- table responsive -->
            </div>
        </div>
    </div>
</div>

{{-- Add Inventory Item Modal --}}
<div class="modal fade" id="addInventoryModal" tabindex="-1" aria-labelledby="addInventoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('inventories.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addInventoryModalLabel">{{ __('Add New Inventory Item') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="item_name" class="form-label">{{ __('Item Name') }}</label>
                        <input type="text" name="item_name" id="item_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">{{ __('Unit of Measurement') }}</label>
                        <textarea name="description" id="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                            <label for="category" class="form-label">{{ __('Category') }}</label>
                            <select name="category" id="category" class="form-control">
                                <option value="">-- Select Category --</option>
                                <option value="packaging material">Packaging Material</option>
                                <option value="raw material">Raw Material</option>
                                <option value="energy">Energy</option>
                                <option value="assets">Assets</option>
                                <option value="cleaning material">Cleaning Material</option>
                                <option value="culture danisco">Culture Danisco</option>
                                <option value="servicing material">Servicing Material</option>
                            </select>
                        </div>
                        <div class="mb-3">
                        <label for="reorder_level" class="form-label">{{ __('Reorder Level') }}</label>
                        <input type="number" name="reorder_level" id="reorder_level" class="form-control" min="1" placeholder="Enter minimum stock before reordering" required>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Save Item') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Stock Modal --}}
<div class="modal fade" id="addStockModal" tabindex="-1" aria-labelledby="addStockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('inventory-stock.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addStockModalLabel">{{ __('Add Stock to Inventory') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="inventory_id" class="form-label">{{ __('Select Item') }}</label>
                        <select name="inventory_id" id="inventory_id" class="form-control" required>
                            <option value="" disabled selected>{{ __('Choose Item') }}</option>
                            @foreach($inventories as $item)
                                <option value="{{ $item->id }}">{{ $item->item_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quantity_added" class="form-label">{{ __('Quantity Added') }}</label>
                        <input type="number" name="quantity_added" id="quantity_added" class="form-control" min="1" required>
                    </div>
                     <div class="mb-3">
                        <label for="purchase_price" class="form-label">{{ __('Unit Purchase Price') }}</label>
                        <input type="number" name="purchase_price" id="purchase_price" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="supplier" class="form-label">{{ __('Supplier') }}</label>
                        <input type="text" name="supplier" id="supplier" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="total_purchase_price" class="form-label">{{ __('Total Purchase Price') }}</label>
                        <input type="number" name="total_purchase_price" id="total_purchase_price" class="form-control" readonly required>
                    </div>

                   
                    <div class="mb-3">
                        <label for="note" class="form-label">{{ __('Note (Optional)') }}</label>
                        <textarea name="note" id="note" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    <button type="submit" class="btn btn-info">{{ __('Save Stock') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="issueModal" tabindex="-1" aria-labelledby="issueModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('inventory-issues.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="issueModalLabel">{{ __('Issue Item') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="inventory_id" class="form-label">{{ __('Select Item') }}</label>
                        <select name="inventory_id" id="issue_inventory_id" class="form-control" required>
                            <option value="" disabled selected>{{ __('Choose Item') }}</option>
                            @foreach($inventories as $item)
                                <option value="{{ $item->id }}">{{ $item->item_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Recipient</label>
                        <input type="text" name="issued_to" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Quantity to Issue</label>
                        <input type="number" name="quantity_issued" id="issue_quantity" 
                               class="form-control" min="1" required>
                        <small id="availableQuantity" class="text-muted">Available: -</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Issued By</label>
                        <input type="text" name="issued_by" class="form-control" >
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    <button type="submit" class="btn btn-info">{{ __('Issue Item') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script-page')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const inventorySelect = document.getElementById("issue_inventory_id");
    const quantityInput   = document.getElementById("issue_quantity");
    const availableText   = document.getElementById("availableQuantity");

    if (inventorySelect && quantityInput && availableText) {
        inventorySelect.addEventListener("change", function () {
            let inventoryId = this.value;
            if (!inventoryId) return;

            fetch(`/inventory/${inventoryId}/quantity`) 
                .then(response => response.json())
                .then(data => {
                    console.log("Fetched:", data);
                    if (data.quantity !== undefined) {
                        // update max attribute
                        quantityInput.max = data.quantity;

                        // also reset value if it's greater than available
                        if (quantityInput.value > data.quantity) {
                            quantityInput.value = data.quantity;
                        }

                        // show available stock
                        availableText.textContent = `Available: ${data.quantity}`;
                    }
                })
                .catch(err => {
                    console.error("Error fetching quantity:", err);
                });
        });

        // Extra safeguard: prevent typing above max
        quantityInput.addEventListener("input", function () {
            let max = parseInt(this.max, 10);
            if (max && this.value > max) {
                this.value = max;
            }
        });
    }
});
</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const qtyInput = document.getElementById("quantity_added");
        const priceInput = document.getElementById("purchase_price");
        const totalInput = document.getElementById("total_purchase_price");

        function calculateTotal() {
            const qty = parseFloat(qtyInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            totalInput.value = qty * price;
        }

        qtyInput.addEventListener("input", calculateTotal);
        priceInput.addEventListener("input", calculateTotal);
    });
    
    document.addEventListener("DOMContentLoaded", function () {
    // Find all forms inside modals
    document.querySelectorAll(".modal form").forEach(form => {
        form.addEventListener("submit", function (e) {
            const submitBtn = form.querySelector("[type=submit]");
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
            }
        });
    });
});
</script> 

@endpush

 





