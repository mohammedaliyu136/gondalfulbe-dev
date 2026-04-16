@php
    use App\Models\Utility;

    $setting = Utility::settings();
    $logo = Utility::get_file('uploads/logo');
    $company_logo = $setting['company_logo_dark'] ?? 'logo-light.png';
@endphp

<!DOCTYPE html>
<html>
<head>
    <title>Employee ID Card</title>

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        .id-card-front {
            width: 638px;
            height: 1011px;
            background-image: url('{{ asset("public/id_templates/id_back.png") }}');
            background-size: cover;
            background-position: center;
            position: relative;
            border-radius: 10px;
            overflow: hidden;
        }

        .top-logo {
            text-align: center;
            padding-top: 35px;
        }

        .top-logo img {
            width: 140px;
        }

        .photo {
            margin-top: 10px;
            text-align: center;
        }

        .photo img {
            width: 240px;
            height: 260px;
            object-fit: cover;
            border-radius: 10px;
            border: 3px solid #0f5132;
        }

        .employee-name {
            text-align: center;
            margin-top: 18px;
            font-size: 40px;
            font-weight: bold;
            color: #0f5132;
        }

        .designation {
            text-align: center;
            font-size: 26px;
            margin-top: 4px;
            color: #000;
        }

        .barcode {
            text-align: center;
            margin-top: 22px;
        }

        .barcode img {
            height: 70px;
        }

    </style>
</head>
<body>

<div class="id-card-front">

    <div class="top-logo">
        <img src="{{ $logo . '/' . $company_logo }}" alt="Company Logo">
    </div>

    <div class="photo">
        <img src="{{ asset('public/uploads/employees_passport/' . $employee->passport) }}" alt="Passport">
    </div>

    <div class="employee-name">
        {{ strtoupper($employee->name) }}
    </div>

    <div class="designation">
        {{ strtoupper($employee->designation->name ?? '-') }}
    </div>

    <div class="barcode">
        <img src="https://barcode.tec-it.com/barcode.ashx?data={{ urlencode($employee->employee_number) }}&code=Code128&dpi=96" alt="Barcode">
    </div>

</div>

</body>
</html>
