@extends('layouts.admin')
@push('script-page')
@endpush
@section('page-title')
    {{__('Payslip-Detail-MC Agents')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('mcagent.index')}}">{{__('MC Agent')}}</a></li>
    <li class="breadcrumb-item">Payslip-{{$payslip['batch_id']}}</li>

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
            <h4 class="mb-0 text-white">Pay Slip Information</h4>
        </div>
        <div class="card-body">
            <div class="row mb-2">
                @if(\Auth::user()->can('approve payment mc officer'))
                    @if($payslip->status == 0)
                    <div class="col-auto float-end mt-4">
                        <!--<a href="{{route('payslipitem.farmer.approvepayment', \Crypt::encrypt($payslip->id))}}" class="btn btn-primary"-->
                        <!--    data-bs-toggle="tooltip" title="{{ __('approve ') }}"-->
                        <!--    data-original-title="{{ __('payslip') }}">{{ __('Approve payments') }}-->
                        <!--</a> -->
                        <a href="{{ route('mcagent.payslips.approvepayment', \Crypt::encrypt($payslip->id)) }}" 
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
                @if(\Auth::user()->can('initialise payment mc officer'))
                     @if($payslip->status == 1)
                        <div class="col-auto float-end mt-4">
                            <a href="{{route('mcagent.payslips.initialisepayment', \Crypt::encrypt($payslip->id))}}" class="btn btn-info"
                                data-bs-toggle="tooltip" title="{{ __('pay now') }}"
                                data-original-title="{{ __('payslip') }}">{{ __('Pay now') }}
                            </a> 
                        </div>
                    @endif
                @endif
                @if(\Auth::user()->can('resend token mc officer'))
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
                            <h6 class="mb-1 text-muted">Total Amount</h6>
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
                            <h6 class="mb-1 text-muted">Reversed Transaction Count </h6>
                            <h4 class="mb-3" style='color:red'><?= $reversedCount; ?></h4>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- PaySlip Info -->
                <div class="col-md-3">
                    <div class="p-3 bg-light rounded">
                        <h6 class="mb-1 text-muted">Batch ID</h6>
                        <p class="text-dark font-weight-bold mb-3"><?= $payslip->batch_id; ?></p>
                        @if(!empty($payslip->batch_reference))
                        <h6 class="mb-1 text-muted">Batch Reference</h6>
                        <p class="text-dark font-weight-bold mb-3"><?= $payslip->batch_reference; ?></p>
                        <p>
                            @if(\Auth::user()->can('initialise payment mc officer'))
                            <a href="{{route('mcagent.payslips.revalidate', \Crypt::encrypt($payslip->batch_reference))}}" class="btn btn-sm btn-info"
                                data-bs-toggle="tooltip" title="{{ __('Revalidate transaction status from gateway') }}"
                                data-original-title="{{ __('payslip') }}"><span class="dash-micon"><i
                            class="ti ti-refresh"></i></span>{{ __('Revalidate') }}
                            </a> 
                            @endif
                        </p>
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
                            $statusLabel = \App\Models\PaySlipRiderBatch::$statues[$payslip->status] ?? 'Unknown Status';
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
    <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center justify-content-start mt-2">
                            <h5>{{ __('Recipients') }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        <div class="card-body">
    <div class="table-responsive">
        <table class="table datatable" id="pc-dt-render-column-cells">
            <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th>{{ __('System Status') }}</th>
                    <th>{{ __('Reference') }}</th>
                    <th>{{ __('TXN Status') }}</th>
                    <th>{{ __('TXN Description') }}</th>
                    
                    <th>{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payslipItems as $payslipItem)
                    <tr>
                        <td class="vender_name">
                            <a href="{{ route('mcagent.show', \Crypt::encrypt($payslipItem->agent_id)) }}" class="btn btn-outline-primary">
                                {{ $payslipItem->agent->name }} <!-- Access vendor's name -->
                            </a>
                        </td>
                        <td>
                            <p class="text-success mt-3">
                                ₦{{ number_format($payslipItem->amount, 2) }}</p>
                        </td>
                        <td>
                            @if ($payslipItem->status == 0)
                                <span class="status_badge badge bg-secondary p-2 px-3 rounded">
                                    {{ __(\App\Models\PaySlipFarmerBatch::$statues[$payslipItem->status]) }}
                                </span>
                            @elseif($payslipItem->status == 1)
                                <span class="status_badge badge bg-warning p-2 px-3 rounded">
                                    {{ __(\App\Models\PaySlipFarmerBatch::$statues[$payslipItem->status]) }}
                                </span>
                            @elseif($payslipItem->status == 2)
                                <span class="status_badge badge bg-danger p-2 px-3 rounded">
                                    {{ __(\App\Models\PaySlipFarmerBatch::$statues[$payslipItem->status]) }}
                                </span>
                            @elseif($payslipItem->status == 3)
                                <span class="status_badge badge bg-info p-2 px-3 rounded">
                                    {{ __(\App\Models\PaySlipFarmerBatch::$statues[$payslipItem->status]) }}
                                </span>
                            @elseif($payslipItem->status == 4)
                                <span class="status_badge badge bg-primary p-2 px-3 rounded">
                                    {{ __(\App\Models\PaySlipFarmerBatch::$statues[$payslipItem->status]) }}
                                </span>
                            @elseif($payslipItem->status == 6)
                                <span class="status_badge badge p-2 px-3 rounded" style="background-color: red !important; color: white !important;">
                                    {{ __(\App\Models\PaySlipFarmerBatch::$statues[$payslipItem->status]) }}
                                </span>

                            @endif
                        </td>
                        <td class="reference">
                            <p class="text-muted">{{ $payslipItem->reference }}</p>
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
                             @elseif ($payslipItem->txn_status === 'EXPIRED')
                                <span class="status_badge badge bg-warning p-2 px-3 rounded">
                                    {{ __('Expired') }}
                                </span>
                            @elseif ($payslipItem->txn_status === 'PENDING')
                                <span class="status_badge badge bg-warning p-2 px-3 rounded">
                                    {{ __('Pending') }}
                                </span>
                            @elseif ($payslipItem->txn_status === 'PENDING_BATCH_AUTHORIZATION')
                                <span class="status_badge badge bg-info bg-gradient p-2 px-3 rounded">
                                    {{ __('Pendin batch authorization') }}
                                </span>
                            @elseif ($payslipItem->txn_status === 'PROCESSING')
                                <span class="status_badge badge bg-info p-2 px-3 rounded">
                                    {{ __('Processing') }}
                                </span>
                            @elseif ($payslipItem->txn_status === 'REVERSED')
                                <span class="status_badge badge bg-danger p-2 px-3 rounded">
                                    {{ __('Reversed') }}
                                </span>
                            @else
                                <span class="status_badge badge bg-secondary p-2 px-3 rounded">
                                    {{ __('Unknown') }}
                                </span>
                            @endif
                        </td>

                        <td class="txn_description">
                            <p class="text-muted" style="word-wrap: break-word; overflow-wrap: break-word; white-space: normal;">
                                {{ $payslipItem->txn_description }}
                            </p>
                        </td>
                        <td class="Action">
                            @if($payslip->status === 0)
                                @can('generate bulk payment rider')
                                    <div class="action-btn me-2">
                                        <a href="#" class="mx-3 btn btn-sm align-items-center bg-info" data-size="lg"
                                            data-title="{{ __('Edit Payslip') }}"
                                            data-url="{{ route('mcagent.payslips.edit',  $payslipItem['id']) }}" 
                                            data-ajax-popup="true" title="{{ __('Edit') }}" 
                                            data-bs-toggle="tooltip">
                                            <i class="ti ti-pencil text-white"></i>  
                                        </a>
                                    </div>

                                        <div class="action-btn">
                                        {!! Form::open(['method' => 'DELETE', 'route' => ['mcagent.payslips.delete', $payslipItem['id']], 'id' => 'delete-form-' . $payslipItem['id']]) !!}
                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para bg-danger" data-bs-toggle="tooltip"
                                        data-original-title="{{ __('Delete') }}" title="{{ __('Delete') }}"
                                        data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                        data-confirm-yes="document.getElementById('delete-form-{{ $payslipItem['id'] }}').submit();">
                                        <i class="ti ti-trash text-white"></i>
                                        </a>
                                    {!! Form::close() !!} 
                                    </div>
                                @endcan
                            @endif
                        
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
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
            <form id="otpForm" method="POST" action="{{route('mcagent.payslips.authorisepayment')}}">
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
            url: '{{ route('mcagent.payslips.authorisepayment') }}',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                otp: otp,
                payslip_batch_id: payslipBatchId
            },
            success: function (response) {
                if (response.success) {
                    alert('Payment authorized successfully!');
                   location.reload(); // Reload the page
                } else {
                    alert('Error: ' + response.message);
                }
                console.log(response);
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
            url: '{{ route('mcagent.payslips.resendtoken') }}',
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
                         window.location.href = "{{route('mcagent.payslips.initialisepayment', \Crypt::encrypt($payslip->id))}}";
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