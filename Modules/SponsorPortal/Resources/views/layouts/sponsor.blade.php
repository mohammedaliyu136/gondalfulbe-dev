<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('page-title', __('Sponsor Portal')) — Gondal Fulbe ERP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <style>
        body { background: #f0f4f8; }
        .sidebar { min-height: 100vh; background: #1a3c5e; color: #cdd9e8; width: 240px; flex-shrink: 0; }
        .sidebar .brand { padding: 1.5rem 1rem; border-bottom: 1px solid rgba(255,255,255,.1); }
        .sidebar .brand img { height: 40px; }
        .sidebar .nav-link { color: #a0b8d0; padding: .5rem 1rem; border-radius: 6px; margin: 2px 8px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: rgba(255,255,255,.1); color: #fff; }
        .sidebar .nav-link .ti { font-size: 1.2rem; margin-right: .5rem; }
        .main-content { flex: 1; overflow-y: auto; }
        .topbar { background: #fff; border-bottom: 1px solid #e2e8f0; padding: .75rem 1.5rem; }
    </style>
    @stack('styles')
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column">
        <div class="brand d-flex align-items-center gap-2">
            <i class="ti ti-building-community fs-3 text-white"></i>
            <div>
                <div class="fw-bold text-white" style="font-size:0.95rem">Gondal Fulbe</div>
                <div style="font-size:0.75rem">{{ __('Sponsor Portal') }}</div>
            </div>
        </div>
        <nav class="nav flex-column mt-3">
            <a class="nav-link @if(request()->routeIs('sponsor.dashboard')) active @endif"
               href="{{ route('sponsor.dashboard') }}">
                <i class="ti ti-layout-dashboard"></i> {{ __('Dashboard') }}
            </a>
        </nav>
        <div class="mt-auto p-3 border-top" style="border-color:rgba(255,255,255,.1) !important;">
            <div class="small mb-2 text-truncate">{{ auth('sponsor')->user()->name }}</div>
            <form method="POST" action="{{ route('sponsor.logout') }}">
                @csrf
                <button class="btn btn-sm btn-outline-light w-100">
                    <i class="ti ti-logout"></i> {{ __('Sign Out') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Main -->
    <div class="main-content d-flex flex-column w-100">
        <div class="topbar d-flex justify-content-between align-items-center">
            <h5 class="mb-0">@yield('page-title', __('Sponsor Portal'))</h5>
            <span class="text-muted small">{{ now()->format('d M Y') }}</span>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible mx-4 mt-3 mb-0" role="alert">
            {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible mx-4 mt-3 mb-0" role="alert">
            {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="container-fluid p-4">
            @yield('content')
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
