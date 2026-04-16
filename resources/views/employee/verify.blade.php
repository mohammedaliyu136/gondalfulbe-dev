<!DOCTYPE html>
<html>
<head>
    <title>Employee Verification</title>
    <style>
        body {
            background: #f5f6fa;
            font-family: Arial, sans-serif;
            padding: 30px;
        }

        .container {
            max-width: 650px;
            margin: 0 auto;
            background: #fff;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .header {
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #004080;
        }

        .profile {
            text-align: center;
            margin-bottom: 20px;
        }

        .profile img {
            width: 180px;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            border: 3px solid #004080;
        }

        .info {
            font-size: 18px;
            line-height: 28px;
        }

        .info strong {
            color: #000;
        }

        .footer {
            margin-top: 25px;
            text-align: center;
            color: #555;
            font-size: 16px;
        }

        .verified {
            display: inline-block;
            padding: 6px 15px;
            background: #27ae60;
            color: white;
            font-weight: bold;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 16px;
        }
    </style>
</head>
<body>

<div class="container">

    <div class="header">Employee Verification</div>

    <div class="verified">Identity Verified ✔</div>

    <div class="profile">
        <img src="{{ asset('public/uploads/employees_passport/' . $employee->passport) }}" alt="Passport">
    </div>

    <div class="info">
        <strong>Name:</strong> {{ $employee->name }} <br>

        <strong>Department:</strong> {{ $employee->department->name ?? '-' }} <br>
        <strong>Phone:</strong> {{ $employee->phone }} <br>
        <strong>Date of Birth:</strong> {{ $employee->dob }} <br>
        <strong>Gender:</strong> {{ $employee->gender }} <br>
        <strong>Branch:</strong> {{ $employee->branch->name ?? '-' }} <br>
    </div>

    <div class="footer">
        This information is retrieved securely using UUID verification.<br>
        © {{ date('Y') }} Sebore Farms
    </div>
</div>

</body>
</html>
