@extends('layouts.admin')

@php
    $profile = asset(Storage::url('uploads/avatar/'));
@endphp

@section('page-title')
    {{ __('Purchase Requisitions Approval (MD)') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
    </li>
    <li class="breadcrumb-item">{{ __('Purchase Requisitions') }}</li>
    <li class="breadcrumb-item">{{ __('MD Approval') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">

            <div class="card-body table-border-style">

                {{-- Tabs --}}
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active"
                                id="pending-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#pending"
                                type="button"
                                role="tab">
                            Pending ({{ $pendingRequisitions->count() }})
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link"
                                id="rejected-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#rejected"
                                type="button"
                                role="tab">
                            Rejected ({{ $rejectedRequisitions->count() }})
                        </button>
                    </li>
                </ul>

                {{-- Tab Contents --}}
                <div class="tab-content">

                    {{-- Pending Requisitions --}}
                    <div class="tab-pane fade show active" id="pending" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Requisition ID') }}</th>
                                        <th>{{ __('Title') }}</th>
                                        <th>{{ __('Requested By') }}</th>
                                        <th>{{ __('Department') }}</th>
                                        <th>{{ __('Approval Stage') }}</th>
                                        <th>{{ __('Status') }}</th>
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
                                    ];
                                @endphp

                                @forelse ($pendingRequisitions as $requisition)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>

                                        <td>
                                            <a href="{{ route('purchase-requisitions.show', Crypt::encrypt($requisition->id)) }}"
                                               class="btn btn-outline-primary btn-sm">
                                                {{ $requisition->pr_id }}
                                            </a>
                                        </td>

                                        <td>{{ $requisition->title }}</td>
                                        <td>{{ $requisition->requested_by }}</td>
                                        <td>{{ $requisition->PrDepartment->name ?? '-' }}</td>

                                        <td>
                                            {{ $stages[$requisition->current_stage_level] ?? 'Completed' }}
                                        </td>

                                        <td>
                                            <span class="badge bg-warning">
                                                {{ ucfirst($requisition->status) }}
                                            </span>
                                        </td>

                                        <td>
                                            {{ Auth::user()->dateFormat($requisition->created_at) }}
                                        </td>

                                        <td>
                                            <div class="action-btns d-flex">
                                                <a href="{{ route('purchase-requisitions.view', [
                                                    'id' => Crypt::encrypt($requisition->id),
                                                    'stage' => Crypt::encrypt($requisition->current_stage_level)
                                                ]) }}"
                                                   class="btn btn-sm bg-primary"
                                                   data-bs-toggle="tooltip"
                                                   title="{{ __('View') }}">
                                                    <i class="ti ti-eye text-white"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">
                                            No pending requisitions found.
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Rejected Requisitions --}}
                    <div class="tab-pane fade" id="rejected" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Requisition ID') }}</th>
                                        <th>{{ __('Title') }}</th>
                                        <th>{{ __('Requested By') }}</th>
                                        <th>{{ __('Department') }}</th>
                                        <th>{{ __('Approval Stage') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Created At') }}</th>
                                        <th>{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse ($rejectedRequisitions as $requisition)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>

                                        <td>
                                            <a href="{{ route('purchase-requisitions.show', Crypt::encrypt($requisition->id)) }}"
                                               class="btn btn-outline-danger btn-sm">
                                                {{ $requisition->pr_id }}
                                            </a>
                                        </td>

                                        <td>{{ $requisition->title }}</td>
                                        <td>{{ $requisition->requested_by }}</td>
                                        <td>{{ $requisition->PrDepartment->name ?? '-' }}</td>

                                        <td>
                                            {{ $stages[$requisition->current_stage_level] ?? 'Completed' }}
                                        </td>

                                        <td>
                                            <span class="badge bg-danger">
                                                {{ ucfirst($requisition->status) }}
                                            </span>
                                        </td>

                                        <td>
                                            {{ Auth::user()->dateFormat($requisition->created_at) }}
                                        </td>

                                        <td>
                                            <div class="action-btns d-flex">
                                                <a href="{{ route('purchase-requisitions.view', [
                                                    'id' => Crypt::encrypt($requisition->id),
                                                    'stage' => Crypt::encrypt($requisition->current_stage_level)
                                                ]) }}"
                                                   class="btn btn-sm bg-primary"
                                                   data-bs-toggle="tooltip"
                                                   title="{{ __('View') }}">
                                                    <i class="ti ti-eye text-white"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">
                                            No rejected requisitions found.
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
                {{-- End Tab Content --}}

            </div>
        </div>
    </div>
</div>
@endsection
