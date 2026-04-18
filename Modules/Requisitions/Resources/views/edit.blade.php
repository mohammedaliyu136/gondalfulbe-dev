@extends('layouts.admin')
@section('page-title'){{ __('New Requisition') }}@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('requisitions.index') }}">{{ __('Requisitions') }}</a></li>
    <li class="breadcrumb-item">{{ __('New') }}</li>
@endsection
@section('content')
<div class="card">
<div class="card-header"><h5 class="mb-0">{{ __('New Requisition') }}</h5></div>
<div class="card-body">
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    <form action="{{ route('requisitions.store') }}" method="POST">
        @csrf
        <div class="row g-3 mb-4">
            <div class="col-md-6"><label class="form-label">{{ __('Title') }} *</label>
                <input type="text" name="title" class="form-control" value="{{ old('title') }}" required></div>
            <div class="col-md-3"><label class="form-label">{{ __('Request Date') }} *</label>
                <input type="date" name="request_date" class="form-control" value="{{ old('request_date', date('Y-m-d')) }}" required></div>
            <div class="col-md-3"><label class="form-label">{{ __('Priority') }} *</label>
                <select name="priority" class="form-select" required>
                    @foreach($priorities as $p)<option value="{{ $p }}" {{ old('priority','Medium')==$p?'selected':'' }}>{{ $p }}</option>@endforeach
                </select></div>
            <div class="col-md-6"><label class="form-label">{{ __('Centre (MCC)') }}</label>
                <select name="center" class="form-select">
                    <option value="">{{ __('N/A') }}</option>
                    @foreach($mccs as $m)<option value="{{ $m }}" {{ old('center')==$m?'selected':'' }}>{{ $m }}</option>@endforeach
                </select></div>
            <div class="col-md-6"><label class="form-label">{{ __('Description') }}</label>
                <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea></div>
        </div>

        <h6>{{ __('Items') }}</h6>
        <div class="table-responsive">
        <table class="table table-bordered" id="items-table">
            <thead class="table-light"><tr>
                <th>{{ __('Item Name') }} *</th>
                <th>{{ __('Qty') }} *</th>
                <th>{{ __('Unit') }}</th>
                <th>{{ __('Est. Cost (₦)') }} *</th>
                <th>{{ __('Purpose') }}</th>
                <th>{{ __('Subtotal') }}</th>
                <th></th>
            </tr></thead>
            <tbody id="items-body">
                <tr class="item-row">
                    <td><input type="text" name="items[0][item_name]" class="form-control form-control-sm" required></td>
                    <td><input type="number" name="items[0][quantity]" class="form-control form-control-sm qty" step="0.01" min="0.01" required oninput="updateTotal()"></td>
                    <td><input type="text" name="items[0][unit]" class="form-control form-control-sm" placeholder="{{ __('e.g. kg') }}"></td>
                    <td><input type="number" name="items[0][estimated_cost]" class="form-control form-control-sm cost" step="0.01" min="0" required oninput="updateTotal()"></td>
                    <td><input type="text" name="items[0][purpose]" class="form-control form-control-sm"></td>
                    <td class="subtotal">₦0.00</td>
                    <td><button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)"><i class="ti ti-trash"></i></button></td>
                </tr>
            </tbody>
        </table>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary mb-3" onclick="addRow()"><i class="ti ti-plus"></i> {{ __('Add Item') }}</button>

        <div class="d-flex align-items-center justify-content-end gap-3">
            <h5>{{ __('Total') }}: <strong id="grand-total">₦0.00</strong></h5>
            <button type="submit" class="btn btn-primary">{{ __('Submit Requisition') }}</button>
            <a href="{{ route('requisitions.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
</div></div>
<script>
var rowIdx = 1;
function addRow() {
    var i = rowIdx++;
    var row = `<tr class="item-row">
        <td><input type="text" name="items[${i}][item_name]" class="form-control form-control-sm" required></td>
        <td><input type="number" name="items[${i}][quantity]" class="form-control form-control-sm qty" step="0.01" min="0.01" required oninput="updateTotal()"></td>
        <td><input type="text" name="items[${i}][unit]" class="form-control form-control-sm"></td>
        <td><input type="number" name="items[${i}][estimated_cost]" class="form-control form-control-sm cost" step="0.01" min="0" required oninput="updateTotal()"></td>
        <td><input type="text" name="items[${i}][purpose]" class="form-control form-control-sm"></td>
        <td class="subtotal">₦0.00</td>
        <td><button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)"><i class="ti ti-trash"></i></button></td>
    </tr>`;
    document.getElementById('items-body').insertAdjacentHTML('beforeend', row);
}
function removeRow(btn) {
    var rows = document.querySelectorAll('.item-row');
    if (rows.length > 1) { btn.closest('tr').remove(); updateTotal(); }
}
function updateTotal() {
    var total = 0;
    document.querySelectorAll('.item-row').forEach(function(row) {
        var qty  = parseFloat(row.querySelector('.qty')?.value) || 0;
        var cost = parseFloat(row.querySelector('.cost')?.value) || 0;
        var sub  = qty * cost;
        row.querySelector('.subtotal').textContent = '₦' + sub.toLocaleString('en-NG', {minimumFractionDigits:2});
        total += sub;
    });
    document.getElementById('grand-total').textContent = '₦' + total.toLocaleString('en-NG', {minimumFractionDigits:2});
}
</script>
@endsection
