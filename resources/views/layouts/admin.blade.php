<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Admin') - Sensus Harian</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="admin-page-body">

    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <i class="fas fa-hospital-user"></i>
                <h2>Panel Admin</h2>
            </div>
            <ul class="sidebar-nav">
                <li class="nav-item {{ request()->is('manajemen/akun*') ? 'active' : '' }}">
                    <a href="#"><i class="fas fa-users-cog"></i> Manajemen Akun</a>
                </li>
                <li class="nav-item {{ request()->is('manajemen/ruangan*') ? 'active' : '' }}">
                    <a href="{{ route('ruangan.index') }}"><i class="fas fa-door-open"></i> Manajemen Ruangan</a>
                </li>
            </ul>
            <div class="sidebar-footer">
                <a href="{{ url('/') }}" class="back-to-app"><i class="fas fa-arrow-left"></i> Kembali ke Aplikasi</a>
            </div>
        </aside>

        <main class="admin-content">
            @yield('content')
        </main>
    </div>

    @yield('scripts')
</body>
</html>