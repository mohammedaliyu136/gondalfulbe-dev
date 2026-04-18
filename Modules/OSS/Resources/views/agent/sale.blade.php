@extends('layouts.admin')
@section('page-title', __('Record Field Sale'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('oss.agent.index') }}">{{ __('Agent Distribution') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Field Sale') }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-9">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">{{ __('Record Agent Field Sale') }}</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('oss.agent.record-sale') }}">
                    @csrf
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Agent') }} <span class="text-danger">*</span></label>
                            <select name="agent_id" class="form-select @error('agent_id') is-invalid @enderror" id="agentSel" required>
                                <option value="">{{ __('Select agent') }}</option>
                                @foreach($agents as $a)
                                <option value="{{ $a->id }}" @selected(old('agent_id') == $a->id)>{{ $a->name }}</option>
                                @endforeach
                            </select>
                            @error('agent_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
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
                        <div class="col-md-2">
                            <label class="form-label">{{ __('Date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('Payment') }} <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select" required>
                                <option value="Cash">{{ __('Cash') }}</option>
                                <option value="Credit">{{ __('Credit') }}</option>
                                <option value="Mobile Money">{{ __('Mobile Money') }}</option>
                            </select>
                        </div>
                    </div>

                    <h6>{{ __('Items') }}</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered" id="agentSaleTable">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Product') }}</th>
                                    <th style="width:130px">{{ __('Qty') }}</th>
                                    <th style="width:150px">{{ __('Unit Price') }}</th>
                                    <th style="width:140px">{{ __('Subtotal') }}</th>
                                    <th style="width:50px"></th>
                                </tr>
                            </thead>
                            <tbody id="agentItemsBody">
                                <tr class="item-row">
                                    <td>
                                        <select name="items[0][product_id]" class="form-select form-select-sm product-sel" required>
                                            <option value="">{{ __('Select') }}</option>
                                            @foreach($products as $p)
                                            <option value="{{ $p->id }}" data-price="{{ $p->unit_price }}">{{ $p->name }} ({{ $p->unit }})</option>
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
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="addAgentRow">
                            <i class="ti ti-plus"></i> {{ __('Add Item') }}
                        </button>
                        <strong>{{ __('Total') }}: ₦<span id="agentGrandTotal">0.00</span></strong>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">{{ __('Record Sale') }}</button>
                        <a href="{{ route('oss.agent.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let rc = 1;
function computeRow(row) {
    const qty = parseFloat(row.querySelector('.qty-input').value)||0;
    const price = parseFloat(row.querySelector('.price-input').value)||0;
    row.querySelector('.subtotal-input').value = (qty*price).toFixed(2);
    let total=0;
    document.querySelectorAll('.subtotal-input').forEach(el=>total+=parseFloat(el.value)||0);
    document.getElementById('agentGrandTotal').textContent=total.toFixed(2);
}
document.getElementById('agentItemsBody').addEventListener('input',e=>{const r=e.target.closest('.item-row');if(r)computeRow(r);});
document.getElementById('agentItemsBody').addEventListener('change',e=>{
    if(e.target.classList.contains('product-sel')){
        const opt=e.target.selectedOptions[0];
        const row=e.target.closest('.item-row');
        row.querySelector('.price-input').value=parseFloat(opt?.dataset.price||0).toFixed(2);
        computeRow(row);
    }
});
document.getElementById('addAgentRow').addEventListener('click',function(){
    const tbody=document.getElementById('agentItemsBody');
    const clone=tbody.querySelector('.item-row').cloneNode(true);
    clone.querySelectorAll('input').forEach(el=>el.value='');
    clone.querySelectorAll('select').forEach(el=>el.selectedIndex=0);
    clone.querySelector('.subtotal-input').value='0.00';
    clone.querySelectorAll('[name]').forEach(el=>{el.name=el.name.replace(/\[\d+\]/,'['+rc+']');});
    rc++;
    tbody.appendChild(clone);
});
document.getElementById('agentItemsBody').addEventListener('click',e=>{
    if(e.target.closest('.remove-row')){
        const rows=document.querySelectorAll('.item-row');
        if(rows.length>1){e.target.closest('.item-row').remove();
        let t=0;document.querySelectorAll('.subtotal-input').forEach(el=>t+=parseFloat(el.value)||0);
        document.getElementById('agentGrandTotal').textContent=t.toFixed(2);}
    }
});
</script>
@endpush
