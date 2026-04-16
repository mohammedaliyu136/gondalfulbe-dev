@extends('layouts.admin')
@push('script-page')
@endpush
@section('page-title')
    {{__('Payslip-Detail')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('purchase-requisitions.index') }}">{{ __('Purchase Requisition') }}</a></li>
    <li class="breadcrumb-item">{{$payslip['batch_id']}}</li>

@endsection

@section('action-btn')
    <div class="float-end d-flex">
        &nbsp;
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0 text-white">Requisition Pay Slip Information</h4>
        </div>
        
        <div class="card-body">
            <div class="row mb-2">
                @if(\Auth::user()->can('approve payment requisition'))
                    @if($payslip->status == 0)
                    <div class="col-auto float-end mt-4">
                        <!--<a href="#"-->
                        <!--    data-url="{{route('purchase-requisitions.approvepayment', \Crypt::encrypt($payslip->id))}}" -->
                        <!--    data-ajax-popup="true" -->
                        <!--    class="mx-3 btn btn-primary" -->
                        <!--    data-bs-toggle="tooltip"-->
                        <!--    title="Approve payment">{{ __('Approve payments') }}</a>-->
                        <a href="{{ route('purchase-requisitions.approvepayment', \Crypt::encrypt($payslip->id)) }}" 
                           class="btn btn-primary"
                           data-bs-toggle="tooltip" 
                           title="{{ __('approve ') }}" 
                           data-original-title="{{ __('payslip') }}"
                           onclick="return confirm('Are you sure you want to approve this payment?');">
                           {{ __('Approve payments') }}
                        </a>
                    </div>
                    @endif
                @endif
                @if(\Auth::user()->can('initialise payment requisition'))
                    @if($payslip->status == 1)
                    <div class="col-auto float-end mt-4">
                        <a href="{{route('purchase-requisitions.initialisepayment', \Crypt::encrypt($payslip->id))}}" class="btn btn-info"
                            data-bs-toggle="tooltip" title="{{ __('pay now') }}"
                            data-original-title="{{ __('payslip') }}">{{ __('Pay now') }}
                        </a> 
                    </div>
                    @endif
                @endif
                @if(\Auth::user()->can('resend token requisition'))
                     @if($payslip->status == 3)
                    <div class="col-auto float-end mt-4">
                        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#otpModal">
                           {{ __('Authorize Bulk Transfer') }}
                        </button>
                        
                        <button type="button" class="btn btn-info" id='resend-token' data-ref="{{\Crypt::encrypt($payslip->batch_reference)}}" >
                           {{ __('Resend Token') }}
                        </button>
                    </div>
                    @endif
                @endif

            </div>
            
            <div class="row">
                <!-- Total Count and Total Sum -->
                <div class="col-md-6">
                    <div class="row p-3 bg-light rounded">
                        
                        <div class="col-md-6">
                            <h6 class="mb-1 text-muted">Total Paying Amount</h6>
                            <h4 class="text-success">₦<?= number_format($totalSum, 2); ?></h4>
                            <h6 class="mb-1 text-muted">Total Recipients</h6>
                            <h4 class="text-primary mb-3"><?= $totalCount; ?></h4>
                            <h6 class="mb-1 text-muted">Total fee</h6>
                            <h4 class="text-success">₦<?= number_format($payslip->total_fee, 2); ?></h4>
                        </div>
                        <div class="col-md-6">
                            @if($failedCount > 0)
                            <h6 class="mb-1 text-muted">Total Failed Amount</h6>
                            <h4 class="text-danger">₦<?= number_format($failedTotalSum, 2); ?></h4>
                            <h6 class="mb-1 text-muted">Failed Transaction Count </h6>
                            <h4 class="text-danger mb-3"><?= $failedCount; ?></h4>
                            @if($payslip->status == 4)
                            <p>
                                <a href="{{ route('payslip.bulktransfer.regenerate', \Crypt::encrypt($payslip->id)) }}" 
                                   class="btn btn-sm btn-warning"
                                   data-bs-toggle="tooltip" 
                                   title="{{ __('Revalidate transaction status from gateway') }}"
                                   onclick="return confirm('{{ __('Are you sure you want to regenerate failed transactions?') }}');">
                                   <span class="dash-micon"><i class="ti ti-refresh"></i></span> 
                                   {{ __('Regenerate Failed Transactions') }}
                                </a>
                            @endif
                            </p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- PaySlip Info -->
