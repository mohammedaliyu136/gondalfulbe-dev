@extends('layouts.admin')
@section('page-title', __('Agent Distribution'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Agent Distribution') }}</li>
@endsection

@section('content')
<div class="row">
    <!-- Allocate Stock -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">{{ __('Allocate Stock to Agent') }}</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('oss.agent.allocate') }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">{{ __('Agent') }} <span class="text-danger">*</span></label>
                        <select name="agent_id" class="form-select form-select-sm" required>
                            <option value="">{{ __('Select agent') }}</option>
                            @foreach($agents as $a)
                            <option value="{{ $a->id }}">{{ $a->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ __('Product') }} <span class="text-danger">*</span></label>
                        <select name="product_id" class="form-select form-select-sm" required>
                            <option value="">{{ __('Select product') }}</option>
                            @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} ({{ number_format($p->current_stock,2) }} {{ $p->unit }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ __('Quantity') }} <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="quantity_allocated" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ __('Date') }} <span class="text-danger">*</span></label>
                        <input type="date" name="allocated_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ __('Center') }}</label>
                        <input type="text" name="center" class="form-control form-control-sm">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ __('Notes') }}</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                    <button class="btn btn-success btn-sm w-100">{{ __('Allocate') }}</button>
                </form>
            </div>
        </div>

        <!-- Record Return -->
        <div class="card mt-3">
            <div class="card-header"><h6 class="mb-0">{{ __('Record Agent Return') }}</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('oss.agent.return') }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">{{ __('Agent') }} <span class="text-danger">*</span></label>
                        <select name="agent_id" class="form-select form-select-sm" required>
                            <option value="">{{ __('Select agent') }}</option>
                            @foreach($agents as $a)
                            <option value="{{ $a->id }}">{{ $a->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ __('Product') }} <span class="text-danger">*</span></label>
                        <select name="product_id" class="form-select form-select-sm" required>
                            <option value="">{{ __('Select product') }}</option>
                            @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ __('Qty Returned') }} <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="quantity_returned" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ __('Return Date') }} <span class="text-danger">*</span></label>
                        <input type="date" name="return_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ __('Reason') }}</label>
                        <textarea name="reason" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                    <button class="btn btn-warning btn-sm w-100">{{ __('Record Return') }}</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Allocation History -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">{{ __('Allocation History') }}</h6>
                <a href="{{ route('oss.agent.sale') }}" class="btn btn-primary btn-sm">
                    <i class="ti ti-shopping-cart"></i> {{ __('Record Field Sale') }}
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ __('Alloc ID') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Agent') }}</th>
                                <th>{{ __('Product') }}</th>
                                <th>{{ __('Allocated') }}</th>
                                <th>{{ __('Balance') }}</th>
                                <th>{{ __('Center') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allocations as $alloc)
                            @php $key = $alloc->agent_id . '_' . $alloc->product_id; @endphp
                            <tr>
                                <td><code>{{ $alloc->allocation_id }}</code></td>
                                <td>{{ \Carbon\Carbon::parse($alloc->allocated_date)->format('d M Y') }}</td>
                                <td>{{ $alloc->agent->name ?? '—' }}</td>
                                <td>{{ $alloc->product->name ?? '—' }}</td>
                                <td>{{ number_format($alloc->quantity_allocated, 2) }} {{ $alloc->product->unit ?? '' }}</td>
                                <td>
                                    <span class="{{ ($balances[$key] ?? 0) > 0 ? 'text-success' : 'text-muted' }}">
                                        {{ number_format($balances[$key] ?? 0, 2) }}
                                    </span>
                                </td>
                                <td>{{ $alloc->center ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center py-4 text-muted">{{ __('No allocations yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
