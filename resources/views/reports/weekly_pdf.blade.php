<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        h1 { font-size: 18px; color: #1a3a5c; }
        h2 { font-size: 14px; color: #1a3a5c; border-bottom: 1px solid #ccc; padding-bottom: 4px; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th { background: #1a3a5c; color: #fff; padding: 6px 8px; text-align: left; font-size: 11px; }
        td { padding: 5px 8px; border-bottom: 1px solid #e5e5e5; font-size: 11px; }
        .kpi-grid { display: flex; gap: 12px; margin: 12px 0; }
        .kpi-box { border: 1px solid #ccc; border-radius: 4px; padding: 10px 14px; flex: 1; }
        .kpi-val { font-size: 20px; font-weight: bold; color: #1a3a5c; }
        .kpi-label { font-size: 10px; color: #888; }
        .footer { margin-top: 30px; font-size: 10px; color: #aaa; text-align: center; }
    </style>
</head>
<body>
    <h1>Gondal Fulbe ERP — Weekly Report</h1>
    <p>Period: <strong>{{ $weekStart->format('d M Y') }}</strong> to <strong>{{ $weekEnd->format('d M Y') }}</strong></p>
    <p>Generated: {{ now()->format('d M Y H:i') }}</p>

    <h2>Key Performance Indicators</h2>
    <table>
        <tr>
            <th>Metric</th>
            <th>Value</th>
        </tr>
        <tr><td>Active Farmers</td><td>{{ $activeFarmers }}</td></tr>
        <tr><td>Total Litres This Week</td><td>{{ number_format($weekLitres, 2) }} L</td></tr>
        <tr><td>Financial Inclusion Rate</td><td>{{ $financialInclusion }}%</td></tr>
        <tr><td>Centers Operational</td><td>{{ $centersOperational }}</td></tr>
        <tr><td>Requisitions Processed</td><td>{{ $requisitionsCount }}</td></tr>
        <tr><td>Payments Processed (NGN)</td><td>₦{{ number_format($paymentsTotal, 2) }}</td></tr>
    </table>

    <h2>Milk Collection by Center</h2>
    <table>
        <tr><th>Center (MCC)</th><th>Litres This Week</th></tr>
        @foreach($mccSummary as $mcc => $data)
        <tr><td>{{ $mcc }}</td><td>{{ number_format($data['week'], 2) }} L</td></tr>
        @endforeach
    </table>

    <h2>Requisitions Summary</h2>
    <table>
        <tr><th>Status</th><th>Count</th></tr>
        @foreach($requisitionsByStatus as $status => $count)
        <tr><td>{{ ucfirst(str_replace('_', ' ', $status)) }}</td><td>{{ $count }}</td></tr>
        @endforeach
    </table>

    <div class="footer">Gondal Fulbe Integrated ERP — Confidential — Auto-generated report</div>
</body>
</html>
