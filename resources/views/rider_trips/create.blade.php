{{ Form::open(['url' => route('rider-trips.store'), 'method' => 'post', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">
    {{ Form::hidden('rider_id', $rider->id) }}

    <div class="table-responsive">
        <table class="table table-bordered" id="trip-table">
            <thead>
                <tr>
                    <th>{{ __('Trip Date') }}</th>
                    <th>{{ __('Trip Count') }}</th>
                    <th>{{ __('Amount Per Trip') }}</th>
                    <th>{{ __('Total Amount') }}</th>
                    <th>{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="date" name="trips[0][trip_date]" class="form-control" required></td>
                    <td><input type="number" name="trips[0][trip_count]" class="form-control trip-count" value="1" min="1" required></td>
                    <td><input type="number" name="trips[0][amount_per_trip]" class="form-control amount-per-trip" value="{{ $rider->amount_per_trip }}" readonly></td>
                    <td><input type="number" name="trips[0][total_amount]" class="form-control total-amount" readonly></td>
                    <td></td> <!-- no remove button -->
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-end fw-bold">{{ __('Grand Total') }}</td>
                    <td><input type="number" class="form-control" id="grand-total" readonly></td>
                    <td><button type="button" class="btn btn-sm btn-success" id="add-row"><i class="ti ti-plus"></i></button></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="modal-footer">
    <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
</div>
{{ Form::close() }}

<script>
    (function () {
        let rowIdx = 1;
        const amountPerTrip = {{ $rider->amount_per_trip }};

        function updateTotals() {
            let grandTotal = 0;
            $('#trip-table tbody tr').each(function () {
                const $row = $(this);
                const count = parseInt($row.find('.trip-count').val()) || 0;
                const total = count * amountPerTrip;
                $row.find('.total-amount').val(total.toFixed(2));
                grandTotal += total;
            });
            $('#grand-total').val(grandTotal.toFixed(2));
        }

        $(document).on('input', '.trip-count', updateTotals);

        $(document).on('click', '#add-row', function () {
            const newRow = `
                <tr>
                    <td><input type="date" name="trips[${rowIdx}][trip_date]" class="form-control" required></td>
                    <td><input type="number" name="trips[${rowIdx}][trip_count]" class="form-control trip-count" value="1" min="1" required></td>
                    <td><input type="number" name="trips[${rowIdx}][amount_per_trip]" class="form-control amount-per-trip" value="${amountPerTrip}" readonly></td>
                    <td><input type="number" name="trips[${rowIdx}][total_amount]" class="form-control total-amount" readonly></td>
                    <td><button type="button" class="btn btn-sm btn-danger remove-row"><i class="ti ti-trash"></i></button></td>
                </tr>`;
            $('#trip-table tbody').append(newRow);
            rowIdx++;
            updateTotals();
        });

        $(document).on('click', '.remove-row', function () {
            $(this).closest('tr').remove();
            updateTotals();
        });

        $(document).ready(function () {
            updateTotals();
        });
    })();
</script>