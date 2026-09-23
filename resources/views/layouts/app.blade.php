<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @include('layouts.partials.pwa')
    @stack('styles')
</head>
<body class="app-body">
<div class="d-flex app-shell">
    <aside class="sidebar d-none d-lg-flex">
        <a href="{{ route('dashboard') }}" class="sidebar-brand">Personal PM</a>
        @include('layouts.partials.nav')
    </aside>

    <div class="flex-grow-1 d-flex flex-column min-vw-0">
        <nav class="navbar navbar-dark mobile-navbar d-lg-none">
            <div class="container-fluid">
                <a class="navbar-brand" href="{{ route('dashboard') }}">Personal PM</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#appOffcanvas" aria-controls="appOffcanvas" aria-label="Open menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
        </nav>

        <div class="offcanvas offcanvas-end app-offcanvas d-lg-none" tabindex="-1" id="appOffcanvas" aria-labelledby="appOffcanvasLabel">
            <div class="offcanvas-header">
                <h2 class="offcanvas-title h5 mb-0" id="appOffcanvasLabel">Personal PM</h2>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                @include('layouts.partials.nav')
            </div>
        </div>

        <main class="app-main">
            {{ $slot ?? '' }}
            @yield('content')
            @stack('forms')
        </main>
    </div>
</div>

@include('layouts.partials.mobile-tabbar')

<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="appToast" class="toast align-items-center text-bg-dark border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body" id="appToastBody">{{ session('success') }}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/app.js') }}"></script>
@if (session('success') || session('error'))
    <script>window.addEventListener('DOMContentLoaded', () => window.pmShowToast(@json(session('error') ?? session('success'))));</script>
@endif
@stack('scripts')
</body>
</html>
