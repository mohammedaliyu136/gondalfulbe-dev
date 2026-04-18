<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; color: #333; background: #f5f5f5; }
        .container { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 6px; overflow: hidden; }
        .header { background: #1a3a5c; color: #fff; padding: 20px 24px; }
        .header h2 { margin: 0; font-size: 18px; }
        .body { padding: 24px; }
        .stat { background: #f8f9fa; border-left: 4px solid #1a3a5c; padding: 12px 16px; margin-bottom: 12px; border-radius: 3px; }
        .stat .val { font-size: 22px; font-weight: bold; color: #1a3a5c; }
        .stat .lbl { font-size: 12px; color: #888; }
        .alert { background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px 14px; border-radius: 3px; margin-top: 16px; }
        .footer { background: #f5f5f5; padding: 12px 24px; font-size: 11px; color: #aaa; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>{{ $centerName }} — Daily Summary</h2>
            <p style="margin:4px 0 0;font-size:13px;opacity:.8">{{ now()->format('l, d M Y') }}</p>
        </div>
        <div class="body">
            <p>Hello {{ $managerName }},</p>
            <p>Here is your daily operational summary for <strong>{{ $centerName }}</strong>:</p>

            <div class="stat">
                <div class="val">{{ number_format($todayLitres, 2) }} L</div>
                <div class="lbl">Milk collected today</div>
            </div>
            <div class="stat">
                <div class="val">{{ $pendingCosts }}</div>
                <div class="lbl">Cost entries awaiting approval</div>
            </div>
            <div class="stat">
                <div class="val">{{ $lowStockCount }}</div>
                <div class="lbl">Low stock items</div>
            </div>

            @if($lowStockCount > 0)
            <div class="alert">
                ⚠️ <strong>{{ $lowStockCount }} inventory item(s)</strong> are at or below reorder level. Please review and replenish.
            </div>
            @endif
        </div>
        <div class="footer">Gondal Fulbe Integrated ERP — Auto-generated daily summary</div>
    </div>
</body>
</html>
