<?php

namespace App\Plugins;

use App\Models\Theme;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\File;
use Throwable;

class ThemeManager
{
    protected string $themeBasePath = '';

    /**
     * 初始化主题
     */
    public function boot(): void
    {
        $theme = $this->getActiveTheme();
        if (!$theme) {
            return;
        }
        $this->themeBasePath = base_path('themes/' . $theme->folder);
        $this->registerThemeComponents();
        $this->registerThemeViewDirectory();
    }

    /**
     * 获取当前启用的主题
     */
    protected function getActiveTheme(): ?Theme
    {
        return $this->rescue(function () {
            if ($cookie = Cookie::get('theme')) {
                $theme = Theme::where('folder', $cookie)->first();
                if ($theme) {
                    return $theme;
                }
            }
            return Theme::where('active', 1)->first();
        });
    }

    /**
     * 注册 Blade 匿名组件路径
     */
    private function registerThemeComponents(): void
    {
        Blade::anonymousComponentPath($this->themeBasePath . '/components');
    }

    /**
     * 指定模板目录
     */
    public function registerThemeViewDirectory():void
    {
        if (File::exists($this->themeBasePath)) {
            view()->addNamespace('blog', $this->themeBasePath);
        }
    }

    /**
     * 容错执行
     */
    protected function rescue(callable $callback, $rescue = null)
    {
        try {
            return $callback();
        } catch (Throwable $e) {
            report($e);
            return value($rescue);
        }
    }
}
