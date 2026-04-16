
@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
@endpush
<div class="modal-view">
    <form id="bulk-payment-form" action="{{ route('rider.payslips.store') }}" method="POST">
        @csrf
 <div class="row mb-4">
    <div class="col-md-4 mb-3">
        <h4>Date Range</h4>
    </div>

    <div class="col-md-4 mb-3">
        <label for="start_date">Start Date:</label>
        <input type="text" id="start_date" name="start_date" class="form-control" value="{{ request('start_date') }}" required />
    </div>
    
    <div class="col-md-4 mb-3">
        <label for="end_date">End Date:</label>
        <input type="text" id="end_date" name="end_date" class="form-control" value="{{ request('end_date') }}" required />
    </div>
</div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" id="select-all" /> <!-- Check all -->
                    </th>
                    <th>#</th>
                    <th>Name</th>
                    <th>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span>Amount</span>
                            ₦<input type="number" step="0.01" id="bulk-amount" class="form-control form-control-sm" placeholder="All" style="width: 40%;">
                        </div>
                    </th>
        </tr>
            </thead>
            <tbody>
                @forelse ($riders as $rider)
                    <tr>
                        <td>
                            <input type="checkbox" name="rider_ids[]" value="{{ $rider->id }}" class="rider-checkbox" />
                        </td>
                        <td>
                            @can('show rider')
                                <a href="{{ route('rider.show', \Crypt::encrypt($rider->id)) }}" class="btn btn-outline-primary">
                                    SEB-RD-00{{ $rider->rider_id }}
                                </a>
                            @else
                                <a href="#" class="btn btn-outline-primary"> SEB-RD-00{{ $rider->rider_id }}</a>
                            @endcan
                        </td>
                        <td>{{ $rider->name }}</td>
                        <td>₦<input type="number" name="amount[]" class="rider-amount" step="0.01" required/> </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">No active rider.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="text-right m-4 float-end">
            <button type="submit" class="btn btn-primary" id="submit-button" disabled>Process  Payment</button>
        </div>
    </form>
</div>


<script>
    
    flatpickr("#start_date", {
        dateFormat: "Y-m-d",
        defaultDate: "{{ request('start_date') }}",
        // Optional: set a max date to the end_date
        onClose: function(selectedDates, dateStr, instance) {
            endDatePicker.set('minDate', dateStr);
        }
    });
    
    const endDatePicker = flatpickr("#end_date", {
        dateFormat: "Y-m-d",
        defaultDate: "{{ request('end_date') }}",
        // Optional: set a min date to the start_date
        onClose: function(selectedDates, dateStr, instance) {
            startDatePicker.set('maxDate', dateStr);
        }
    });
    
    const startDatePicker = flatpickr("#start_date", {
        dateFormat: "Y-m-d",
        defaultDate: "{{ request('start_date') }}",
        onClose: function(selectedDates, dateStr, instance) {
            endDatePicker.set('minDate', dateStr);
        }
    });
    
    const selectAll = document.getElementById('select-all');
    const bulkAmountInput = document.getElementById('bulk-amount');

    // Handle "Select All" checkbox
    selectAll.addEventListener('change', function (e) {
        const checkboxes = document.querySelectorAll('input[name="rider_ids[]"]');
        const amounts = document.querySelectorAll('.rider-amount');
    
        checkboxes.forEach(cb => cb.checked = e.target.checked);
    
        if (e.target.checked && bulkAmountInput.value) {
            checkboxes.forEach(cb => {
                const input = cb.closest('tr').querySelector('.rider-amount');
                if (input) input.value = bulkAmountInput.value;
            });
        }
    
        updateAmountRequiredStates();
        toggleSubmitButton();
    });

    // When bulk amount input changes, update all rider amount fields
    bulkAmountInput.addEventListener('input', function (e) {
        const checkboxes = document.querySelectorAll('input[name="rider_ids[]"]');
        const value = e.target.value;
    
        checkboxes.forEach(cb => {
            if (cb.checked) {
                const input = cb.closest('tr').querySelector('.rider-amount');
                if (input) input.value = value;
            }
        });
    });

    // Track individual checkbox changes
    document.querySelectorAll('input[name="rider_ids[]"]').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            updateAmountRequiredStates();
            toggleSubmitButton();
        });
    });
    
    function updateAmountRequiredStates() {
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const checkbox = row.querySelector('.rider-checkbox');
            const amountInput = row.querySelector('.rider-amount');
    
            if (checkbox && amountInput) {
                amountInput.required = checkbox.checked;
            }
        });
    }


    function toggleSubmitButton() {
        const checked = document.querySelectorAll('input[name="rider_ids[]"]:checked');
        const submit = document.getElementById('submit-button');
        submit.disabled = checked.length === 0;
    }

    toggleSubmitButton();

    // Confirm before submit
    document.getElementById('bulk-payment-form').addEventListener('submit', function (e) {
            
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
    
            if (!startDate || !endDate) {
                alert('Both start date and end date are required.');
                e.preventDefault();
                return;
            }
            
             // Compare the dates
                const start = new Date(startDate);
                const end = new Date(endDate);
            
                if (start > end) {
                    alert('Start date cannot be later than end date.');
                    e.preventDefault();
                    return;
                }
        updateAmountRequiredStates(); // ensure correct before submit
        if (!confirm("Are you sure you want to proceed?")) {
            e.preventDefault();
        }
    });
</script>


