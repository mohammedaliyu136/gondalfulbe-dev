@extends('layouts.admin')

@section('page-title')
    {{ __('Payslip') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('payslip') }}</li>
@endsection

@section('content')
<ul class="nav nav-tabs" id="tabMenu" role="tablist">
    <li class="nav-item" role="presentation">
        <button 
            class="nav-link active" 
            id="payslip-tab" 
            data-bs-toggle="tab" 
            data-bs-target="#payslip" 
            type="button" 
            role="tab" 
            aria-controls="payments" 
            aria-selected="false">
            Payslips
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button 
            class="nav-link" 
            id="disbursements-tab" 
            data-bs-toggle="tab" 
            data-bs-target="#disbursements" 
            type="button" 
            role="tab" 
            aria-controls="disbursements" 
            aria-selected="true">
            Disbursement
        </button>
    </li>
</ul>
<div class="tab-content mt-3" id="tabContent">
    <div class="tab-pane fade show active" id="payslip" role="tabpanel" aria-labelledby="payslip-tab">
        <div class="rows">
        <div class="col-sm-12 col-lg-12 col-xl-12 col-md-12 mt-4">
        <div class="card">
            <div class="card-body">
                {{ Form::open(['route' => ['payslip.store'], 'method' => 'POST', 'id' => 'payslip_form']) }}
                <div class="d-flex align-items-center justify-content-end gap-2">
                    <div class="col-xl-2 col-lg-3 col-md-3">
                        <div class="btn-box">
                            {{ Form::label('month', __('Select Month'), ['class' => 'form-label']) }}
                            {{ Form::select('month', $month, date('m'), ['class' => 'form-control select', 'id' => 'month']) }}
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-3">
                        <div class="btn-box">
                            {{ Form::label('year', __('Select Year'), ['class' => 'form-label']) }}
                            {{ Form::select('year', $year, date('Y'), ['class' => 'form-control select']) }}
                        </div>
                    </div>
                    <div class="col-auto float-end  mt-4">
                        <a href="#" class="btn  btn-primary"
                            onclick="document.getElementById('payslip_form').submit(); return false;"
                            data-bs-toggle="tooltip" title="{{ __('payslip') }}"
                            data-original-title="{{ __('payslip') }}">{{ __('Generate Payslip') }}
                        </a>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center justify-content-start mt-2">
                            <h5>{{ __('Find Employee Payslip') }}</h5>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex align-items-center justify-content-end gap-2 ">
                            <div class="col-xl-2 col-lg-3 col-md-4">
                                <div class="btn-box">
                                    <select class="form-control month_date " name="year" tabindex="-1"
                                        aria-hidden="true">
                                        <option value="--">--</option>
                                        @foreach ($month as $k => $mon)
                                            @php
                                                $selected = date('m') == $k ? 'selected' : '';
                                            @endphp
                                            <option value="{{ $k }}" {{ $selected }}>{{ $mon }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-4">
                                <div class="btn-box">
                                    {{ Form::select('year', $year, date('Y'), ['class' => 'form-control year_date ']) }}
                                </div>
                            </div>
                            <div class="col-auto float-end">
                                {{ Form::open(['route' => ['payslip.export'], 'method' => 'POST', 'id' => 'payslip_form']) }}
                                <input type="hidden" name="filter_month" class="filter_month">
                                <input type="hidden" name="filter_year" class="filter_year">
                                <input type="submit" value="{{ __('Export') }}" class="btn btn-primary">
                                {{ Form::close() }}
                            </div>
                            <div class="col-auto float-end me-0">
                                @can('create pay slip')
                                    <input type="button" value="{{ __('Bulk Payment') }}" class="btn btn-primary"
                                        id="bulk_payment">
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-render-column-cells">
                        <thead>
                            <tr>
                                <th>{{ __('Employee Id') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Payroll Type') }}</th>
                                <th>{{ __('Salary') }}</th>
                                <th>{{ __('Net Salary') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
    </div>
    
    <div class="tab-pane fade" id="disbursements" role="tabpanel" aria-labelledby="disbursements-tab">
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
                                    <a href="{{ route('payslip.showpayslip', \Crypt::encrypt($payslip_batch->id)) }}" 
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
                                        <a href="{{ route('payslip.showpayslip', \Crypt::encrypt($payslip_batch->id)) }}"
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
</div>


@endsection

@push('script-page')
    <script>
        $(document).ready(function() {
            callback();

            function callback() {
                var month = $(".month_date").val();
                var year = $(".year_date").val();

                $('.filter_month').val(month);
                $('.filter_year').val(year);

                if (month == '') {
                    month = '{{ date('m', strtotime('last month')) }}';
                    year = '{{ date('Y') }}';

                    $('.filter_month').val(month);
                    $('.filter_year').val(year);
                }

                var datePicker = year + '-' + month;

                $.ajax({
                    url: '{{ route('payslip.search_json') }}',
                    type: 'POST',
                    data: {
                        "datePicker": datePicker,
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function(data) {

                        function renderstatus(data, cell, row) {
                            if (data == 'Paid')
                                return '<div class="badge bg-success p-2 px-3 rounded"><a href="#" class="text-white">' +
                                    data + '</a></div>';
                            else
                                return '<div class="badge bg-danger p-2 px-3 rounded"><a href="#" class="text-white">' +
                                    data + '</a></div>';
                        }

                        function renderButton(data, cell, row) {

                            var $div = $(row);
                            employee_id = $div.find('td:eq(0)').text();
                            status = $div.find('td:eq(6)').text();

                            var month = $(".month_date").val();
                            var year = $(".year_date").val();
                            var id = employee_id;
                            var payslip_id = data;

                            var clickToPaid = '';
                            var payslip = '';
                            var view = '';
                            var edit = '';
                            var deleted = '';
                            var form = '';

                            if (data != 0) {
                                var payslip =
                                    '<a href="#" data-url="{{ url('payslip/pdf/') }}/' + id +
                                    '/' + datePicker +
                                    '" data-size="md-pdf"  data-ajax-popup="true" class="btn btn-primary" data-title="{{ __('Employee Payslip') }}">' +
                                    '{{ __('Payslip') }}' + '</a> ';
                            }

                            // if (status == "UnPaid" && data != 0) {
                            //     clickToPaid = '<a href="{{ url('payslip/paysalary/') }}/' + id +
                            //         '/' + datePicker + '"  class="view-btn primary-bg btn-sm">' +
                            //         '{{ __('Click To Paid') }}' + '</a>  ';
                            // } 

                            if (data != 0) {
                                view =
                                    '<a href="#" data-url="{{ url('payslip/showemployee/') }}/' +
                                    payslip_id +
                                    '"  data-ajax-popup="true" class="view-btn gray-bg" data-title="{{ __('View Employee Detail') }}">' +
                                    '{{ __('View') }}' + '</a>';
                            }

                            if (data != 0 && status == "UnPaid") {
                                edit =
                                    '<a href="#" data-url="{{ url('payslip/editemployee/') }}/' +
                                    payslip_id +
                                    '"  data-ajax-popup="true" class="view-btn blue-bg" data-title="{{ __('Edit Employee salary') }}">' +
                                    '{{ __('Edit') }}' + '</a>';
                            }

                            var url = '{{ route('payslip.delete', ':id') }}';
                            url = url.replace(':id', payslip_id);

                            @if (\Auth::user()->type != 'Employee')
                                if (data != 0) {
                                    deleted = '<a href="#"  data-url="' + url +
                                        '" class="payslip_delete view-btn red-bg" >' +
                                        '{{ __('Delete') }}' + '</a>';
                                }
                            @endif

                            return view + payslip + clickToPaid + edit + deleted + form;
                        }
                        var tr = '';
                        if (data.length > 0) {
                            $.each(data, function(indexInArray, valueOfElement) {



                                var status =
                                    '<div class="badge bg-danger p-2 px-3 rounded"><a href="#" class="text-white">' +
                                    valueOfElement[6] + '</a></div>';
                                if (valueOfElement[6] == 'Paid') {
                                    var status =
                                        '<div class="badge bg-success p-2 px-3 rounded"><a href="#" class="text-white">' +
                                        valueOfElement[6] + '</a></div>';
                                }

                                var id = valueOfElement[0];
                                var employee_id = valueOfElement[1];
                                var payslip_id = valueOfElement[7];

                                if (valueOfElement[7] != 0) {
                                    var payslip =
                                        '<a href="#" data-url="{{ url('payslip/pdf/') }}/' +
                                        id +
                                        '/' + datePicker +
                                        '" data-size="lg"  data-ajax-popup="true" class=" btn-sm btn btn-warning me-1" data-title="{{ _('Employee Payslip') }}" data-bs-toggle="tooltip" title="{{('Payslip')}}" data-original-title="{{_('Payslip')}}"><i class="ti ti-report-money"></i></a> ';
                                }
                                if (valueOfElement[6] == "UnPaid" && valueOfElement[7] != 0) {
                                    // var clickToPaid =
                                    //     '<a href="{{ url('payslip/paysalary/') }}/' + id +
                                    //     '/' + datePicker +
                                    //     '"  class="btn-sm btn btn-primary me-1" "data-bs-toggle="tooltip" title="{{('Click To Paid')}}" data-original-title="{{('Click To Paid')}}"><i class="ti ti-currency-dollar"></i></a>  ';
                                    var clickToPaid = '';
                                } else {
                                    var clickToPaid = '';
                                }

                                if (valueOfElement[7] != 0 && valueOfElement[6] == "UnPaid") {
                                    var edit =
                                        '<a href="#" data-url="{{ url('payslip/editemployee/') }}/' +
                                        payslip_id +
                                        '"  data-ajax-popup="true" class="btn-sm btn btn-info me-2" data-title="{{ _('Edit Employee salary') }}"data-bs-toggle="tooltip" title="{{('Edit')}}" data-original-title="{{_('Edit')}}"><i class="ti ti-pencil text-white"></i></a>';
                                } else {
                                    var edit = '';
                                }


                                var url = '{{ route('payslip.delete', ':id') }}';
                                url = url.replace(':id', payslip_id);

                                @if (\Auth::user()->type != 'Employee')
                                    if (valueOfElement[7] != 0) {
                                        var deleted = '<a href="#"  data-url="' + url +
                                            '" class="payslip_delete view-btn btn btn-danger btn-sm"   data-bs-toggle="tooltip" title="{{('Delete')}}" data-original-title="{{('Delete')}}"><i class="ti ti-trash text-white"></i></a>';
                                    } else {
                                        var deleted = '';
                                    }
                                @endif
                                var url_employee = valueOfElement['url'];

                                tr +=
                                    '<tr> ' +
                                    '<td> <a class="btn btn-outline-primary" href="' +
                                    url_employee + '">' +
                                    valueOfElement[1] + '</a></td> ' +
                                    '<td>' + valueOfElement[2] + '</td> ' +
                                    '<td>' + valueOfElement[3] + '</td>' +
                                    '<td>' + valueOfElement[4] + '</td>' +
                                    '<td>' + valueOfElement[5] + '</td>' +
                                    '<td>' + status + '</td>' +
                                    '<td>' + payslip + clickToPaid + edit + deleted + '</td>' +
                                    '</tr>';
                            });
                        } else {
                            // var colspan = $('#pc-dt-render-column-cells thead tr th').length;
                            // var tr = '<tr><td class="dataTables-empty" colspan="' + colspan +
                            //     '">{{ __('No entries found') }}</td></tr>';
                        }

                        $('#pc-dt-render-column-cells tbody').html(tr);
                        // var table = document.querySelector("#pc-dt-render-column-cells");
                        new simpleDatatables.DataTable('#pc-dt-render-column-cells');
                        // var datatable = new simpleDatatables.DataTable("#pc-dt-render-column-cells");

                    },
                    error: function(data) {

                    }

                });

            }

            $(document).on("change", ".month_date,.year_date", function() {
                callback();
            });

            //bulkpayment Click
            $(document).on("click", "#bulk_payment", function() {
                var month = $(".month_date").val();
                var year = $(".year_date").val();
                var datePicker = year + '_' + month;


            });
            $(document).on('click', '#bulk_payment',
                'a[data-ajax-popup="true"], button[data-ajax-popup="true"], div[data-ajax-popup="true"]',
                function() {
                    var month = $(".month_date").val();
                    var year = $(".year_date").val();
                    var datePicker = year + '-' + month;

                    var title = 'Bulk Payment';
                    var size = 'md';
                    var url = 'payslip/bulk_pay_create/' + datePicker;

                    // return false;

                    $("#commonModal .modal-title").html(title);
                    $("#commonModal .modal-dialog").addClass('modal-' + size);
                    $.ajax({
                        url: url,
                        success: function(data) {
                            // alert(data);
                            // return false;
                            if (data.length) {
                                $('#commonModal .body').html(data);
                                $("#commonModal").modal('show');
                                // common_bind();
                            } else {
                                show_toastr('error', 'Permission denied.');
                                $("#commonModal").modal('hide');
                            }
                        },
                        error: function(data) {
                            data = data.responseJSON;
                            show_toastr('error', data.error);
                        }
                    });
                });

            $(document).on("click", ".payslip_delete", function() {
                var confirmation = confirm("are you sure you want to delete this payslip?");
                var url = $(this).data('url');


                if (confirmation) {
                    $.ajax({
                        type: "GET",
                        url: url,
                        dataType: "JSON",
                        success: function(data) {

                            // show_toastr(data.status, data.msg, 'data.status');
                            show_toastr('success', 'Payslip Deleted Successfully', 'success');


                            setTimeout(function() {
                                location.reload();
                            }, 800)


                        },

                    });

                }
            });
        });
    </script>
@endpush