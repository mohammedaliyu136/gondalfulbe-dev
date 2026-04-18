<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Sponsor Login') }} — Gondal Fulbe ERP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        body { background: linear-gradient(135deg, #1a3c5e 0%, #2d6ea0 100%); min-height: 100vh; display:flex; align-items:center; justify-content:center; }
        .login-card { width: 100%; max-width: 420px; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="card shadow-lg">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <h4 class="fw-bold">Gondal Fulbe</h4>
                <p class="text-muted">{{ __('Sponsor Portal') }}</p>
            </div>

            @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('sponsor.login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">{{ __('Email Address') }}</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" autofocus required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label">{{ __('Password') }}</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">{{ __('Sign In') }}</button>
            </form>
        </div>
    </div>
    <p class="text-center text-white-50 mt-3 small">© {{ date('Y') }} Gondal Fulbe Agricultural Cooperative</p>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
