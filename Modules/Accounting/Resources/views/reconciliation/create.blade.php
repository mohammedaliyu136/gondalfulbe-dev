@extends('layouts.admin')
@section('page-title'){{ __('New Bank Reconciliation') }}@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('accounting.reconciliation.index') }}">{{ __('Reconciliation') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create') }}</li>
@endsection
@section('content')
<form method="POST" action="{{ route('accounting.reconciliation.store') }}">
@csrf
<div class="row g-3">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent"><h6 class="mb-0">{{ __('Statement Details') }}</h6></div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('Bank Account') }} <span class="text-danger">*</span></label>
                    <select name="bank_account_id" class="form-select" required>
                        <option value="">— {{ __('Select') }} —</option>
                        @foreach($bankAccounts as $acct)
                        <option value="{{ $acct->id }}">{{ $acct->bank_name }} – {{ $acct->account_number }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('Statement Date') }} <span class="text-danger">*</span></label>
                    <input type="date" name="statement_date" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('Opening Balance') }} <span class="text-danger">*</span></label>
                    <input type="number" name="opening_balance" class="form-control" step="0.01" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('Closing Balance') }} <span class="text-danger">*</span></label>
                    <input type="number" name="closing_balance" class="form-control" step="0.01" required>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-transparent d-flex justify-content-between">
                <h6 class="mb-0">{{ __('Statement Items') }}</h6>
                <button type="button" id="add-item" class="btn btn-sm btn-outline-primary"><i class="ti ti-plus me-1"></i>{{ __('Add Item') }}</button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Description') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Reference') }}</th>
                            <th></th>
                        </tr></thead>
                        <tbody id="items-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <button type="submit" class="btn btn-primary w-100">{{ __('Save & Match Items') }}</button>
                <a href="{{ route('accounting.reconciliation.index') }}" class="btn btn-outline-secondary w-100 mt-2">{{ __('Cancel') }}</a>
            </div>
        </div>
    </div>
</div>
</form>

@push('script-page')
<script>
let idx = 0;
function addItem() {
    document.getElementById('items-body').insertAdjacentHTML('beforeend', `
    <tr>
        <td><input type="date" name="items[${idx}][date]" class="form-control form-control-sm" required></td>
        <td><input type="text" name="items[${idx}][description]" class="form-control form-control-sm" required placeholder="{{ __('Description') }}"></td>
        <td><select name="items[${idx}][type]" class="form-select form-select-sm" required>
            <option value="debit">{{ __('Debit') }}</option>
            <option value="credit">{{ __('Credit') }}</option>
        </select></td>
        <td><input type="number" name="items[${idx}][amount]" class="form-control form-control-sm" step="0.01" min="0.01" required></td>
        <td><input type="text" name="items[${idx}][reference]" class="form-control form-control-sm" placeholder="{{ __('Ref #') }}"></td>
        <td><button type="button" class="btn btn-xs btn-danger" onclick="this.closest('tr').remove()"><i class="ti ti-trash"></i></button></td>
    </tr>`);
    idx++;
}
document.getElementById('add-item').addEventListener('click', addItem);
addItem();
</script>
@endpush
@endsection
