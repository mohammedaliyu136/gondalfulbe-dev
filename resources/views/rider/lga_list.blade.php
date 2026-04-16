<div class="modal-view">
    <h4 class="m-4">Rider(s) with Positive Balance</h4>
    <form id="bulk-payment-form" action="{{ route('rider.payslips.lga.bulk_pay_store') }}" method="POST">
        @csrf
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" id="select-all" /> <!-- Check all -->
                    </th>
                    <th>#</th>
                    <th>Name</th>
                    <th>Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($riders as $rider)
                    <tr>
                        <td>
                            <input type="checkbox" name="rider_ids[]" value="{{ $rider->id }}" class="vendor-checkbox" />
                        </td>
                        <td>
                            @can('show vender')
                                <a href="{{ route('rider.show', \Crypt::encrypt($rider->id)) }}" class="btn btn-outline-primary">
                                    SEB-RD-00{{ $rider['rider_id'] }}
                                </a>
                            @else
                                <a href="#" class="btn btn-outline-primary"> {{ AUth::user()->venderNumberFormat($rider->rider_id) }}</a>
                            @endcan
                        </td>
                        <td>{{ $rider->name }}</td>
                        <td>₦{{ number_format($rider->book_balance, 2, '.', ',') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">No rider with positive balance found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="text-right m-4 float-end">
            <button type="submit" class="btn btn-primary" id="submit-button" disabled>Process Bulk Payment</button>
        </div>
        <input type="hidden" value="{{preg_replace('/[^a-zA-Z0-9]/', '', $lga)}}">
    </form>
</div>

<script>
    // Handle the "Select All" checkbox
    document.getElementById('select-all').addEventListener('change', function (e) {
        const checkboxes = document.querySelectorAll('input[name="rider_ids[]"]');
        for (const checkbox of checkboxes) {
            checkbox.checked = e.target.checked;
        }
        toggleSubmitButton(); // Update button state
    });

    // Handle individual checkboxes
    document.querySelectorAll('input[name="rider_ids[]"]').forEach(function (checkbox) {
        checkbox.addEventListener('change', toggleSubmitButton);
    });

    // Enable or disable the submit button
    function toggleSubmitButton() {
        const checkboxes = document.querySelectorAll('input[name="rider_ids[]"]:checked');
        const submitButton = document.getElementById('submit-button');
        submitButton.disabled = checkboxes.length === 0;
    }

    // Initial state check
    toggleSubmitButton();

    // Add confirmation dialog before submitting the form
    document.getElementById('bulk-payment-form').addEventListener('submit', function (e) {
        const confirmation = confirm("Are you sure you want to proceed?");
        if (!confirmation) {
            e.preventDefault(); // Prevent form submission if the user cancels
        }
    });
</script>