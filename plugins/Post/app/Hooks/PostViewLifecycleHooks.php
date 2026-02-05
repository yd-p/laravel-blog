<?php

namespace Plugins\Post\Hooks;

use App\Services\ViewLifecycleService;
use App\Hooks\Facades\Hook;

/**
 * Post 插件视图生命周期钩子示例
 * 
 * 展示如何在插件中使用视图生命周期系统
 */
class PostViewLifecycleHooks
{
    protected ViewLifecycleService $lifecycle;

    public function __construct(ViewLifecycleService $lifecycle)
    {
        $this->lifecycle = $lifecycle;
    }

    /**
     * 注册所有视图生命周期钩子
     */
    public function register(): void
    {
        $this->registerBeforeRenderHooks();
        $this->registerComposingHooks();
        $this->registerCreatingHooks();
    }

    /**
     * 注册渲染前钩子
     */
    protected function registerBeforeRenderHooks(): void
    {
        // 在所有文章视图渲染前注入数据
        $this->lifecycle->registerLifecycleHook(
            'view.before_render',
            'post.*',
            function ($viewName, $data) {
                return [
                    'plugin_name' => 'Post Plugin',
                    'plugin_version' => '1.0.0',
                ];
            },
            10
        );

        // 在文章详情页渲染前添加阅读统计
        $this->lifecycle->registerLifecycleHook(
            'view.before_render',
            'post.show',
            function ($viewName, $data) {
                if (isset($data['post'])) {
                    // 增加阅读量
                    $data['post']->increment('view_count');
                }
                return $data;
            },
            15
        );
    }

    /**
     * 注册视图组合钩子
     */
    protected function registerComposingHooks(): void
    {
        // 使用 Hook 系统注册视图组合钩子
        Hook::register('view.composing', function ($viewName, $data) {
            // 为所有文章相关视图注入最新文章
            if (str_starts_with($viewName, 'post.')) {
                return [
                    'data' => [
                        'latest_posts' => \App\Models\Post::latest()
                            ->take(5)
                            ->get(),
                    ]
                ];
            }
            return [];
        }, 10, 'post-plugin');

        // 注入文章分类
        Hook::register('view.composing', function ($viewName, $data) {
            if (str_starts_with($viewName, 'post.')) {
                return [
                    'data' => [
                        'post_categories' => \App\Models\Category::withCount('posts')
                            ->get(),
                    ]
                ];
            }
            return [];
        }, 10, 'post-plugin');
    }

    /**
     * 注册视图创建钩子
     */
    protected function registerCreatingHooks(): void
    {
        // 在文章视图创建时记录日志
        Hook::register('view.creating', function ($viewName, $view) {
            if (str_starts_with($viewName, 'post.')) {
                logger()->info('Post view creating', [
                    'view' => $viewName,
                    'timestamp' => now(),
                ]);
            }
        }, 10, 'post-plugin');
    }

    /**
     * 注册自定义插件钩子点
     */
    public function registerCustomHooks(): void
    {
        // 文章内容渲染前钩子
        Hook::register('plugin.post.before_content', function ($viewName, $data) {
            return '<div class="post-meta">插件钩子：文章内容前</div>';
        }, 10, 'post-plugin');

        // 文章内容渲染后钩子
        Hook::register('plugin.post.after_content', function ($viewName, $data) {
            return '<div class="post-footer">插件钩子：文章内容后</div>';
        }, 10, 'post-plugin');

        // 侧边栏钩子
        Hook::register('plugin.post.sidebar', function ($viewName, $data) {
            $html = '<div class="post-sidebar-widget">';
            $html .= '<h3>热门文章</h3>';
            $html .= '<ul>';
            
            $popularPosts = \App\Models\Post::orderBy('view_count', 'desc')
                ->take(5)
                ->get();
            
            foreach ($popularPosts as $post) {
                $html .= '<li><a href="/posts/' . $post->slug . '">' . $post->title . '</a></li>';
            }
            
            $html .= '</ul>';
            $html .= '</div>';
            
            return $html;
        }, 10, 'post-plugin');
    }
}
