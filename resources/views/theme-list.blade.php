<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>主题列表 - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row mb-4">
            <div class="col">
                <h1><i class="fas fa-palette me-2"></i>可用主题</h1>
                <p class="text-muted">当前主题: <strong>{{ app('theme')->getCurrentTheme() }}</strong></p>
            </div>
            <div class="col-auto">
                <a href="{{ route('home') }}" class="btn btn-outline-primary">
                    <i class="fas fa-home me-1"></i>返回首页
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            @foreach($themes as $slug => $theme)
            <div class="col-md-4 mb-4">
                <div class="card h-100 {{ app('theme')->getCurrentTheme() === $slug ? 'border-primary' : '' }}">
                    @if(isset($theme['screenshot']))
                    <img src="{{ asset(($theme['type'] === 'plugin' ? 'plugins/' . $theme['plugin'] . '/themes/' . $theme['theme'] : 'themes/' . $slug) . '/' . $theme['screenshot']) }}" 
                         class="card-img-top" alt="{{ $theme['name'] }}"
                         onerror="this.src='https://via.placeholder.com/400x250?text={{ urlencode($theme['name']) }}'">
                    @else
                    <div class="card-img-top bg-gradient" style="height: 250px; background: linear-gradient(135deg, {{ $theme['type'] === 'plugin' ? '#8b5cf6' : '#667eea' }} 0%, {{ $theme['type'] === 'plugin' ? '#ec4899' : '#764ba2' }} 100%); display: flex; align-items: center; justify-content: center;">
                        <div class="text-center text-white">
                            <h2>{{ $theme['name'] }}</h2>
                            @if($theme['type'] === 'plugin')
                                <p class="mb-0"><i class="fas fa-puzzle-piece me-1"></i>插件主题</p>
                            @endif
                        </div>
                    </div>
                    @endif
                    
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0">{{ $theme['name'] }}</h5>
                            <div>
                                @if(app('theme')->getCurrentTheme() === $slug)
                                    <span class="badge bg-primary">当前</span>
                                @endif
                                @if($theme['type'] === 'plugin')
                                    <span class="badge bg-info">插件</span>
                                @endif
                            </div>
                        </div>
                        
                        <p class="text-muted small mb-2">
                            <i class="fas fa-tag me-1"></i>版本: {{ $theme['version'] ?? '1.0.0' }}
                        </p>
                        
                        @if($theme['type'] === 'plugin')
                        <p class="text-muted small mb-2">
                            <i class="fas fa-puzzle-piece me-1"></i>插件: {{ $theme['plugin'] }}
                        </p>
                        @endif
                        
                        <p class="card-text">{{ $theme['description'] ?? '无描述' }}</p>
                        
                        @if(isset($theme['author']))
                        <p class="text-muted small mb-2">
                            <i class="fas fa-user me-1"></i>作者: {{ $theme['author'] }}
                        </p>
                        @endif

                        @if(isset($theme['features']) && is_array($theme['features']))
                        <div class="mb-3">
                            @foreach($theme['features'] as $feature)
                                <span class="badge bg-secondary me-1 mb-1">{{ $feature }}</span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    
                    <div class="card-footer bg-white">
                        @if(app('theme')->getCurrentTheme() === $slug)
                            <button class="btn btn-primary w-100" disabled>
                                <i class="fas fa-check me-1"></i>当前使用
                            </button>
                        @else
                            <a href="{{ route('theme.switch', $slug) }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-exchange-alt me-1"></i>切换到此主题
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @if(count($themes) === 0)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            没有找到可用的主题。请确保主题文件夹存在于 <code>resources/themes/</code> 目录中。
        </div>
        @endif

        <div class="card mt-4">
            <div class="card-body">
                <h5><i class="fas fa-info-circle me-2"></i>主题开发说明</h5>
                <p>要创建新主题，请在 <code>resources/themes/</code> 目录下创建新文件夹，并包含以下文件：</p>
                <ul>
                    <li><code>theme.json</code> - 主题配置文件</li>
                    <li><code>views/</code> - 视图文件目录</li>
                    <li><code>assets/</code> - 资源文件目录（CSS、JS、图片等）</li>
                </ul>
                <p class="mb-0">
                    <a href="https://laravel.com/docs" target="_blank" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-book me-1"></i>查看文档
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
