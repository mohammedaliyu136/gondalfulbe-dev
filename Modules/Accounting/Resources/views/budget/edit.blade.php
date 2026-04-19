@extends('layouts.admin')
@section('page-title'){{ __('Edit Budget') }}: {{ $budget->name }}@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('accounting.budget.index') }}">{{ __('Budgets') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit') }}</li>
@endsection
@section('content')
<form method="POST" action="{{ route('accounting.budget.update', $budget->id) }}">
@csrf @method('PUT')
<div class="row g-3">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('Name') }}</label>
                    <input type="text" name="name" class="form-control" value="{{ $budget->name }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('Start Date') }}</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $budget->start_date->toDateString() }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('End Date') }}</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $budget->end_date->toDateString() }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('Status') }}</label>
                    <select name="status" class="form-select">
                        @foreach(\Modules\Accounting\Models\Budget::STATUSES as $k=>$v)
                        <option value="{{ $k }}" {{ $budget->status===$k?'selected':'' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">{{ __('Description') }}</label>
                    <textarea name="description" class="form-control" rows="2">{{ $budget->description }}</textarea>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-transparent d-flex justify-content-between">
                <h6 class="mb-0">{{ __('Budget Lines') }}</h6>
                <button type="button" id="add-line" class="btn btn-sm btn-outline-primary"><i class="ti ti-plus me-1"></i>{{ __('Add Line') }}</button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr>
                            <th style="min-width:200px">{{ __('Account') }}</th>
                            <th>Jan</th><th>Feb</th><th>Mar</th><th>Apr</th><th>May</th><th>Jun</th>
                            <th>Jul</th><th>Aug</th><th>Sep</th><th>Oct</th><th>Nov</th><th>Dec</th>
                            <th>{{ __('Total') }}</th><th></th>
                        </tr></thead>
                        <tbody id="lines-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <button type="submit" class="btn btn-primary w-100">{{ __('Save Changes') }}</button>
                <a href="{{ route('accounting.budget.show', $budget->id) }}" class="btn btn-outline-secondary w-100 mt-2">{{ __('Cancel') }}</a>
            </div>
        </div>
    </div>
</div>
</form>

@push('script-page')
<script>
const accounts   = @json($accounts->map(fn($a)=>['id'=>$a->id,'text'=>$a->code.' – '.$a->name]));
const existLines = @json($budget->lines);
const months     = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];
let idx = 0;

function addLine(data = {}) {
    let opts = accounts.map(a => `<option value="${a.id}" ${data.chart_account_id==a.id?'selected':''}>${a.text}</option>`).join('');
    let mcols = months.map(m => `<td><input type="number" name="lines[${idx}][${m}]" class="form-control form-control-sm month-val" style="width:70px" value="${data[m]||0}" step="0.01" min="0" oninput="calcRow(this)"></td>`).join('');
    document.getElementById('lines-body').insertAdjacentHTML('beforeend',
        `<tr><td><select name="lines[${idx}][chart_account_id]" class="form-select form-select-sm" required><option value="">—</option>${opts}</select></td>${mcols}<td><strong class="row-total">${(months.reduce((s,m)=>s+(parseFloat(data[m])||0),0)).toFixed(2)}</strong></td><td><button type="button" class="btn btn-xs btn-danger" onclick="this.closest('tr').remove()"><i class="ti ti-trash"></i></button></td></tr>`
    );
    idx++;
}

function calcRow(el){
    const tr=el.closest('tr');
    let t=0; tr.querySelectorAll('.month-val').forEach(v=>t+=parseFloat(v.value)||0);
    tr.querySelector('.row-total').textContent=t.toFixed(2);
}

existLines.forEach(l => addLine(l));
document.getElementById('add-line').addEventListener('click', ()=>addLine());
</script>
@endpush
@endsection
