<!DOCTYPE html>
<html>
<head>
    <title>Print ID Card</title>

    <style>
        body {
            background: #f4f4f4;
            padding: 20px;
            text-align: center;
            font-family: Arial, sans-serif;
        }

        .controls {
            margin-bottom: 20px;
        }

        .controls button {
            padding: 10px 20px;
            margin: 5px;
            font-size: 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
        }

        .btn-front { background: #0f5132; color: white; }
        .btn-back { background: #0069d9; color: white; }
        .btn-print { background: black; color: white; }

        .card-wrapper {
            width: 1011px;
            margin: auto;
        }

        .card-section {
            display: none;
            margin-bottom: 20px;
        }

        @media print {
            .controls { display: none !important; }
            body { margin: 0; padding: 0; background: white; }
            .card-section { display: block !important; page-break-after: always; }
        }
    </style>

</head>

<body>

<div class="controls">
    <button class="btn-front" onclick="showFront()">Show Front</button>
    <button class="btn-back" onclick="showBack()">Show Back</button>
    <button class="btn-print" onclick="window.print()">Print</button>
</div>

<div class="card-wrapper">

    <!-- FRONT -->
    <div id="front" class="card-section">
        @include('employee.id_card')
    </div>

    <!-- BACK -->
    <div id="back" class="card-section">
        @include('employee.id_card_back')
    </div>

</div>

<script>
function showFront() {
    document.getElementById('front').style.display = 'block';
    document.getElementById('back').style.display = 'none';
}

function showBack() {
    document.getElementById('front').style.display = 'none';
    document.getElementById('back').style.display = 'block';
}

// When page loads, show front by default
showFront();
</script>

</body>
</html>