<div class="col-md-3">
    <div class="p-3 bg-light rounded">
        <h6 class="mb-1 text-muted">Batch ID</h6>
        <p class="text-dark font-weight-bold mb-3">{{ $payslip->batch_id }}</p>

        @if(!empty($payslip->batch_reference))
            <h6 class="mb-1 text-muted">Batch Reference</h6>
            <p class="text-dark font-weight-bold mb-3">{{ $payslip->batch_reference }}</p>
            <p>
                <a href="{{ route('purchase-requisitions.bulktransfer.revalidate', \Crypt::encrypt($payslip->batch_reference)) }}" 
                   class="btn btn-sm btn-info" 
                   data-bs-toggle="tooltip" 
                   title="{{ __('Revalidate transaction status from gateway') }}">
                    <span class="dash-micon"><i class="ti ti-refresh"></i></span> 
                    {{ __('Revalidate') }}
                </a>
            </p>
        @endif

        {{-- Parent Batch Section --}}
        @if($payslip->parent_batch_id)
            @php
                $parentBatch = \App\Models\PaySlipHrBatch::find($payslip->parent_batch_id);
            @endphp

            <hr> {{-- Divider for clarity --}}

            <h6 class="mb-1 text-muted">Parent Batch ID</h6>
            <p class="text-dark font-weight-bold mb-3">{{ $parentBatch->batch_id ?? 'N/A' }}</p>

            @if(!empty($parentBatch->batch_reference))
                <h6 class="mb-1 text-muted">Parent Batch Reference</h6>
                <p class="text-dark font-weight-bold mb-3">{{ $parentBatch->batch_reference }}</p>
            @endif
        @endif
    </div>
</div>

                <div class="col-md-3">
                    <div class="p-3 bg-light rounded">
                        <h6 class="mb-1 text-muted">Status</h6>
                        <?php
                            // Define status colors
                            $statusClasses = [
                                0 => 'bg-secondary', // For status 0
                                1 => 'bg-warning',   // For status 1
                                2 => 'bg-danger',    // For status 2
                                3 => 'bg-info',      // For status 3
                                4 => 'bg-primary',   // For status 4
                                5 => 'bg-danger',    // For status 5
                            ];
                            
                            // Determine the appropriate class for the current status
                            $statusClass = $statusClasses[$payslip->status] ?? 'bg-secondary'; // Default to 'bg-secondary'
                            
                            // Get the status label
                            $statusLabel = \App\Models\PaySlipHrBatch::$statues[$payslip->status] ?? 'Unknown Status';
                            ?>
                            <p class="text-dark mb-3">
                                <span class="status_badge badge <?= $statusClass; ?> p-2 px-3 rounded">
                                    <?= __($statusLabel); ?>
                                </span>
                            </p>

                        <h6 class="mb-1 text-muted">Created At</h6>
                        <p class="text-dark font-weight-bold">{{\Auth::user()->dateFormat($payslip->created_at)}} </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</div>
<div class="container mt-5">
<div class="card em-card ">
        <div class="card-header">
            <h5>{{ __('Pay Slip Items') }}</h5>
        </div>
        <div class="card-body">
    <div class="table-responsive">
        <table class="table datatable" id="pc-dt-render-column-cells">
            <thead>
                <tr>
                    <th>{{ __('Title') }}</th>
                    <th>{{ __('Service Provider') }}</th>
               
                    <th>{{ __('TXN Reference') }}</th>
                    <th>{{ __('TXN Status') }}</th>
                    
                    <th>{{ __('TXN Description') }}</th>
                    <th>{{ __('Approved Amount') }}</th>
                    <th>{{ __('Paying Amount') }}</th>
                    <th>{{ __('Balance') }}</th>
                    <th>{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payslipItems as $payslipItem)
                    <tr>
                        <td class="reference">
                            <p class="text-muted">{{ $payslipItem->title }}</p>
                        </td>
                       
                        <td>
                             @if($payslipItem->serviceProvider)
                               <p class="text-dark font-weight-bold mb-0"> {{ $payslipItem->serviceProvider->name }}</p>
                               <small class="text-muted d-block">{{ $payslipItem->serviceProvider->bank_name }}</small>
                               <small class="text-muted">{{ $payslipItem->serviceProvider->bank_account }}</small>
                            @else
                                <p class="text-muted">-</p>
                            @endif
                        </td>

                        <td class="reference">
                            <p class="text-muted">{{ $payslipItem->txn_ref }}</p>
                        </td>
                        <td>
                            @if ($payslipItem->txn_status === 'SUCCESS')
                                <span class="status_badge badge bg-primary p-2 px-3 rounded">
                                    {{ __('Success') }}
                                </span>
                            @elseif ($payslipItem->txn_status === 'FAILED')
                                <span class="status_badge badge bg-danger p-2 px-3 rounded">
                                    {{ __('Failed') }}
                                </span>
                            @elseif ($payslipItem->txn_status === 'PENDING')
                                <span class="status_badge badge bg-warning p-2 px-3 rounded">
                                    {{ __('Pending') }}
                                </span>
                            @else
                                @if($payslipItem->txn_status)
                                <span class="status_badge badge bg-secondary p-2 px-3 rounded">
                                    {{ __($payslipItem->txn_status) }}
                                </span>
                                @endif
                            @endif
                           
                        </td>
                        <td class="reference">
                            <p class="text-muted">{{ $payslipItem->txn_description }}</p>
                        </td>
                        <td>
                            <p class="text-dark mt-3">
                                ₦{{ number_format($payslipItem->totalApprovedCost(), 2) }}</p>
                        </td>
                        <td>
                             <p class="text-success mt-3">
                                ₦{{ number_format($payslipItem->pivot->amount, 2) }}</p>
                        </td>
                        <td>
                             <p class="text-danger mt-3">
                                @php
                                    $totalPaidAcrossAllBatches = $payslipItem->paymentBatches->sum('pivot.amount');
                                    $remainingBalance = $payslipItem->totalApprovedCost() - $totalPaidAcrossAllBatches;
                                    if($remainingBalance < 0.01) $remainingBalance = 0;
                                @endphp
                                ₦{{ number_format($remainingBalance, 2) }}</p>
                        </td>
                        <td class="Action">
                             <div class="action-btns d-flex">
                            <!-- Show Button -->
                                <a href="{{ route('purchase-requisitions.show', \Crypt::encrypt($payslipItem->id)) }}" class="btn btn-sm bg-primary me-2" data-bs-toggle="tooltip" title="{{ __('View') }}">
                                    <i class="ti ti-eye text-white"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    </div>
        </div>
    
