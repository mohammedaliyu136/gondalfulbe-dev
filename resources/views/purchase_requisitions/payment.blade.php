@extends('layouts.admin')

@php
$profile = asset(Storage::url('uploads/avatar/'));
@endphp

@section('page-title')
    {{ __('Purchase Requisitions Payments') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('purchase-requisitions.index') }}">{{ __('Purchase Requisition') }}</a></li>
    <li class="breadcrumb-item">{{ __('Purchase Requisitions') }}</li>
@endsection



@section('content')
    <div class="row">
    <!-- Nav Tabs -->
<ul class="nav nav-tabs" id="tabMenu" role="tablist">
    <li class="nav-item" role="presentation">
        <button 
            class="nav-link active" 
            id="disbursements-tab" 
            data-bs-toggle="tab" 
            data-bs-target="#disbursements" 
            type="button" 
            role="tab" 
            aria-controls="disbursements" 
            aria-selected="true">
            Payment History
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button 
            class="nav-link" 
            id="payments-tab" 
            data-bs-toggle="tab" 
            data-bs-target="#payments" 
            type="button" 
            role="tab" 
            aria-controls="payments" 
            aria-selected="false">
            {{ __('New Payments') }}
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button 
            class="nav-link" 
            id="partial-payments-tab" 
            data-bs-toggle="tab" 
            data-bs-target="#partial-payments" 
            type="button" 
            role="tab" 
            aria-controls="partial-payments" 
            aria-selected="false">
            {{ __('Partially Paid') }}
        </button>
    </li>
</ul>

