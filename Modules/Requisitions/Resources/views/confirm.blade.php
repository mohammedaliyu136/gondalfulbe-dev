@extends('layouts.main')

@section('page-title')
    {{__('Confirm Item Receipt')}}
@endsection

@section('content')
<div class="row">
    <div class="col-xl-8 offset-xl-2">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{__('Confirm Receipt of Items')}}</h5>
                <span class="badge bg-primary">{{ $req->requisition_ref }}</span>
            </div>
            <div class="card-body">
                <p class="text-muted">{{__('Please review the items listed below and confirm that you have received them in full.')}}</p>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>{{__('Item Description')}}</th>
                                <th>{{__('Qty')}}</th>
                                <th>{{__('Unit Price')}}</th>
                                <th>{{__('Total')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($req->items as $i => $item)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $item->description }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>₦{{ number_format($item->unit_price, 2) }}</td>
                                <td>₦{{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end fw-bold">{{__('Total Estimated Cost')}}</td>
                                <td class="fw-bold">₦{{ number_format($req->total_estimated_cost, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="alert alert-warning">
                    <i class="ti ti-alert-triangle me-1"></i>
                    {{__('By confirming below, you acknowledge that all items have been received in the quantities listed above.')}}
                </div>

                <form action="{{ route('requisitions.complete', $req->id) }}" method="POST">
                    @csrf
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="ti ti-check me-1"></i> {{__('Confirm Receipt')}}
                        </button>
                        <a href="{{ route('requisitions.show', $req->id) }}" class="btn btn-secondary">
                            {{__('Cancel')}}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
