@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Purchase Requisitions') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Purchase Requisitions') }}</li>
@endsection

@section('action-btn')
    <div class="float-end d-flex">
        <a href="{{ route('purchase-requisitions.create') }}" class="btn btn-sm btn-primary me-2">
            <i class="ti ti-plus"></i>
        </a>
    </div>
@endsection

@section('content')

{{-- ========================== ADVANCED FILTER FORM ============================= --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('purchase-requisitions.report') }}">
            <div class="row mb-2">

                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="">-- All --</option>
                        <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                        <option value="approved" {{ request('status')=='approved'?'selected':'' }}>Approved</option>
                        <option value="rejected" {{ request('status')=='rejected'?'selected':'' }}>Rejected</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-control">
                        <option value="">-- All --</option>
                        <option value="High" {{ request('priority')=='High'?'selected':'' }}>High</option>
                        <option value="Medium" {{ request('priority')=='Medium'?'selected':'' }}>Medium</option>
                        <option value="Low" {{ request('priority')=='Low'?'selected':'' }}>Low</option>
                    </select>
                </div>

            </div>

            <div class="row mb-2">
                <div class="col-md-3">
                    <label class="form-label">Approval Stage</label>
                    <select name="stage" class="form-control">
                        <option value="">-- All --</option>
                        <option value="1">HOD</option>
                        <option value="2">Internal Audit</option>
                        <option value="3">Accounts</option>
                        <option value="4">MD</option>
                        <option value="5">Final Approval</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Payment Status</label>
                    <select name="payment_status" class="form-control">
                        <option value="">-- All --</option>
                        <option value="2" {{ request('payment_status')=='2'?'selected':'' }}>Unpaid</option>
                        <option value="3" {{ request('payment_status')=='3'?'selected':'' }}>Partially Paid</option>
                        <option value="4" {{ request('payment_status')=='4'?'selected':'' }}>Paid</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Department</label>
                    <select name="department_id" class="form-control">
                        <option value="">-- All --</option>
                        @foreach(App\Models\Department::all() as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id')==$dept->id?'selected':'' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary me-2">Filter</button>
                    <a href="{{ route('purchase-requisitions.report') }}" class="btn btn-secondary">Reset</a>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- ========================== STATS CARDS ============================= --}}
<div class="row mb-3">

    <div class="col">
        <div class="card p-3 text-white bg-primary text-center">
            <h5>Total Requisitions</h5>
            <h2>{{ $stats['total'] }}</h2>
        </div>
    </div>

    <div class="col">
        <div class="card p-3 text-white bg-warning text-center">
            <h5>Pending</h5>
            <h2>{{ $stats['pending'] }}</h2>
        </div>
    </div>

    <div class="col">
        <div class="card p-3 text-white bg-success text-center">
            <h5>Approved</h5>
            <h2>{{ $stats['approved'] }}</h2>
        </div>
    </div>

    <div class="col">
        <div class="card p-3 text-white bg-info text-center">
            <h5>Partially Paid</h5>
            <h2>{{ $stats['partial'] }}</h2>
        </div>
    </div>

    <div class="col">
        <div class="card p-3 text-white bg-danger text-center">
            <h5>High Priority</h5>
            <h2>{{ $stats['high'] }}</h2>
        </div>
    </div>

</div>

{{-- ========================== PAID / UNPAID SUM ============================= --}}
<div class="row mb-3">
    
    <div class="col-md-6">
        <div class="card p-3 bg-success text-white text-center">
            <h5>Total Paid Amount (₦)</h5>
            <h2>{{ number_format($paidTotal, 2) }}</h2>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card p-3 bg-danger text-white text-center">
            <h5>Total Unpaid Amount (₦)</h5>
            <h2>{{ number_format($unpaidTotal, 2) }}</h2>
        </div>
    </div>

</div>

{{-- ========================== GRAPHS ============================= --}}
<div class="row mb-4">

    <div class="col-md-6">
        <div class="card p-3">
            <h5 class="text-center">Priority Distribution</h5>
            <div id="priority_chart"></div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card p-3">
            <h5 class="text-center">Approval Stage Distribution</h5>
            <div id="stage_chart"></div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card p-3">
            <h5 class="text-center">Monthly Requisitions</h5>
            <div id="monthly_chart"></div>
        </div>
    </div>

</div>

