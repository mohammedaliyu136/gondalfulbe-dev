<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Activity Type</th>
            <th>Field</th>
            <th>Worker</th>
            <th>Cost</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($activities as $activity)
            <tr>
                <td>{{ \Carbon\Carbon::parse($activity->activity_date)->format('d-m-Y') }}</td>
                <td>{{ $activity->activity_type }}</td>
                <td>{{ $activity->farmField->field_name ?? 'N/A' }}</td>
                <td>{{ $activity->worker }}</td>
                <td>{{ $activity->cost }}</td>
                <td>{{ $activity->description }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
