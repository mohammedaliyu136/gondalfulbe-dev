@extends('layouts.admin')

@php
$profile = asset(Storage::url('uploads/avatar/'));
@endphp

@section('page-title')
    {{ __('Manage Purchase Requisitions') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Purchase Requisitions') }}</li>
@endsection

@section('action-btn')
    <div class="float-end d-flex">
        <a href="{{ route('purchase-requisitions.create') }}" class="btn btn-sm btn-primary me-2" data-bs-toggle="tooltip" title="{{ __('Create Purchase Requisition') }}">
            <i class="ti ti-plus"></i>
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Requisition ID') }}</th>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Department') }}</th>
                                    @if (Gate::check('view all requisition'))
                                    <th>{{ __('Approval Stage') }}</th>
                                    @endif
                                    <th>{{ __('Priority') }}</th>
                                    @if (Gate::check('view all requisition'))
                                    <th>{{ __('Status') }}</th>
                                    @endif
                                    <th>{{ __('Created At') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                           <tbody>
    @php
        $stages = [
            1 => 'HOD',
            2 => 'Internal Audit',
            3 => 'Accounts',
            4 => 'MD',
            5 => 'Final Approval'
        ];
    @endphp

    @foreach ($requisitions as $k => $requisition)
        @php
            $stageName = $stages[$requisition->current_stage_level] ?? 'Approvals Completed';
        @endphp

        <tr>
            <td>{{ $loop->iteration }}</td>
            <td class="Id">
                <a href="{{ route('purchase-requisitions.show', \Crypt::encrypt($requisition->id)) }}" class="btn btn-outline-primary">{{ $requisition->pr_id }}</a>
            </td>
            <td>{{ $requisition->title }}</td>
            <td>{{ $requisition->PrDepartment->name }}</td>
            @if (Gate::check('view all requisition'))
            <td>{{ $stageName }}</td>
            @endif
            <td>
                <span class="badge 
                                @if($requisition->priority == 'High') bg-danger 
                                @elseif($requisition->priority == 'Medium') bg-warning 
                                @else bg-success 
                                @endif">
                                {{ ucfirst($requisition->priority) }}
                            </span>
            </td>
            @if (Gate::check('view all requisition'))
            <td>
                <span class="badge bg-{{ $requisition->status == 'approved' ? 'success' : 'warning' }}">
                    {{ ucfirst($requisition->status) }}
                </span>
            </td>
            @endif
            <td>{{ Auth::user()->dateFormat($requisition->created_at) }}</td>
            <td>
                <div class="action-btns d-flex">
                    <!-- Show Button -->
                    <a href="{{ route('purchase-requisitions.show', \Crypt::encrypt($requisition->id)) }}" class="btn btn-sm bg-primary me-2" data-bs-toggle="tooltip" title="{{ __('View') }}">
                        <i class="ti ti-eye text-white"></i>
                    </a>

                    <!-- Edit Button -->
                    <!--<a href="{{ route('purchase-requisitions.edit', $requisition->id) }}" class="btn btn-sm bg-info me-2" data-bs-toggle="tooltip" title="{{ __('Edit') }}">-->
                    <!--    <i class="ti ti-pencil text-white"></i>-->
                    <!--</a>-->

                    <!-- Delete Button -->
                    <!--{!! Form::open(['method' => 'DELETE', 'route' => ['purchase-requisitions.destroy', $requisition->id], 'id' => 'delete-form-' . $requisition->id]) !!}-->
                    <!--    <a href="#" class="btn btn-sm bg-danger" data-bs-toggle="tooltip" title="{{ __('Delete') }}"-->
                    <!--       data-confirm="{{ __('Are you sure?') }}" data-confirm-yes="document.getElementById('delete-form-{{ $requisition->id }}').submit();">-->
                    <!--        <i class="ti ti-trash text-white"></i>-->
                    <!--    </a>-->
                    <!--{!! Form::close() !!}-->
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
    </div>
@endsection