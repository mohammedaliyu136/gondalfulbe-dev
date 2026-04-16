@extends('layouts.admin')

@section('page-title')
    {{ __('Purchase Requisition Details') }}
@endsection
    @php
        $stages = [
            1 => 'Approval HOD',
            2 => 'Internal Audit',
            3 => 'Accounts',
            4 => 'MD',
            5 => 'Final Approval'
        ];

        $currentStage = $pr->current_stage_level ?? 1;
        $stageName = $stages[$currentStage] ?? 'Pending';
        $userCanApprove = $pr->approvals->where('stage_level', $currentStage)
                                        ->where('user_id', Auth::id())
                                        ->whereNull('status')
                                        ->isNotEmpty();
    @endphp
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('purchase-requisitions.index') }}">{{ __('Purchase Requisitions') }}</a></li>
    <li class="breadcrumb-item">{{ __('Approval ') . $stageName }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card em-card">
            <div class="card-header">
                <h5>{{ __('Requisition Details') }}</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <th>{{ __('Title') }}</th>
                        <td>{{ $pr->title }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Requested By') }}</th>
                        <td>{{ $pr->requested_by }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Priority') }}</th>
                        <td>
                            <span class="badge 
                                @if($pr->priority == 'High') bg-danger 
                                @elseif($pr->priority == 'Medium') bg-warning 
                                @else bg-success 
                                @endif">
                                {{ ucfirst($pr->priority) }}
                            </span>
                        </td>
                    </tr>
                                        <tr>
                        <th>{{ __('Department') }}</th>
                        <td>{{ $pr->PrDepartment->name }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Comment') }}</th>
                        <td>{{ $pr->comment }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Created By') }}</th>
                        <td>{{ optional(App\Models\User::find($pr->created_by))->name ?? 'Unknown' }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Status') }}</th>
                        <td>{{ ucfirst($pr->status) }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Purchase Items -->
        <div class="col-md-12">
        <div class="card em-card">
            <div class="card-header">
                <h5>{{ __('Requested Items') }}</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table">
                    <thead>
                       <tr>
                            <th>#</th>
                            <th>{{ __('Item Name') }}</th>
                            <th>{{ __('Quantity') }}</th>
                            <th>{{ __('Estimated Unit price') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th class="bg-success text-white">{{ __('Quantity') }}</th>
                            <th class="bg-success text-white">{{ __('Unit price') }}</th>
                            <th class="bg-success text-white">{{ __('Amount') }}</th>
                        </tr>

                    </thead>
                    <tbody>
                        @foreach ($pr->items as $key => $item)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->estimated_cost, 2) }}</td>
                                <td>{{ number_format($item->estimated_cost * $item->quantity, 2) }}</td>
                                <td>{{ $item->approved_quantity ?? '-' }}</td>
                                <td>{{ number_format($item->approved_cost, 2) }}</td>
                                <td>{{ number_format($item->approved_cost * $item->approved_quantity, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                        <tr>
                        <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                       <tr>
                            <td colspan="7" class="text-end"><strong>{{ __('Total Estimated Cost') }}:</strong></td>
                            <td><strong>₦{{ number_format($pr->totalEstimatedCost(), 2) }}</strong></td>
                        </tr>
                        <tr class="table-success"> <!-- Bootstrap class for highlighting -->
                            <td colspan="7" class="text-end"><strong>{{ __('Total Approved Cost') }}:</strong></td>
                            <td class="fw-bold text-success"><strong>₦{{ number_format($pr->totalApprovedCost(), 2) }}</strong></td> 
                        </tr>

                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <!--Service Provider-->
    <div class="col-md-12">
        <div class="card em-card">
            <div class="card-header">
                <h5>{{ __('Service Provider') }}</h5>
            </div>
            <div class="card-body">
                <table class="table">
 
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <td>{{ $pr->serviceProvider ? $pr->serviceProvider->name : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Contact') }}</th>
                        <td>{{ $pr->serviceProvider ? $pr->serviceProvider->contact : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Location') }}</th>
                        <td>{{ $pr->serviceProvider ? $pr->serviceProvider->billing_city : 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Approvals -->
    <div class="col-md-12">
        <div class="card em-card">
            <div class="card-header">
                <h5>{{ __('Approval Stages') }}</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('Phase') }}</th>
                            <th>{{ __('Approved By') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pr->approvals as $approval)
                            <tr>
                                <td>{{ $approval->stage_name }}</td>
                                <td>{{ optional(App\Models\User::find($approval->approved_by))->name ?? 'Pending' }}</td>
                                <td>
                                    @if ($approval->status == 'approved')
                                        <span class="badge bg-success">{{ __('Approved') }}</span>
                                    @elseif ($approval->status == 'rejected')
                                        <span class="badge bg-danger">{{ __('Rejected') }}</span>
                                    @else
                                        <span class="badge bg-warning">{{ __('Pending') }}</span>
                                    @endif
                                </td>
                                <td>{{ $approval->updated_at ? $approval->updated_at->format('d M, Y') : 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Approval / Rejection Actions -->



<!-- Approval Action Section -->
<div class="col-md-12">
    <div class="card em-card">
        <div class="card-header">
            <h5>{{ __('Approval Action') }}</h5>
        </div>
        <div class="card-body">
            <p><strong>Current Stage: </strong> {{ $stageName }}</p>
            @if ($stageName == 'Approval HOD')
             <div class="d-flex gap-2">
            <!-- Button to trigger Internal Audit Modal -->
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#hodApprovalModal">
                <i class="ti ti-check"></i> {{ __('Approve') }}
            </button>
        
            <!-- Normal approval/rejection buttons for other stages -->
            <form action="{{ route('purchase-requisitions.approve', ['id' => $pr->id]) }}" method="POST">
                @csrf
                <input type="hidden" name="status" id="approval-status">
        
                <button type="button" class="btn btn-danger" onclick="submitApproval('rejected')">
                    <i class="ti ti-x"></i> {{ __('Reject') }}
                </button>
            </form>
        </div>
            @elseif ($stageName == 'Internal Audit')
             <div class="d-flex gap-2">
            <!-- Button to trigger Internal Audit Modal -->
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#auditApprovalModal">
                <i class="ti ti-check"></i> {{ __('Approve') }}
            </button>
        
            <!-- Normal approval/rejection buttons for other stages -->
            <form action="{{ route('purchase-requisitions.approve', ['id' => $pr->id]) }}" method="POST">
                @csrf
                <input type="hidden" name="status" id="approval-status">
        
                <button type="button" class="btn btn-danger" onclick="submitApproval('rejected')">
                    <i class="ti ti-x"></i> {{ __('Reject') }}
                </button>
            </form>
        </div>

                
            @elseif ($stageName == 'Accounts')
                <!-- Button to trigger Accounts Approval Modal -->
                 <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#accountsApprovalModal">
                    <i class="ti ti-check"></i> {{ __('Approve & Assign Service Provider') }}
                </button>
                
                <form action="{{ route('purchase-requisitions.approve', ['id' => $pr->id]) }}" method="POST">
                @csrf
                <input type="hidden" name="status" id="approval-status">
        
                <button type="button" class="btn btn-danger" onclick="submitApproval('rejected')">
                    <i class="ti ti-x"></i> {{ __('Reject') }}
                </button>
            </form>
                </div>
            @else
                <!-- Normal approval/rejection buttons for other stages -->
                <form action="{{ route('purchase-requisitions.approve', ['id' => $pr->id]) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" id="approval-status">

                    <button type="button" class="btn btn-success me-2" onclick="submitApproval('approved')">
                        <i class="ti ti-check"></i> {{ __('Approve') }}
                    </button>

                    <button type="button" class="btn btn-danger" onclick="submitApproval('rejected')">
                        <i class="ti ti-x"></i> {{ __('Reject') }}
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

<!-- HOD Approval Modal -->
<div class="modal fade" id="hodApprovalModal" tabindex="-1" aria-labelledby="hodApprovalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="hodApprovalModalLabel">{{ __('Approve Purchase Requisition Items') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('purchase-requisitions.hod-approve', $pr->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <table class="table table-bordered table-striped text-center" style="table-layout: fixed; width: 100%;">
    <thead class="table-dark">
        <tr>
            <th class="w-25">{{ __('Item Name') }}</th> <!-- Wider column -->
            <th class="w-10">{{ __('Requested Qty') }}</th>
            <th class="w-15">{{ __('Estimated Cost') }}</th>
            <th class="w-20">{{ __('Status') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($pr->items as $item)
            <tr>
                <td>{{ $item->name }}</td>
                <td>
                    <input type="number" name="quantity[{{ $item->id }}]" class="form-control" value="{{ $item->quantity }}" required>
                </td>
                <td>
                    <input type="number" name="estimated_cost[{{ $item->id }}]" class="form-control" value="{{ $item->estimated_cost }}" required>
                </td>
                <td>
                    <select class="form-select" name="status[{{ $item->id }}]" id="status">
                        <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>{{ __('Accept') }}</option>
                        <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>{{ __('Remove') }}</option>
                    </select>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-success">{{ __('Approve') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Internal Audit Approval Modal -->
<div class="modal fade" id="auditApprovalModal" tabindex="-1" aria-labelledby="auditApprovalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="auditApprovalModalLabel">{{ __('Approve Purchase Requisition Items') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('purchase-requisitions.internal-audit-approve', $pr->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <table class="table table-bordered table-striped text-center" style="table-layout: fixed; width: 100%;">
    <thead class="table-dark">
        <tr>
            <th class="w-25">{{ __('Item Name') }}</th> <!-- Wider column -->
            <th class="w-10">{{ __('Requested Qty') }}</th>
            <th class="w-10">{{ __('Approved Qty') }}</th>
            <th class="w-15">{{ __('Estimated Cost') }}</th>
            <th class="w-15">{{ __('Approved Unit Cost') }}</th>
            <th class="w-20">{{ __('Status') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($pr->items as $item)
            <tr>
                <td>{{ $item->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>
                    <input type="number" name="approved_quantity[{{ $item->id }}]" class="form-control" value="{{ $item->quantity }}" required>
                </td>
                <td>{{ number_format($item->estimated_cost, 2) }}</td>
                <td>
                    <input type="number" name="approved_cost[{{ $item->id }}]" class="form-control" value="{{ $item->estimated_cost }}" required>
                </td>
                <td>
                    <select class="form-select" name="status[{{ $item->id }}]" id="status">
                        <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>{{ __('Accept') }}</option>
                        <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>{{ __('Remove') }}</option>
                    </select>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-success">{{ __('Approve') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Accounts Approval Modal -->
<div class="modal fade" id="accountsApprovalModal" tabindex="-1" aria-labelledby="accountsApprovalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="accountsApprovalModalLabel">{{ __('Accounts Approval & Service Provider Selection') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('purchase-requisitions.accounts-approve', $pr->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="service_provider_id" class="form-label">{{ __('Select Service Provider') }}</label><x-required></x-required>
                        <select name="service_provider_id" class="form-control" required>
                            <option value="">{{ __('Select Service Provider') }}</option>
                            @foreach($serviceProviders as $provider)
                                <option value="{{ $provider->id }}">
                                    {{ $provider->name }} - {{ $provider->bank_name }} ({{ $provider->bank_account }})
                                </option>
                            @endforeach
                        </select>


                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Approve & Assign') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function submitApproval(status) {
        if (confirm("Are you sure you want to " + status + " this purchase requisition?")) {
            document.getElementById('approval-status').value = status;
            event.target.closest("form").submit();
        }
    }
</script>


</div>
@endsection
