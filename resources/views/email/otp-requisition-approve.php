<!DOCTYPE html>
<html>
<head>
    <title>OTP Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            background-color: #004085;
            color: #ffffff;
            padding: 15px;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
        .header h2 {
            margin: 0;
            font-size: 20px;
        }
        .content {
            padding: 20px;
            font-size: 16px;
            line-height: 1.5;
        }
        .otp-box {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            background: #004085;
            color: #ffffff;
            padding: 10px;
            border-radius: 5px;
            display: inline-block;
            margin: 20px 0;
        }
        .table-container {
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #f9f9f9;
            border-radius: 5px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background: #6fd943;
            color: white;
        }
        td {
            background: #ffffff;
            color: #333;
        }
        .footer {
            text-align: center;
            font-size: 14px;
            color: #666;
            padding: 10px;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>OTP Verification: Requisition Approval</h2>
    </div>

    <div class="content">
        <p>Hello <strong>{{ Auth::user()->name }}</strong>,</p>
        <p>Your One-Time Password (OTP) for payslip {{_($payslip->batch_id)}} is:</p>
        <div class="otp-box">{{ $otp }}</div>
        <p>This OTP is valid for a limited time. Please do not share it with anyone.</p>

        <div class="table-container">
            <h3>Transaction Details</h3>
            <table>
                <tbody>
                    <tr>
                        <th>Disbursement Amount:</th>
                        <td style="color: green;">₦{{ number_format($totalAmount, 2, '.', ',') }}</td>
                    </tr>
                  
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        <p>Thank you.</p>
    </div>
</div>

</body>
</html>
