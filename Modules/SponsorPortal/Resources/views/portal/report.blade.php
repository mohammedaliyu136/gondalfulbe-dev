<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ __('Impact Report') }} — {{ $project->title }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        h1 { color: #1a3c5e; font-size: 18px; }
        h2 { color: #2d6ea0; font-size: 14px; border-bottom: 1px solid #ddd; padding-bottom: 4px; }
        .header { border-bottom: 2px solid #1a3c5e; margin-bottom: 20px; padding-bottom: 10px; }
        .kpi-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .kpi-table td { padding: 8px 12px; border: 1px solid #ddd; }
        .kpi-table tr:nth-child(even) td { background: #f5f8fc; }
        .footer { margin-top: 40px; font-size: 10px; color: #888; text-align: center; }
    </style>
</head>
<body>
<div class="header">
    <h1>Gondal Fulbe Agricultural Cooperative</h1>
    <h2>{{ __('Sponsor Impact Report') }}</h2>
    <p><strong>{{ __('Sponsor:') }}</strong> {{ $sponsor->organization_name }}<br>
    <strong>{{ __('Project:') }}</strong> {{ $project->title }}<br>
    <strong>{{ __('Report Date:') }}</strong> {{ now()->format('d F Y') }}</p>
</div>

<h2>{{ __('Project Summary') }}</h2>
<table class="kpi-table">
    <tr><td><strong>{{ __('Project Code') }}</strong></td><td>{{ $project->project_code }}</td></tr>
    <tr><td><strong>{{ __('Status') }}</strong></td><td>{{ $project->status }}</td></tr>
    <tr><td><strong>{{ __('Budget Allocated') }}</strong></td><td>₦{{ number_format($project->budget, 2) }}</td></tr>
    <tr><td><strong>{{ __('Start Date') }}</strong></td><td>{{ $project->start_date?->format('d M Y') ?? '—' }}</td></tr>
    <tr><td><strong>{{ __('End Date') }}</strong></td><td>{{ $project->end_date?->format('d M Y') ?? '—' }}</td></tr>
</table>

<h2>{{ __('Impact Metrics') }}</h2>
<table class="kpi-table">
    <tr><td><strong>{{ __('Total Beneficiary Farmers') }}</strong></td><td>{{ $totalFarmers }}</td></tr>
    <tr><td><strong>{{ __('Total Milk Collected (Litres)') }}</strong></td><td>{{ number_format($milkLitres, 2) }}</td></tr>
</table>

<div class="footer">
    {{ __('This report was generated automatically by Gondal Fulbe ERP on') }} {{ now()->format('d M Y H:i') }}.
</div>
</body>
</html>
