@extends('layouts.admin')

@section('page-title', 'Issued Stock Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Issued Stock Report') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <!-- Card Header -->
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ __('Issued Stock Report') }}</h5>
            </div>

            <!-- Card Body -->
            <div class="card-body">
                <!-- Filter Form -->
                <form action="{{ route('inventory-issues.report') }}" method="GET" class="row mb-3">
                    <div class="col-md-3">
                        <input type="text" name="item" class="form-control" placeholder="Filter by Item" value="{{ request('item') }}">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="issued_by" class="form-control" placeholder="Filter by Issued By" value="{{ request('issued_by') }}">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="issue_date" class="form-control" value="{{ request('issue_date') }}">
                    </div>
                    <div class="col-md-3 d-flex">
                        <button class="btn btn-primary me-2" type="submit">{{ __('Filter') }}</button>
                        <a href="{{ route('inventory-issues.report') }}" class="btn btn-secondary">{{ __('Clear') }}</a>
                    </div>
                </form>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>{{ __('Item') }}</th>
                                <th>{{ __('Quantity Issued') }}</th>
                                <th>{{ __('Issued To') }}</th>
                                <th>{{ __('Issued By') }}</th>
                                <th>{{ __('Issued At') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($issues as $issue)
                                <tr>
                                    <td>{{ $issue->inventory?->item_name ?? 'N/A' }}</td>
                                    <td>{{ $issue->quantity }}</td>
                                    <td>{{ $issue->issued_to }}</td>
                                    <td>{{ $issue->issued_by }}</td>
                                    <td>{{ $issue->issue_date?->format('Y-m-d') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">{{ __('No issues found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div> <!-- table responsive -->
            </div> <!-- card body -->
        </div> <!-- card -->
    </div>
</div>
@endsection
