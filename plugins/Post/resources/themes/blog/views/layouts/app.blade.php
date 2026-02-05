<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', config('app.name'))</title>
    <meta name="description" content="@yield('description', config('app.name') . ' - 专业博客平台')">
    <meta name="keywords" content="@yield('keywords', '博客, 文章, 内容')">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --color-primary: {{ app('theme')->getColors()['primary'] ?? '#6366f1' }};
            --color-secondary: {{ app('theme')->getColors()['secondary'] ?? '#8b5cf6' }};
            --color-success: {{ app('theme')->getColors()['success'] ?? '#10b981' }};
            --color-danger: {{ app('theme')->getColors()['danger'] ?? '#ef4444' }};
            --color-warning: {{ app('theme')->getColors()['warning'] ?? '#f59e0b' }};
            --color-info: {{ app('theme')->getColors()['info'] ?? '#3b82f6' }};
            --font-body: {{ app('theme')->getFonts()['body'] ?? 'Inter, sans-serif' }};
            --font-heading: {{ app('theme')->getFonts()['heading'] ?? 'Poppins, sans-serif' }};
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-body);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-attachment: fixed;
            min-height: 100vh;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-heading);
            font-weight: 700;
        }

        /* 导航栏 */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }

        .navbar-brand {
            font-family: var(--font-heading);
            font-weight: 800;
            font-size: 1.75rem;
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-link {
            font-weight: 500;
            color: #4b5563 !important;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--color-primary) !important;
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 30px;
            height: 3px;
            background: var(--color-primary);
            border-radius: 2px;
        }

        /* 主容器 */
        .main-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        /* 文章卡片 */
        .post-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .post-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        }

        .post-thumbnail {
            height: 220px;
            object-fit: cover;
            position: relative;
        }

        .post-thumbnail::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.3) 100%);
        }

        .category-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            z-index: 2;
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            color: white;
            padding: 0.5rem 1.25rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .post-card .card-body {
            flex: 1;
            padding: 1.75rem;
        }

        .post-card .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1rem;
            line-height: 1.4;
            color: #1f2937;
        }

        .post-card .card-title a {
            color: inherit;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .post-card .card-title a:hover {
            color: var(--color-primary);
        }

        /* 侧边栏 */
        .sidebar {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .sidebar h5 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 3px solid var(--color-primary);
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* 按钮 */
        .btn-primary {
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            border: none;
            border-radius: 50px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(99, 102, 241, 0.4);
        }

        .btn-outline-primary {
            border: 2px solid var(--color-primary);
            color: var(--color-primary);
            border-radius: 50px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            background: var(--color-primary);
            color: white;
            transform: translateY(-2px);
        }

        /* 面包屑 */
        .breadcrumb {
            background: white;
            padding: 1.25rem 2rem;
            border-radius: 50px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        /* 页脚 */
        .footer {
            background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
            color: white;
            margin-top: 4rem;
            padding: 3rem 0 1.5rem;
            position: relative;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--color-primary), var(--color-secondary));
        }

        /* 搜索框 */
        .search-form .form-control {
            border-radius: 50px 0 0 50px;
            border: 2px solid #e5e7eb;
            padding: 0.75rem 1.5rem;
        }

        .search-form .btn {
            border-radius: 0 50px 50px 0;
        }

        /* 动画 */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .post-card {
            animation: fadeInUp 0.6s ease-out;
        }

        /* 响应式 */
        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 1.5rem;
            }

            .post-card .card-title {
                font-size: 1.125rem;
            }

            .sidebar {
                margin-top: 2rem;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- 导航栏 -->
    <nav class="navbar navbar-expand-lg sticky-top">
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
                        <button class="btn btn-primary" type="submit">
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
    <main class="main-container">
        @yield('content')
    </main>

    <!-- 页脚 -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>{{ config('app.name') }}</h5>
                    <p class="mb-0">专业的博客平台，让内容创作更简单。</p>
                    <p class="mt-2 mb-0">
                        <small class="text-muted">
                            <i class="fas fa-palette me-1"></i>
                            主题: 博客主题 (Post插件)
                        </small>
                    </p>
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