</div>

    
<!-- Modal -->
<div class="modal fade" id="otpModal" tabindex="-1" aria-labelledby="otpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="otpModalLabel">Authorize Bulk Transfer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="otpForm" method="POST" action="{{route('payslip.authorisepayment')}}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="otp" class="form-label">Enter 6-Digit OTP</label>
                        <input type="text" class="form-control" id="otp" name="otp" maxlength="6" pattern="\d{6}" 
                            required placeholder="Enter OTP">
                        <input type="hidden" class="form-control" id="payslipBatchId" name="payslipBatchId" value="{{\Crypt::encrypt($payslip->id)}}">
                        <div class="form-text">Check your registered phone or email for the OTP.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Authorize</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('script-page')
<script>
$(document).ready(function () {
    $('#otpForm').on('submit', function (e) {
        e.preventDefault(); // Prevent default form submission

        const otp = $('#otp').val(); // Get OTP value
        const payslipBatchId = $('#payslipBatchId').val(); // Get Payslip Batch ID value

        $.ajax({
            url: '{{ route('purchase-requisitions.authorisepayment') }}',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                otp: otp,
                payslip_batch_id: payslipBatchId
            },
            success: function (response) {
                // if (response.success) {
                //     alert('Payment authorized successfully!');
                //   location.reload(); // Reload the page
                // } else {
                //     alert('Error: ' + response.message);
                // }
                console.log(response);
                
                alert(response)
            },
            error: function (xhr, status, error) {
                console.error('Error:', error);
                const errorMessage = xhr.responseJSON?.message || 'An unexpected error occurred.';
                alert('Error: ' + errorMessage);
            }
        });
    });
    
    $('#resend-token').on('click' , function() {
        var reference = $(this).data('ref');
         $.ajax({
            url: '{{ route('purchase-requisitions.resendtoken') }}',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                reference: reference,
            },
            success: function (response) {
                if (response.success) {
                   alert(response.data.message)
                } else {
                    alert('Error1: ' + response.details.responseMessage);
                }
                console.log(response);
            },
            error: function (xhr, status, error) {
                console.error('Error:', error);
                const errorMessage = xhr.responseJSON?.message || 'An unexpected error occurred.';
                //alert('Error: ' + errorMessage);
                if(errorMessage == 'Transaction batch not awaiting authorization'){
                    let ans = prompt('Transaction has expired. Would you like to reinitialise')
                    // Check if the user pressed Cancel or entered nothing
                    if (ans === null || ans.trim() === "") {
                        alert("No answer entered. Defaulting to 'No'.");
                        ans = "no";
                    } else {
                        // Normalize the input for consistent comparison
                        ans = ans.trim().toLowerCase();
                    }
                    
                    if(ans == 'yes'){
                        //renitialise
                        alert('Transaction is reinitialising. check your email for OTP');
                         window.location.href = "{{route('purchase-requisitions.initialisepayment', \Crypt::encrypt($payslip->id))}}";
                    }else if (ans == 'no'){
                        console.log('termiated');
                    }else{
                        alert('Unrecognise response, response with Yes or No only')
                    } 
                    
                }else{
                    alert('Error2: ' + errorMessage);
                }
            }
        });
        
    });
});


</script>
@endpush

