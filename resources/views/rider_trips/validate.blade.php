<div class="modal-body">
    <form method="POST" action="{{ route('rider-trips.process-validate') }}">
        @csrf
        <input type="hidden" name="trip_id" value="{{ $trip->id }}">
        
        <div class="table-responsive">
            <table class="table table-bordered" id="trip-table">
                <thead>
                    <tr>
                        <th>{{ __('Trip Date') }}</th>
                        <th>{{ __('Trip Count') }}</th>
                        <th>{{ __('Amount Per Trip') }}</th>
                        <th>{{ __('Total Amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr> 
                        <td>{{ $trip->trip_date }}</td>
                        <td>{{ $trip->trip_count }}</td>
                        <td>{{ $trip->amount_per_trip }}</td>
                        <td>{{ $trip->total_amount }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td>
                            <button type="submit" name="action" value="valid" class="btn btn-sm btn-success">Valid</button>
                        </td>
                        <td>
                            <button type="submit" name="action" value="invalid" class="btn btn-sm btn-danger">Invalid</button>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </form>
</div>