{{-- ========================== TABLE ============================= --}}
<div class="card">
    <div class="card-body table-border-style">
        <div class="table-responsive">
            <table class="table datatable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Requisition ID</th>
                        <th>Title</th>
                        <th>Department</th>
                        <th>Approval Stage</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Paid</th>
                        <th>Created At</th>
                        <th>Action</th>
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
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td class="Id">
                                <a href="{{ route('purchase-requisitions.show', \Crypt::encrypt($requisition->id)) }}" 
                                   class="btn btn-outline-primary">
                                   {{ $requisition->pr_id }}
                                </a>
                            </td>

                            <td>{{ $requisition->title }}</td>
                            <td>{{ $requisition->PrDepartment->name }}</td>

                            <td>{{ $stages[$requisition->current_stage_level] ?? 'Completed' }}</td>

                            <td>
                                <span class="badge 
                                    @if($requisition->priority == 'High') bg-danger 
                                    @elseif($requisition->priority == 'Medium') bg-warning 
                                    @else bg-success @endif">
                                    {{ ucfirst($requisition->priority) }}
                                </span>
                            </td>

                            <td>
                                <span class="badge bg-{{ $requisition->status == 'approved' ? 'success' : 'warning' }}">
                                    {{ ucfirst($requisition->status) }}
                                </span>
                            </td>
                             <td>
                                @if($requisition->payment_status == 4)
                                    <span class="badge bg-success">Paid</span>
                                @elseif($requisition->payment_status == 3)
                                    <span class="badge bg-info">Partially Paid</span>
                                @else
                                    <span class="badge bg-warning">Unpaid</span>
                                @endif
                            </td>
                            <td>{{ Auth::user()->dateFormat($requisition->created_at) }}</td>

                            <td>
                                <div class="action-btns d-flex">
                                    <a href="{{ route('purchase-requisitions.show', \Crypt::encrypt($requisition->id)) }}" 
                                       class="btn btn-sm bg-primary me-2" data-bs-toggle="tooltip" title="View">
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

@endsection

@push('script-page')


<script>
window.addEventListener("load", function() {

    // Delay to allow Bootstrap/Admin template to finish layout rendering
    setTimeout(function() {

        // ============================
        // SAFE PHP → JS DATA
        // ============================
        const priorityData = @json($priorityData ?? ['High'=>0,'Medium'=>0,'Low'=>0]);
        const stageStats   = @json($stageStats ?? []);
        const monthlyData  = @json($monthlyDataFull ?? array_fill(0, 12, 0));

        console.log("PRIORITY:", priorityData);
        console.log("STAGE:", stageStats);
        console.log("MONTHLY:", monthlyData);

        // ============================
        // HELPER: Check element & height
        // ============================
        function safeChart(selector, options) {
            const el = document.querySelector(selector);
            if (!el) {
                console.warn(selector, "NOT found");
                return;
            }

            // Force height so ApexCharts won't crash
            if (el.offsetHeight < 50) {
                el.style.minHeight = "360px";
            }

            console.log(selector, "height =", el.offsetHeight);

            const chart = new ApexCharts(el, options);
            chart.render();
        }

        // ============================
        // PRIORITY PIE CHART
        // ============================
        safeChart("#priority_chart", {
            chart: { type: 'pie', height: 260 },
            series: [
                Number(priorityData.High ?? 0),
                Number(priorityData.Medium ?? 0),
                Number(priorityData.Low ?? 0)
            ],
            labels: ['High', 'Medium', 'Low'],
            colors: [
                '#dc3545', // High → red
                '#ffc107', // Medium → yellow
                '#198754'  // Low → green
            ],
        });

        // ============================
        // STAGE BAR CHART
        // ============================
        safeChart("#stage_chart", {
            chart: { type: 'bar', height: 260 },
            series: [{
                name: 'Requisitions',
                data: Object.values(stageStats).map(v => Number(v))
            }],
            xaxis: {
                categories: Object.keys(stageStats)
            }
        });

        // ============================
        // MONTHLY LINE CHART
        // ============================
        safeChart("#monthly_chart", {
            chart: { type: 'line', height: 260 },
            series: [{
                name: "Requisitions",
                data: monthlyData.map(v => Number(v))
            }],
            xaxis: {
                categories: [
                    'Jan','Feb','Mar','Apr','May','Jun',
                    'Jul','Aug','Sep','Oct','Nov','Dec'
                ]
            }
        });

    }, 300); // ← delay to avoid zero-height container issue

});
</script>
@endpush


