@php
    use App\Models\Utility;
    $setting = \App\Models\Utility::settings();
    $logo = \App\Models\Utility::get_file('uploads/logo');

    $company_logo = $setting['company_logo_dark'] ?? '';
    $company_logos = $setting['company_logo_light'] ?? '';
    $company_small_logo = $setting['company_small_logo'] ?? '';

    $emailTemplate = \App\Models\EmailTemplate::emailTemplateData();
    $lang = Auth::user()->lang;

@endphp
<!DOCTYPE html>
<html>
<head>
    <title>Invalid ID</title>
    <style>
        body {
            background: #f8d7da;
            font-family: Arial, sans-serif;
            text-align: center;
            padding-top: 100px;
        }
        .box {
            background: #fff;
            max-width: 450px;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            border: 2px solid #dc3545;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.15);
        }
        .title {
            font-size: 28px;
            color: #dc3545;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .text {
            font-size: 18px;
            color: #333;
        }
        .logo {
            margin-bottom: 20px;
            max-width: 120px;
        }
    </style>
</head>
<body>

<div class="box">
    <img src="{{ $logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-light.png') }}"
                alt="{{ config('app.name', 'Sebore ERP') }}" class="logo logo-lg" style="max-width: 80px; height: auto;">
    <div class="top-header">

    <div class="title">
        Invalid ID
    </div>

    <div class="text">
        The ID you scanned is not valid or not registered in our system.
    </div>
</div>

</body>
</html>
