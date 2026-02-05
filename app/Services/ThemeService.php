<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;

class ThemeService
{
    protected string $themesPath;
    protected string $pluginsPath;
    protected string $currentTheme;
    protected array $themeConfig;
    protected array $pluginThemes = [];

    public function __construct()
    {
        $this->themesPath = resource_path('themes');
        $this->pluginsPath = base_path('plugins');
        $this->currentTheme = config('theme.current', 'default');
        $this->loadThemeConfig();
        $this->loadPluginThemes();
    }

    /**
     * 加载主题配置
     */
    protected function loadThemeConfig(): void
    {
        $configPath = $this->getThemePath($this->currentTheme) . '/theme.json';
        
        if (File::exists($configPath)) {
            $this->themeConfig = json_decode(File::get($configPath), true);
        } else {
            $this->themeConfig = [];
        }
    }

    /**
     * 加载插件主题
     */
    protected function loadPluginThemes(): void
    {
        if (!File::isDirectory($this->pluginsPath)) {
            return;
        }

        $installedPlugins = $this->getInstalledPlugins();
        
        foreach ($installedPlugins as $pluginName) {
            $pluginThemePath = $this->pluginsPath . '/' . $pluginName . '/resources/themes';
            
            if (File::isDirectory($pluginThemePath)) {
                $themes = File::directories($pluginThemePath);
                
                foreach ($themes as $themePath) {
                    $themeName = basename($themePath);
                    $configPath = $themePath . '/theme.json';
                    
                    if (File::exists($configPath)) {
                        $config = json_decode(File::get($configPath), true);
                        $this->pluginThemes[$pluginName][$themeName] = [
                            'path' => $themePath,
                            'config' => $config,
                            'plugin' => $pluginName,
                        ];
                    }
                }
            }
        }
    }

