@extends('layouts.admin')
@section('page-title'){{ __('New Expense Claim') }}@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('accounting.expense-claims.index') }}">{{ __('Expense Claims') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create') }}</li>
@endsection
@section('content')
<form method="POST" action="{{ route('accounting.expense-claims.store') }}" enctype="multipart/form-data">
@csrf
<div class="row g-3">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent"><h6 class="mb-0">{{ __('Claim Details') }}</h6></div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('Employee') }} <span class="text-danger">*</span></label>
                    <select name="employee_id" class="form-select" required>
                        <option value="">— {{ __('Select') }} —</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('Claim Date') }} <span class="text-danger">*</span></label>
                    <input type="date" name="claim_date" class="form-control" required value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-12">
                    <label class="form-label">{{ __('Title') }} <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" required placeholder="{{ __('e.g. Field trip expenses – April 2026') }}">
                </div>
                <div class="col-12">
                    <label class="form-label">{{ __('Description') }}</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h6 class="mb-0">{{ __('Expense Items') }}</h6>
                <button type="button" id="add-item" class="btn btn-sm btn-outline-primary"><i class="ti ti-plus me-1"></i>{{ __('Add Item') }}</button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Description') }}</th>
                            <th>{{ __('Account') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Receipt') }}</th>
                            <th></th>
                        </tr></thead>
                        <tbody id="items-body"></tbody>
                    </table>
                </div>
                <div class="p-3 border-top d-flex justify-content-end">
                    <strong>{{ __('Total') }}: <span id="grand-total">0.00</span></strong>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <button type="submit" class="btn btn-primary w-100">{{ __('Save Claim') }}</button>
                <a href="{{ route('accounting.expense-claims.index') }}" class="btn btn-outline-secondary w-100 mt-2">{{ __('Cancel') }}</a>
            </div>
        </div>
    </div>
</div>
</form>

@push('script-page')
<script>
const accounts = @json($accounts->map(fn($a)=>['id'=>$a->id,'text'=>$a->code.' – '.$a->name]));
let idx = 0;

function addItem() {
    let opts = accounts.map(a=>`<option value="${a.id}">${a.text}</option>`).join('');
    document.getElementById('items-body').insertAdjacentHTML('beforeend', `
    <tr>
        <td><input type="date" name="items[${idx}][date]" class="form-control form-control-sm" required value="{{ date('Y-m-d') }}"></td>
        <td><input type="text" name="items[${idx}][description]" class="form-control form-control-sm" required placeholder="{{ __('What was this for?') }}"></td>
        <td><select name="items[${idx}][chart_account_id]" class="form-select form-select-sm"><option value="">— {{ __('Optional') }} —</option>${opts}</select></td>
        <td><input type="number" name="items[${idx}][amount]" class="form-control form-control-sm amount-input" step="0.01" min="0.01" required oninput="recalc()"></td>
        <td><input type="file" name="receipts[${idx}]" class="form-control form-control-sm" accept="image/*,application/pdf"></td>
        <td><button type="button" class="btn btn-xs btn-danger" onclick="this.closest('tr').remove();recalc()"><i class="ti ti-trash"></i></button></td>
    </tr>`);
    idx++;
}

function recalc() {
    let total = 0;
    document.querySelectorAll('.amount-input').forEach(v => total += parseFloat(v.value)||0);
    document.getElementById('grand-total').textContent = total.toFixed(2);
}

document.getElementById('add-item').addEventListener('click', addItem);
addItem();
</script>
@endpush
@endsection
