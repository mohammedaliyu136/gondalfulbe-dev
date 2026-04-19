@extends('layouts.admin')
@section('page-title'){{ __('Create Budget') }}@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('accounting.dashboard') }}">{{ __('Finance') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('accounting.budget.index') }}">{{ __('Budgets') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create') }}</li>
@endsection
@section('content')
<form method="POST" action="{{ route('accounting.budget.store') }}">
@csrf
<div class="row g-3">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent"><h6 class="mb-0">{{ __('Budget Details') }}</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Budget Name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="FY2026 Operating Budget">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('Fiscal Year') }} <span class="text-danger">*</span></label>
                        <input type="text" name="fiscal_year" class="form-control" maxlength="4" required placeholder="{{ date('Y') }}" value="{{ date('Y') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('Start Date') }} <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control" required value="{{ date('Y') }}-01-01">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('End Date') }} <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" class="form-control" required value="{{ date('Y') }}-12-31">
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __('Description') }}</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Budget Lines --}}
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h6 class="mb-0">{{ __('Budget Lines') }}</h6>
                <button type="button" id="add-line" class="btn btn-sm btn-outline-primary"><i class="ti ti-plus me-1"></i>{{ __('Add Line') }}</button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0" id="lines-table">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width:200px">{{ __('Account') }}</th>
                                <th>{{ __('Jan') }}</th><th>{{ __('Feb') }}</th><th>{{ __('Mar') }}</th>
                                <th>{{ __('Apr') }}</th><th>{{ __('May') }}</th><th>{{ __('Jun') }}</th>
                                <th>{{ __('Jul') }}</th><th>{{ __('Aug') }}</th><th>{{ __('Sep') }}</th>
                                <th>{{ __('Oct') }}</th><th>{{ __('Nov') }}</th><th>{{ __('Dec') }}</th>
                                <th>{{ __('Total') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="lines-body">
                            {{-- rows added by JS --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <button type="submit" class="btn btn-primary w-100">{{ __('Save Budget') }}</button>
                <a href="{{ route('accounting.budget.index') }}" class="btn btn-outline-secondary w-100 mt-2">{{ __('Cancel') }}</a>
            </div>
        </div>
    </div>
</div>
</form>

@push('script-page')
<script>
const accounts = @json($accounts->map(fn($a) => ['id'=>$a->id,'text'=>$a->code.' – '.$a->name]));
let idx = 0;

function addLine(data = {}) {
    const months = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];
    let opts = accounts.map(a => `<option value="${a.id}" ${data.chart_account_id==a.id?'selected':''}>${a.text}</option>`).join('');
    let monthCols = months.map(m => `<td><input type="number" name="lines[${idx}][${m}]" class="form-control form-control-sm month-val" style="width:70px" value="${data[m]||0}" step="0.01" min="0" oninput="calcRow(this)"></td>`).join('');
    const row = `<tr>
        <td><select name="lines[${idx}][chart_account_id]" class="form-select form-select-sm" required><option value="">—</option>${opts}</select></td>
        ${monthCols}
        <td><strong class="row-total">0.00</strong></td>
        <td><button type="button" class="btn btn-xs btn-danger" onclick="this.closest('tr').remove()"><i class="ti ti-trash"></i></button></td>
    </tr>`;
    document.getElementById('lines-body').insertAdjacentHTML('beforeend', row);
    idx++;
}

function calcRow(el) {
    const tr = el.closest('tr');
    const vals = tr.querySelectorAll('.month-val');
    let total = 0;
    vals.forEach(v => total += parseFloat(v.value)||0);
    tr.querySelector('.row-total').textContent = total.toFixed(2);
}

document.getElementById('add-line').addEventListener('click', () => addLine());
addLine(); // start with one empty line
</script>
@endpush
@endsection
