@extends('layouts.admin')
@section('page-title', __('New OSS Sale'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('oss-sales.index') }}">{{ __('OSS Sales') }}</a></li>
    <li class="breadcrumb-item active">{{ __('New Sale') }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">{{ __('Record OSS Sale') }}</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('oss-sales.store') }}" id="saleForm">
                    @csrf
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Farmer') }} <span class="text-danger">*</span></label>
                            <select name="farmer_id" class="form-select @error('farmer_id') is-invalid @enderror" required>
                                <option value="">{{ __('Select farmer') }}</option>
                                @foreach($farmers as $f)
                                <option value="{{ $f->id }}" @selected(old('farmer_id') == $f->id)>{{ $f->name }}</option>
                                @endforeach
                            </select>
                            @error('farmer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Center') }}</label>
                            <select name="center" class="form-select">
                                <option value="">{{ __('Select center') }}</option>
                                @foreach($mccs as $mcc)
                                <option value="{{ $mcc }}" @selected(old('center') === $mcc)>{{ $mcc }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Payment Method') }} <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select" required>
                                <option value="Cash" @selected(old('payment_method') === 'Cash')>{{ __('Cash') }}</option>
                                <option value="Credit" @selected(old('payment_method') === 'Credit')>{{ __('Credit') }}</option>
                                <option value="Mobile Money" @selected(old('payment_method') === 'Mobile Money')>{{ __('Mobile Money') }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <h6 class="mb-3">{{ __('Sale Items') }}</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered" id="itemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Product') }}</th>
                                    <th style="width:140px">{{ __('Qty') }}</th>
                                    <th style="width:150px">{{ __('Unit Price (₦)') }}</th>
                                    <th style="width:150px">{{ __('Subtotal') }}</th>
                                    <th style="width:50px"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <tr class="item-row">
                                    <td>
                                        <select name="items[0][product_id]" class="form-select form-select-sm product-sel" required>
                                            <option value="">{{ __('Select') }}</option>
                                            @foreach($products as $p)
                                            <option value="{{ $p->id }}" data-price="{{ $p->price }}">{{ $p->name }} ({{ $p->unit }})</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" step="0.01" min="0.01" name="items[0][quantity]" class="form-control form-control-sm qty-input" required></td>
                                    <td><input type="number" step="0.01" min="0" name="items[0][unit_price]" class="form-control form-control-sm price-input" required></td>
                                    <td><input type="text" class="form-control form-control-sm subtotal-input" readonly value="0.00"></td>
                                    <td><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="ti ti-trash"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm mb-3" id="addRow">
                        <i class="ti ti-plus"></i> {{ __('Add Item') }}
                    </button>

                    <div class="text-end">
                        <strong>{{ __('Total') }}: ₦<span id="grandTotal">0.00</span></strong>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">{{ __('Record Sale') }}</button>
                        <a href="{{ route('oss-sales.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let rowCount = 1;

function computeRow(row) {
    const qty   = parseFloat(row.querySelector('.qty-input').value) || 0;
    const price = parseFloat(row.querySelector('.price-input').value) || 0;
    const sub   = qty * price;
    row.querySelector('.subtotal-input').value = sub.toFixed(2);
    computeTotal();
}

function computeTotal() {
    let total = 0;
    document.querySelectorAll('.subtotal-input').forEach(el => total += parseFloat(el.value) || 0);
    document.getElementById('grandTotal').textContent = total.toFixed(2);
}

document.getElementById('itemsBody').addEventListener('input', e => {
    const row = e.target.closest('.item-row');
    if (row) computeRow(row);
});

document.getElementById('itemsBody').addEventListener('change', e => {
    if (e.target.classList.contains('product-sel')) {
        const opt = e.target.selectedOptions[0];
        const price = opt ? (opt.dataset.price || 0) : 0;
        const row = e.target.closest('.item-row');
        row.querySelector('.price-input').value = parseFloat(price).toFixed(2);
        computeRow(row);
    }
});

document.getElementById('addRow').addEventListener('click', function () {
    const tbody = document.getElementById('itemsBody');
    const first = tbody.querySelector('.item-row');
    const clone = first.cloneNode(true);
    clone.querySelectorAll('input').forEach(el => el.value = '');
    clone.querySelectorAll('select').forEach(el => el.selectedIndex = 0);
    clone.querySelector('.subtotal-input').value = '0.00';
    clone.querySelectorAll('[name]').forEach(el => {
        el.name = el.name.replace(/\[\d+\]/, '[' + rowCount + ']');
    });
    rowCount++;
    tbody.appendChild(clone);
});

document.getElementById('itemsBody').addEventListener('click', e => {
    if (e.target.closest('.remove-row')) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length > 1) { e.target.closest('.item-row').remove(); computeTotal(); }
    }
});
</script>
@endpush
