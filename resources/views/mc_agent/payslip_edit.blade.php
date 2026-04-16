
@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
@endpush
<div class="modal-view">

        {{ Form::model($payslip, ['route' => ['mcagent.payslips.update', $payslip->id], 'method' => 'PUT', 'class' => 'needs-validation']) }}
        @csrf

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Amount</th>
        </tr>
            </thead>
            <tbody>
                
                    <tr>
                        <td>
                            @can('show rider')
                                <a href="{{ route('mcagent.show', \Crypt::encrypt($payslip->agent->id)) }}" class="btn btn-outline-primary">
                                    SEB-AG-00{{ $payslip->agent->agent_id }}
                                </a>
                            @else
                                <a href="#" class="btn btn-outline-primary"> SEB-AG-00{{ $payslip->agent->agent_id }}</a>
                            @endcan
                        </td>
                        <td>{{ $payslip->agent->name }}</td>
                        <td>
                            ₦<input type="number" name="amount" value="{{ $payslip->amount }}" class="rider-amount" step="0.01" required />
                        </td>
                    </tr>

            </tbody>
        </table>
        <div class="text-right m-4 float-end">
            <button type="submit" class="btn btn-primary" id="submit-button" >Update  Payslip</button>
        </div>
{{ Form::close() }}
</div>




