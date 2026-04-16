<!DOCTYPE html>
<html>
<head>
    <title>ID Card - Back</title>

    <style>
        body { margin: 0; padding: 0; }

        .id-card-back {
            width: 638px;
            height: 1011px;
            background-image: url('{{ asset("public/id_templates/id_back.png") }}');
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .message-container {
            position: absolute;
            top: 110px;
            width: 100%;
            text-align: center;
            font-size: 20px;
            line-height: 30px;
            padding: 0 40px;
            color: #000;
        }

        .qr {
            position: absolute;
            top: 385px;
            width: 100%;
            text-align: center;
        }

        .qr img {
            width: 220px;
            height: 220px;
        }

        .footer-text {
            position: absolute;
            bottom: 80px;
            width: 100%;
            text-align: center;
            font-size: 18px;
            color: #000;
            line-height: 28px;
            padding: 0 40px;
        }
    </style>

</head>

<body>

<div class="id-card-back">

    <div class="message-container">
        This card confirms the bearer as a staff member of<br>
        <strong>SEBORE INTERNATIONAL FARMS FZE</strong><br>
        and must be returned upon request by Management.
    </div>

    <div class="qr">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode(route('employee.verify.show', $employee->uuid)) }}">
    </div>

    <div class="footer-text">
        IF FOUND PLEASE RETURN TO <br>
        KM 12, Ngurore Road. Mayo-Belwa, Adamawa State, Nigeria.<br>
        or call <strong>08124218331</strong><br>
        www.seborefarns.ng
    </div>

</div>

</body>
</html>