<div class="tab-content mt-3" id="tabContent">
     <!-- Disbursements Tab Pane -->
    <div class="tab-pane fade show active" id="disbursements" role="tabpanel" aria-labelledby="disbursements-tab">
        <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-4">
                    <div class="d-flex align-items-center justify-content-start mt-2">
                        <h5>{{ __('Payment Batches') }}</h5>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="d-flex align-items-center justify-content-end gap-2">
                        <div class="col-xl-2 col-lg-3 col-md-4">
                            <div class="btn-box"></div>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-4">
                            <div class="btn-box"></div>
                        </div>
                        <div class="col-auto float-end"></div>
                    </div> <!-- Closing div for .d-flex -->
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table datatable" id="">
                    <thead>
                        <tr>
                            <th>{{ __('Batch Id') }}</th>
                            <th>{{__('Batch Type') }}</th>
                            <th>{{ __('Reference') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payslip_batches as $payslip_batch)
                            <tr>
                                <td class="Id">
                                    <a href="{{ route('purchase-requisitions.showpayslip', \Crypt::encrypt($payslip_batch->id)) }}" 
                                       class="btn btn-outline-primary">
                                        {{ $payslip_batch->batch_id }}
                                    </a>
                                </td>
                                <td>
                                    @if($payslip_batch->batch_type == 'failed_reprocess')
                                        @php
                                            $parentBatch = \App\Models\PaySlipHrBatch::find($payslip_batch->parent_batch_id);
                                        @endphp
                                        <i class="ti ti-alert-circle text-danger"></i> 
                                        <span class="text-danger">Failed Reprocess - {{ $parentBatch->batch_id ?? 'N/A' }}</span>
                                    @else
                                        <i class="ti ti-check-circle text-success"></i> 
                                        <span class="text-success">Regular</span>
                                    @endif
                                </td>

                                <td>
                                    <p class="text-danger mt-3">{{ $payslip_batch->batch_reference }}</p>
                                </td>
                                <td>
                                    @if ($payslip_batch->status == 0)
                                        <span class="status_badge badge bg-secondary p-2 px-3 rounded">
                                            {{ __(\App\Models\PaySlipFarmerBatch::$statues[$payslip_batch->status]) }}
                                        </span>
                                    @elseif($payslip_batch->status == 1)
                                        <span class="status_badge badge bg-warning p-2 px-3 rounded">
                                            {{ __(\App\Models\PaySlipFarmerBatch::$statues[$payslip_batch->status]) }}
                                        </span>
                                    @elseif($payslip_batch->status == 2)
                                        <span class="status_badge badge bg-danger p-2 px-3 rounded">
                                            {{ __(\App\Models\PaySlipFarmerBatch::$statues[$payslip_batch->status]) }}
                                        </span>
                                    @elseif($payslip_batch->status == 3)
                                        <span class="status_badge badge bg-info p-2 px-3 rounded">
                                            {{ __(\App\Models\PaySlipFarmerBatch::$statues[$payslip_batch->status]) }}
                                        </span>
                                    @elseif($payslip_batch->status == 4)
                                        <span class="status_badge badge bg-primary p-2 px-3 rounded">
                                            {{ __(\App\Models\PaySlipFarmerBatch::$statues[$payslip_batch->status]) }}
                                        </span>
                                    @elseif($payslip_batch->status == 5)
                                        <span class="status_badge badge bg-danger p-2 px-3 rounded">
                                            {{ __(\App\Models\PaySlipFarmerBatch::$statues[$payslip_batch->status]) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="Action">
                                    <div class="action-btn me-2">
                                        <a href="{{ route('purchase-requisitions.showpayslip', \Crypt::encrypt($payslip_batch->id)) }}"
                                           class="mx-3 btn btn-sm align-items-center bg-warning" 
                                           data-bs-toggle="tooltip" title="Show" 
                                           data-original-title="{{ __('Detail') }}">
                                            <i class="ti ti-eye text-white"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div> <!-- Closing div for .table-responsive -->
        </div> <!-- Closing div for .card-body -->
    </div> <!-- Closing div for .card -->
    </div>
    
    <!-- Payments Tab Pane -->
    <div class="tab-pane fade" id="payments" role="tabpanel" aria-labelledby="payments-tab">
        <form id="bulk-payment-form" action="{{ route('purchase-requisitions.bulk_payment_store') }}" method="POST">
    @csrf
    <div class="table-responsive">
    <table class=" table table-striped table-condensed">
        <thead>
            <tr>
                <th>
                    <input type="checkbox" id="select-all" /> <!-- Check all -->
                </th>
                <th>#</th>
                <th>Requisition Title</th>
                <th>Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($requisitions as $pr)
                <tr>
                    <td>
                        <input type="checkbox" name="requisition_ids[]" value="{{ $pr->id }}" 
                               class="requisition-checkbox" 
                               data-title="{{ $pr->title }}" 
                               data-amount="{{ $pr->totalApprovedCost() }}" />
                    </td>
                    <td>
                        <a href="{{ route('purchase-requisitions.show', \Crypt::encrypt($pr->id)) }}" class="btn btn-outline-primary">
                            PR-{{ str_pad($pr->id, 5, '0', STR_PAD_LEFT) }}
                        </a>
                    </td>
                    <td>{{ $pr->title }}</td>
                    <td>₦{{ number_format($pr->totalApprovedCost(), 2, '.', ',') }}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger" onclick="cancelPayment('{{ route('purchase-requisitions.cancel-payment', \Crypt::encrypt($pr->id)) }}')">
                            Cancel
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No purchase requisitions available for payment.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="text-right m-4 float-end">
        <button type="button" class="btn btn-primary trigger-payment-modal" disabled>Process Bulk Payment</button>
    </div>
    </div>
</form>
</div>

    <!-- Partial Payments Tab Pane -->
    <div class="tab-pane fade" id="partial-payments" role="tabpanel" aria-labelledby="partial-payments-tab">
        <form id="bulk-partial-payment-form" action="{{ route('purchase-requisitions.bulk_payment_store') }}" method="POST">
        @csrf
        <div class="table-responsive">
        <table class=" table table-striped table-condensed">
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" id="select-all-partial" />
                    </th>
                    <th>#</th>
                    <th>Requisition Title</th>
                    <th>Total Cost</th>
                    <th>Paid Amount</th>
                    <th>Remaining Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($partiallyPaidRequisitions as $pr)
                    @php
                        $paidAmount = $pr->paymentBatches->sum('pivot.amount');
                        $remainingBalance = $pr->totalApprovedCost() - $paidAmount;
                        // Avoid displaying floating point errors 
                        if ($remainingBalance < 0.01) $remainingBalance = 0;
                    @endphp
                    <tr>
                        <td>
                             <input type="checkbox" name="requisition_ids[]" value="{{ $pr->id }}" 
                               class="requisition-checkbox-partial" 
                               data-title="{{ $pr->title }}" 
                               data-amount="{{ $remainingBalance }}" /> 
                        </td>
                        <td>
                            <a href="{{ route('purchase-requisitions.show', \Crypt::encrypt($pr->id)) }}" class="btn btn-outline-primary">
                                PR-{{ str_pad($pr->id, 5, '0', STR_PAD_LEFT) }}
                            </a>
                        </td>
                        <td>{{ $pr->title }}</td>
                         <td>₦{{ number_format($pr->totalApprovedCost(), 2, '.', ',') }}</td>
                         <td>₦{{ number_format($paidAmount, 2, '.', ',') }}</td>
                         <td class="text-danger">₦{{ number_format($remainingBalance, 2, '.', ',') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No partially paid requisitions available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
         <div class="text-right m-4 float-end">
            <button type="button" class="btn btn-primary trigger-payment-modal" disabled>Process Balance Payment</button>
        </div>
        </div>
        </form>
    </div>

    <!-- Partial Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Payment Configuration</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Requisition</th>
                                <th>Total Cost</th>
                                <th>Payment Type</th>
                                <th>Amount to Pay</th>
                            </tr>
                        </thead>
                        <tbody id="payment-modal-body">
                            <!-- Rows injected by JS -->
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirm-payment-btn">Confirm Payment</button>
                </div>
            </div>
        </div>
    </div>


<script>
    function setupTab(selectors) {
        const selectAll = document.getElementById(selectors.selectAllId);
        const checkboxesSelector = selectors.checkboxClass;
        const triggerBtn = document.querySelector(selectors.triggerBtnSelector); // We need to find the specific button for this form
        
        // Find the form that contains these checkboxes to scope the button finding
        // But since we have unique classes/ids, we can just find the button in the same form?
        // Actually, let's just use the button closest to these checkboxes.
        
        if(!selectAll) return;

        selectAll.addEventListener('change', function (e) {
            const checkboxes = document.querySelectorAll('.' + checkboxesSelector);
            checkboxes.forEach(checkbox => checkbox.checked = e.target.checked);
            toggleButton();
        });

        document.querySelectorAll('.' + checkboxesSelector).forEach(checkbox => {
            checkbox.addEventListener('change', toggleButton);
        });

        function toggleButton() {
             // Find button inside the active tab/form
             // Simpler: Just check if ANY checkbox in the specific group is checked
             const checked = document.querySelectorAll('.' + checkboxesSelector + ':checked');
             // We need to resolve which button to enable.
             // Let's passed the button ID or find it relative to the checkboxes
             const form = selectAll.closest('form');
             const btn = form.querySelector('.trigger-payment-modal');
             if(btn) btn.disabled = checked.length === 0;
        }
    }

    setupTab({
        selectAllId: 'select-all',
        checkboxClass: 'requisition-checkbox'
    });
    
    setupTab({
        selectAllId: 'select-all-partial',
        checkboxClass: 'requisition-checkbox-partial'
    });


    // Open Modal logic
    document.querySelectorAll('.trigger-payment-modal').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            
            const form = this.closest('form');
            const modalBody = document.getElementById('payment-modal-body');
            modalBody.innerHTML = ''; // Clear previous

            // Get checked items FROM THIS FORM ONLY
            const checkboxes = form.querySelectorAll('input[name="requisition_ids[]"]:checked');
            
            // Set the form ID on the modal confirm button so we know which form to submit
            document.getElementById('confirm-payment-btn').setAttribute('data-form-id', form.id);

            checkboxes.forEach(cb => {
                const id = cb.value;
                const title = cb.getAttribute('data-title');
                const amount = parseFloat(cb.getAttribute('data-amount'));
                
                const tr = document.createElement('tr');
                
                tr.innerHTML = `
                    <td>${title}</td>
                    <td>₦${amount.toLocaleString('en-NG', {minimumFractionDigits: 2})}</td>
                    <td>
                        <div class="form-check form-switch">
                            <input class="form-check-input payment-type-toggle" type="checkbox" id="toggle-${id}" data-id="${id}" checked>
                            <label class="form-check-label" for="toggle-${id}">Full Payment</label>
                        </div>
                    </td>
                    <td>
                        <input type="number" class="form-control payment-amount-input" 
                               id="amount-${id}" 
                               data-id="${id}" 
                               data-max="${amount}" 
                               value="${amount}" 
                               step="0.01" 
                               min="0" 
                               max="${amount}" 
                               readonly>
                    </td>
                `;
                modalBody.appendChild(tr);
            });
            
            // Add Event Listeners for Toggles (same as before)
            document.querySelectorAll('.payment-type-toggle').forEach(toggle => {
                toggle.addEventListener('change', function() {
                    const id = this.getAttribute('data-id');
                    const input = document.getElementById(`amount-${id}`);
                    const max = parseFloat(input.getAttribute('data-max'));
                    
                    if (this.checked) {
                        this.nextElementSibling.innerText = "Full Payment";
                        input.value = max;
                        input.setAttribute('readonly', true);
                    } else {
                        this.nextElementSibling.innerText = "Partial Payment";
                        input.removeAttribute('readonly');
                        input.focus();
                    }
                });
            });

            const myModal = new bootstrap.Modal(document.getElementById('paymentModal'));
            myModal.show();
        });
    });
    
    // Handle Confirm
    document.getElementById('confirm-payment-btn').addEventListener('click', function() {
        const formId = this.getAttribute('data-form-id');
        const form = document.getElementById(formId);
        
        // Validate amounts
        let valid = true;
        const inputs = document.querySelectorAll('.payment-amount-input');
        
        // Remove old hidden inputs if any to prevent duplicates on potential re-runs (though modal re-opens fresh usually)
        form.querySelectorAll('input[type="hidden"][name^="amounts"]').forEach(el => el.remove());
        
        inputs.forEach(input => {
            const val = parseFloat(input.value);
            const max = parseFloat(input.getAttribute('data-max'));
            
            if (isNaN(val) || val <= 0) {
                valid = false;
                alert('Amount must be greater than 0');
                return;
            }
            if (val > max + 0.1) { // small buffer for float precision
                 valid = false;
                 alert(`Amount cannot exceed max payable of ₦${max}`);
                 return;
            }
            
            // Create hidden input for amount
            const id = input.getAttribute('data-id');
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = `amounts[${id}]`;
            hidden.value = val;
            form.appendChild(hidden);
        });
        
        if (valid) {
             if (confirm("Are you sure you want to proceed with payment?")) {
                form.submit();
            }
        }
    });

</script>

    </div>
</div>
       
    </div>
@endsection

<form id="cancel-payment-form" method="POST" style="display: none;">
    @csrf
</form>

<script>
    function cancelPayment(url) {
        if (confirm('Are you sure you want to cancel this payment? This will reject the requisition.')) {
            var form = document.getElementById('cancel-payment-form');
            form.action = url;
            form.submit();
        }
    }
</script>
