<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 钩子系统配置
    |--------------------------------------------------------------------------
    |
    | 这里是钩子系统的配置选项
    |
    */

    // 是否启用自动发现
    'auto_discovery' => env('HOOKS_AUTO_DISCOVERY', true),

    // 是否启用缓存
    'cache_enabled' => env('HOOKS_CACHE_ENABLED', true),

    // 缓存前缀
    'cache_prefix' => env('HOOKS_CACHE_PREFIX', 'hooks:'),

    // 缓存过期时间（小时）
    'cache_ttl' => env('HOOKS_CACHE_TTL', 24),

    // 发现路径
    'discovery_paths' => [
        app_path('Hooks'),
        app_path('Hooks/Custom'),
        base_path('plugins/*/app/Hooks'),
    ],

    // 排除路径
    'exclude_paths' => [
        app_path('Hooks/Contracts'),
        app_path('Hooks/Exceptions'),
    ],

    // 是否启用中间件
    'middleware_enabled' => env('HOOKS_MIDDLEWARE_ENABLED', true),

    // 默认钩子分组
    'default_group' => 'default',

    // 默认优先级
    'default_priority' => 10,

    // 是否记录钩子执行日志
    'log_execution' => env('HOOKS_LOG_EXECUTION', false),

    // 日志级别
    'log_level' => env('HOOKS_LOG_LEVEL', 'debug'),

    // 是否启用性能监控
    'performance_monitoring' => env('HOOKS_PERFORMANCE_MONITORING', false),

    // 性能阈值（毫秒）
    'performance_threshold' => env('HOOKS_PERFORMANCE_THRESHOLD', 100),

    // 内置钩子配置（仅定义钩子点，不包含业务逻辑）
    'builtin_hooks' => [
        // 系统钩子点定义
        'system' => [
            'app.booting',
            'app.booted',
            'app.terminating',
        ],

        // 认证钩子点定义
        'auth' => [
            'user.login.before',
            'user.login.after',
            'user.logout.before',
            'user.logout.after',
            'user.registered',
            'user.password.changed',
        ],

        // 数据库钩子点定义
        'database' => [
            'model.creating',
            'model.created',
            'model.updating',
            'model.updated',
            'model.deleting',
            'model.deleted',
            'model.saving',
            'model.saved',
        ],

        // 插件钩子点定义
        'plugin' => [
            'plugin.installing',
            'plugin.installed',
            'plugin.enabling',
            'plugin.enabled',
            'plugin.disabling',
            'plugin.disabled',
            'plugin.uninstalling',
            'plugin.uninstalled',
            'plugin.deleting',
            'plugin.deleted',
        ],

        // 缓存钩子点定义
        'cache' => [
            'cache.clearing',
            'cache.cleared',
            'cache.writing',
            'cache.written',
            'cache.forgetting',
            'cache.forgotten',
        ],

        // HTTP钩子点定义
        'http' => [
            'request.received',
            'request.processed',
            'response.sending',
            'response.sent',
        ],

        // 视图钩子点定义
        'view' => [
            'view.before_render',
            'view.after_render',
            'view.composing',
            'view.creating',
            'view.data_injection',
            'view.template_modification',
            'view.theme_switching',
            'view.layout_modification',
        ],
    ],

    // 钩子别名
    'aliases' => [
        'before_login' => 'user.login.before',
        'after_login' => 'user.login.after',
        'before_logout' => 'user.logout.before',
        'after_logout' => 'user.logout.after',
    ],

    // 钩子中间件
    'middleware' => [
        // 全局中间件
        'global' => [
            // App\Hooks\Middleware\LoggingMiddleware::class,
            // App\Hooks\Middleware\PerformanceMiddleware::class,
        ],

        // 特定钩子的中间件
        'specific' => [
            // 'user.login' => [
            //     App\Hooks\Middleware\AuthMiddleware::class,
            // ],
        ],
    ],
];