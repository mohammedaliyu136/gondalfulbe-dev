@extends('layouts.admin')

@section('page-title')
    {{ __('Purchase Requisition Details') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('purchase-requisitions.index') }}">{{ __('Purchase Requisitions') }}</a></li>
    <li class="breadcrumb-item">{{ __('View Purchase Requisition') }}</li>
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
                    @if($pr->serviceProvider)
                    <tr>
                        <th>{{ __('Service Provider') }}</th>
                        <td>
                            <span class="d-block fw-bold">{{ $pr->serviceProvider->name }}</span>
                            <small class="text-muted">{{ $pr->serviceProvider->bank_name }} ({{ $pr->serviceProvider->bank_account }})</small>
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <th>{{ __('Comment') }}</th>
                        <td>{{ $pr->comment }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Created By') }}</th>
                        <td>{{ optional(App\Models\User::find($pr->created_by))->name ?? 'Unknown' }}</td>
                    </tr>
                    @if (Gate::check('view all requisition'))
                    <tr>
                        <th>{{ __('Status') }}</th>
                        <td>{{ ucfirst($pr->status) }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Payment Status') }}</th>
                        <td>{{ $pr->getPaymentStatusLabelAttribute() }}</td>
                    </tr>
                    @endif
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

    @if (Gate::check('view all requisition'))
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
    @endif
</div>
@endsection
