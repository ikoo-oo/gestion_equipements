<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Gestion Équipements IT</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <!-- Role-based colors -->
    <style>
        :root {
            @if(auth()->check())
                @if(auth()->user()->role === 'hr')
                    --main-color: #FF8C42;
                    --main-color-light: #FFB380;
                    --main-color-dark: #e9731f;
                @elseif(auth()->user()->role === 'it_manager')
                    --main-color: #7B68EE;
                    --main-color-light: #9D8FFF;
                    --main-color-dark: #6A5ACD;
                @else
                    --main-color: #20B2AA;
                    --main-color-light: #5DD9D1;
                    --main-color-dark: #17958E;
                @endif
            @endif
        }

        /* Notification Dropdown Styles */
        .notification-dropdown {
            position: relative;
        }

        .notification-bell {
            color: white;
            text-decoration: none;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .notification-bell:hover {
            color: rgba(255, 255, 255, 0.8);
            transform: scale(1.1);
        }

        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.65rem;
            font-weight: bold;
            min-width: 18px;
            text-align: center;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .notification-dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 320px;
            max-height: 400px;
            overflow-y: auto;
            display: none;
            z-index: 1000;
            margin-top: 10px;
        }

        .notification-dropdown-menu.show {
            display: block;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .notification-header {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            font-weight: 600;
            font-size: 1rem;
            color: #2c3e50;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-item {
            padding: 12px 15px;
            border-bottom: 1px solid #f1f3f5;
            transition: all 0.2s ease;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            color: inherit;
        }

        .notification-item:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-icon {
            font-size: 1.8rem;
            flex-shrink: 0;
        }

        .notification-content {
            flex: 1;
        }

        .notification-message {
            font-size: 0.9rem;
            color: #495057;
            margin-bottom: 3px;
            font-weight: 500;
        }

        .notification-time {
            font-size: 0.75rem;
            color: #6c757d;
        }

        .notification-empty {
            padding: 40px 20px;
            text-align: center;
            color: #6c757d;
        }

        .notification-empty i {
            margin-bottom: 10px;
            opacity: 0.5;
        }
    </style>
</head>
<body>

    @auth
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, var(--main-color) 0%, var(--main-color-dark) 100%);">
        <div class="container-fluid">
            <!-- Logo/Brand -->
            <a class="navbar-brand fw-bold" href="#">
                <i class="fas fa-laptop me-2"></i>
                Gestion IT
            </a>

            <div class="d-flex align-items-center gap-3">
                <!-- User Info -->
              <span class="text-white d-none d-md-block">
    <i class="fas fa-user-circle me-1"></i>
    {{ auth()->user()->name }}
</span>

                <!-- Notification Bell with Dropdown -->
                <div class="notification-dropdown">
                    <div class="notification-bell" id="notificationBell">
                        <i class="fas fa-bell"></i>
                        @if(auth()->user()->notifications->count() > 0)
                            <span class="notification-badge">{{ auth()->user()->notifications->count() }}</span>
                        @endif
                    </div>

                    <!-- Notification Dropdown Menu -->
                    <div class="notification-dropdown-menu" id="notificationMenu">
                        <div class="notification-header">
                            <span>
                                <i class="fas fa-bell me-2"></i>
                                Notifications
                            </span>
                            <span class="badge bg-danger">{{ auth()->user()->notifications->count() }}</span>
                        </div>

                        @if(auth()->user()->notifications->count() > 0)
                            @foreach(auth()->user()->notifications->take(5) as $notification)
                                @php
                                    // Determine the link based on user role
                                    $link = '#';
                                    if (auth()->user()->role === 'hr') {
                                        $link = route('hr.requests');
                                    } elseif (auth()->user()->role === 'it_manager') {
                                        if ($notification->type === 'new_request') {
                                            $link = route('it-manager.received');
                                        } else {
                                            $link = route('it-manager.assigned');
                                        }
                                    } elseif (auth()->user()->role === 'technician') {
                                        $link = route('technician.requests');
                                    }

                                    // Simple notification messages based on type
                                    $simpleMessage = '';
                                    $icon = '';

                                    switch($notification->type) {
                                        case 'new_request':
                                            $simpleMessage = 'Nouvelle demande reçue';
                                            $icon = '➕';
                                            break;
                                        case 'assigned_request':
                                            $simpleMessage = 'Nouvelle demande assignée';
                                            $icon = '📋';
                                            break;
                                        case 'request_completed':
                                            $simpleMessage = 'Demande terminée';
                                            $icon = '✅';
                                            break;
                                        default:
                                            $simpleMessage = 'Nouvelle notification';
                                            $icon = '🔔';
                                    }
                                @endphp

                                <a href="{{ $link }}" class="notification-item">
                                    <span class="notification-icon">{{ $icon }}</span>
                                    <div class="notification-content">
                                        <div class="notification-message">{{ $simpleMessage }}</div>
                                        <div class="notification-time">
                                            <i class="far fa-clock me-1"></i>
                                            {{ $notification->created_at->locale('fr')->diffForHumans() }}
                                        </div>
                                    </div>
                                </a>
                            @endforeach

                            @if(auth()->user()->notifications->count() > 5)
                                <div class="text-center py-2" style="font-size: 0.85rem; color: #6c757d;">
                                    <i class="fas fa-ellipsis-h"></i>
                                    +{{ auth()->user()->notifications->count() - 5 }} autres
                                </div>
                            @endif
                        @else
                            <div class="notification-empty">
                                <i class="far fa-bell-slash fa-3x"></i>
                                <p class="mb-0">Aucune notification</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Logout Button -->
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-light btn-sm">
                        <i class="fas fa-sign-out-alt me-1"></i> Déconnexion
                    </button>
                </form>
            </div>
        </div>
    </nav>
    @endauth

    <!-- Main Content -->
    <div class="container-fluid px-4 py-4">
        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Page Content -->
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Notification Dropdown Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const notificationBell = document.getElementById('notificationBell');
            const notificationMenu = document.getElementById('notificationMenu');

            if (notificationBell && notificationMenu) {
                // Toggle notification dropdown
                notificationBell.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    notificationMenu.classList.toggle('show');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!notificationBell.contains(e.target) && !notificationMenu.contains(e.target)) {
                        notificationMenu.classList.remove('show');
                    }
                });

                // Close dropdown when clicking a notification link
                const notificationItems = notificationMenu.querySelectorAll('.notification-item');
                notificationItems.forEach(item => {
                    item.addEventListener('click', function() {
                        notificationMenu.classList.remove('show');
                    });
                });
            }
        });
    </script>
</body>
</html>
