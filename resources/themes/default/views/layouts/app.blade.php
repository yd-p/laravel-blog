<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', config('app.name'))</title>
    <meta name="description" content="@yield('description', config('app.name') . ' - 专业的内容管理系统')">
    <meta name="keywords" content="@yield('keywords', 'CMS, 内容管理, 博客')">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Theme Styles -->
    @theme_asset('css/style.css')
    
    <style>
        :root {
            --color-primary: {{ app('theme')->getColors()['primary'] ?? '#3b82f6' }};
            --color-secondary: {{ app('theme')->getColors()['secondary'] ?? '#6b7280' }};
            --color-success: {{ app('theme')->getColors()['success'] ?? '#10b981' }};
            --color-danger: {{ app('theme')->getColors()['danger'] ?? '#ef4444' }};
            --color-warning: {{ app('theme')->getColors()['warning'] ?? '#f59e0b' }};
            --color-info: {{ app('theme')->getColors()['info'] ?? '#3b82f6' }};
            --font-body: {{ app('theme')->getFonts()['body'] ?? 'system-ui, sans-serif' }};
            --font-heading: {{ app('theme')->getFonts()['heading'] ?? 'system-ui, sans-serif' }};
        }

        body {
            font-family: var(--font-body);
            background: #f8f9fa;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-heading);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--color-primary) !important;
        }

        .post-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border-radius: 12px;
            overflow: hidden;
        }

        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .post-thumbnail {
            height: 200px;
            object-fit: cover;
        }

        .category-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            z-index: 1;
            background: var(--color-primary);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .sidebar {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .footer {
            background: #1f2937;
            color: white;
            margin-top: 4rem;
            padding: 3rem 0 1.5rem;
        }

        .breadcrumb {
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .search-form {
            max-width: 400px;
        }

        .btn-primary {
            background-color: var(--color-primary);
            border-color: var(--color-primary);
        }

        .btn-primary:hover {
            background-color: color-mix(in srgb, var(--color-primary) 85%, black);
            border-color: color-mix(in srgb, var(--color-primary) 85%, black);
        }

        .text-primary {
            color: var(--color-primary) !important;
        }

        .bg-primary {
            background-color: var(--color-primary) !important;
        }

        .navbar {
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- 导航栏 -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="fas fa-blog me-2"></i>
                {{ config('app.name') }}
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                            <i class="fas fa-home me-1"></i>首页
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('posts.*') ? 'active' : '' }}" href="{{ route('posts.index') }}">
                            <i class="fas fa-file-alt me-1"></i>文章
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('archive*') ? 'active' : '' }}" href="{{ route('archive') }}">
                            <i class="fas fa-archive me-1"></i>归档
                        </a>
                    </li>
                </ul>
                
                <!-- 搜索表单 -->
                <form class="d-flex search-form" method="GET" action="{{ route('search') }}">
                    <div class="input-group">
                        <input class="form-control" type="search" name="q" placeholder="搜索文章..." 
                               value="{{ request('q') }}" aria-label="搜索">
                        <button class="btn btn-outline-light" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </nav>

    <!-- 面包屑导航 -->
    @if(!request()->routeIs('home'))
    <div class="container mt-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('home') }}">
                        <i class="fas fa-home me-1"></i>首页
                    </a>
                </li>
                @yield('breadcrumb')
            </ol>
        </nav>
    </div>
    @endif

    <!-- 主要内容 -->
    <main class="container my-4">
        @yield('content')
    </main>

    <!-- 页脚 -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>{{ config('app.name') }}</h5>
                    <p class="mb-0">专业的内容管理系统，让内容创作更简单。</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">
                        &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                    </p>
                    <p class="mb-0">
                        <small class="text-muted">Powered by Laravel & FilamentPHP</small>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    @stack('scripts')
</body>
</html>