    /**
     * 获取已安装的插件列表
     */
    protected function getInstalledPlugins(): array
    {
        $installedFile = base_path('plugins/installed.json');
        
        if (!File::exists($installedFile)) {
            return [];
        }

        try {
            $data = json_decode(File::get($installedFile), true);
            return is_array($data) ? $data : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 获取当前主题
     */
    public function getCurrentTheme(): string
    {
        return $this->currentTheme;
    }

    /**
     * 设置当前主题
     */
    public function setCurrentTheme(string $theme): void
    {
        if ($this->themeExists($theme)) {
            $this->currentTheme = $theme;
            $this->loadThemeConfig();
            
            // 更新配置文件
            $this->updateThemeConfig($theme);
            
            // 清除缓存
            Cache::forget('theme.current');
            Cache::forget('theme.config');
        }
    }

    /**
     * 获取主题路径
     */
    public function getThemePath(string $theme = null): string
    {
        $theme = $theme ?? $this->currentTheme;
        return $this->themesPath . '/' . $theme;
    }

    /**
     * 获取主题视图路径
     */
    public function getViewPath(string $theme = null): string
    {
        return $this->getThemePath($theme) . '/views';
    }

    /**
     * 获取主题资源路径
     */
    public function getAssetPath(string $theme = null): string
    {
        return $this->getThemePath($theme) . '/assets';
    }

    /**
     * 检查主题是否存在
     */
    public function themeExists(string $theme): bool
    {
        return File::isDirectory($this->getThemePath($theme));
    }

    /**
     * 获取所有可用主题
     */
    public function getAvailableThemes(): array
    {
        $themes = [];
        
        // 1. 加载系统主题
        $directories = File::directories($this->themesPath);

        foreach ($directories as $directory) {
            $themeName = basename($directory);
            $configPath = $directory . '/theme.json';

            if (File::exists($configPath)) {
                $config = json_decode(File::get($configPath), true);
                $themes[$themeName] = array_merge($config, [
                    'type' => 'system',
                    'path' => $directory,
                ]);
            } else {
                $themes[$themeName] = [
                    'name' => $themeName,
                    'version' => '1.0.0',
                    'description' => '无描述',
                    'type' => 'system',
                    'path' => $directory,
                ];
            }
        }

        // 2. 加载插件主题
        foreach ($this->pluginThemes as $pluginName => $pluginThemeList) {
            foreach ($pluginThemeList as $themeName => $themeData) {
                $key = "{$pluginName}::{$themeName}";
                $themes[$key] = array_merge($themeData['config'], [
                    'type' => 'plugin',
                    'plugin' => $pluginName,
                    'path' => $themeData['path'],
                ]);
            }
        }

        return $themes;
    }

    /**
     * 获取插件主题列表
     */
    public function getPluginThemes(): array
    {
        return $this->pluginThemes;
    }

    /**
     * 检查是否为插件主题
     */
    public function isPluginTheme(string $theme): bool
    {
        return str_contains($theme, '::');
    }

    /**
     * 解析插件主题名称
     */
    public function parsePluginTheme(string $theme): ?array
    {
        if (!$this->isPluginTheme($theme)) {
            return null;
        }

        [$plugin, $themeName] = explode('::', $theme, 2);
        
        return [
            'plugin' => $plugin,
            'theme' => $themeName,
        ];
    }

    /**
     * 获取插件主题路径
     */
    public function getPluginThemePath(string $plugin, string $theme): string
    {
        return $this->pluginsPath . '/' . $plugin . '/resources/themes/' . $theme;
    }

    /**
     * 获取插件主题视图路径
     */
    public function getPluginThemeViewPath(string $plugin, string $theme): string
    {
        return $this->getPluginThemePath($plugin, $theme) . '/views';
    }

    /**
     * 获取插件主题资源路径
     */
    public function getPluginThemeAssetPath(string $plugin, string $theme): string
    {
        return $this->getPluginThemePath($plugin, $theme) . '/assets';
    }

    /**
     * 获取主题配置
     */
    public function getThemeConfig(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->themeConfig;
        }

        return data_get($this->themeConfig, $key, $default);
    }

    /**
     * 获取主题资源URL
     */
    public function asset(string $path): string
    {
        // 检查是否为插件主题
        if ($this->isPluginTheme($this->currentTheme)) {
            $parsed = $this->parsePluginTheme($this->currentTheme);
            return asset('plugins/' . $parsed['plugin'] . '/themes/' . $parsed['theme'] . '/' . ltrim($path, '/'));
        }
        
        return asset('themes/' . $this->currentTheme . '/' . ltrim($path, '/'));
    }

    /**
     * 注册主题视图命名空间
     */
    public function registerViewNamespace(): void
    {
        // 1. 首先注册插件主题视图（优先级最高）
        $this->registerPluginThemeViews();
        
        // 2. 然后注册主主题视图
        $viewPath = $this->getViewPath();
        
        if (File::isDirectory($viewPath)) {
            View::addNamespace('theme', $viewPath);
            
            // 添加到视图查找路径的最前面
            View::getFinder()->prependLocation($viewPath);
        }
    }

    /**
     * 注册插件主题视图
     */
    protected function registerPluginThemeViews(): void
    {
        foreach ($this->pluginThemes as $pluginName => $themes) {
            foreach ($themes as $themeName => $themeData) {
                $viewPath = $themeData['path'] . '/views';
                
                if (File::isDirectory($viewPath)) {
                    // 为每个插件主题注册独立的命名空间
                    View::addNamespace("plugin.{$pluginName}.{$themeName}", $viewPath);
                    
                    // 如果是当前主题，也添加到主查找路径（最高优先级）
                    if ($themeName === $this->currentTheme) {
                        View::getFinder()->prependLocation($viewPath);
                    }
                }
            }
        }
    }

    /**
     * 更新主题配置文件
     */
    protected function updateThemeConfig(string $theme): void
    {
        $configPath = config_path('theme.php');
        $content = "<?php\n\nreturn [\n    'current' => '{$theme}',\n];\n";
        
        File::put($configPath, $content);
    }

    /**
     * 获取主题布局
     */
    public function getLayout(string $layout = 'app'): string
    {
        return "theme::layouts.{$layout}";
    }

    /**
     * 获取主题视图
     */
    public function view(string $view): string
    {
        return "theme::{$view}";
    }

    /**
     * 编译主题资源
     */
    public function compileAssets(string $theme = null): bool
    {
        $theme = $theme ?? $this->currentTheme;
        
        // 检查是否为插件主题
        if ($this->isPluginTheme($theme)) {
            return $this->compilePluginThemeAssets($theme);
        }
        
        $assetPath = $this->getAssetPath($theme);
        $publicPath = public_path('themes/' . $theme);

        if (!File::isDirectory($assetPath)) {
            return false;
        }

        // 创建公共目录
        if (!File::isDirectory($publicPath)) {
            File::makeDirectory($publicPath, 0755, true);
        }

        // 复制资源文件
        File::copyDirectory($assetPath, $publicPath);

        return true;
    }

    /**
     * 编译插件主题资源
     */
    protected function compilePluginThemeAssets(string $theme): bool
    {
        $parsed = $this->parsePluginTheme($theme);
        if (!$parsed) {
            return false;
        }

        $assetPath = $this->getPluginThemeAssetPath($parsed['plugin'], $parsed['theme']);
        $publicPath = public_path('plugins/' . $parsed['plugin'] . '/themes/' . $parsed['theme']);

        if (!File::isDirectory($assetPath)) {
            return false;
        }

        // 创建公共目录
        if (!File::isDirectory($publicPath)) {
            File::makeDirectory($publicPath, 0755, true);
        }

        // 复制资源文件
        File::copyDirectory($assetPath, $publicPath);

        return true;
    }

    /**
     * 编译所有主题资源
     */
    public function compileAllAssets(): array
    {
        $results = [];
        $themes = $this->getAvailableThemes();

        foreach (array_keys($themes) as $themeName) {
            $results[$themeName] = $this->compileAssets($themeName);
        }

        return $results;
    }

    /**
     * 获取主题颜色配置
     */
    public function getColors(): array
    {
        return $this->getThemeConfig('colors', [
            'primary' => '#3490dc',
            'secondary' => '#6c757d',
            'success' => '#38c172',
            'danger' => '#e3342f',
            'warning' => '#ffed4e',
            'info' => '#6cb2eb',
        ]);
    }

    /**
     * 获取主题字体配置
     */
    public function getFonts(): array
    {
        return $this->getThemeConfig('fonts', [
            'body' => 'system-ui, -apple-system, sans-serif',
            'heading' => 'system-ui, -apple-system, sans-serif',
            'mono' => 'Menlo, Monaco, Consolas, monospace',
        ]);
    }
}
