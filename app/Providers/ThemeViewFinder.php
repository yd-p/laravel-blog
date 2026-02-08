<?php

namespace App\Providers;

use Illuminate\View\FileViewFinder;

class ThemeViewFinder extends FileViewFinder
{
    /**
     * 当前活动主题
     *
     * @var null|string
     */
    protected $activeTheme;

    /**
     * 父主题
     *
     * @var null|string
     */
    protected $parentTheme;

    /**
     * 获取视图查找器实例
     *
     * @return \Illuminate\View\ViewFinderInterface
     */
    public function getViewFinder()
    {
        return app('view')->getFinder();
    }

    /**
     * 设置当前活动主题
     *
     * @param string $theme 主题名称
     * @param string|null $parentTheme 父主题名称
     * @return void
     */
    public function setActiveTheme(string $theme, ?string $parentTheme = null): void
    {
        if ($theme) {
            $this->clearThemes();

            if ($parentTheme) {
                $this->registerTheme($parentTheme);

                $this->parentTheme = $parentTheme;
            }

            $this->registerTheme($theme);

            $this->activeTheme = $theme;
        }
    }

    /**
     * 设置视图命名空间提示
     *
     * @param array $hints
     * @return void
     */
    public function setHints($hints): void
    {
        $this->hints = $hints;
    }

    /**
     * 获取主题路径
     *
     * @param string $theme 主题名称
     * @param string|null $path 相对路径
     * @return string
     * @throws ThemeBasePathNotDefined
     */
    public function getThemePath(string $theme, ?string $path = null): string
    {
        return $this->resolvePath(
            base_path('themes') . DIRECTORY_SEPARATOR . $theme . ($path ? DIRECTORY_SEPARATOR . $path : '')
        );
    }

    /**
     * 获取主题视图路径
     *
     * @param string|null $theme 主题名称
     * @return string
     */
    public function getThemeViewPath(?string $theme = null): string
    {
        $theme = $theme ?? $this->getActiveTheme();

        return $this->getThemePath($theme, 'views');
    }

    /**
     * 获取当前活动主题名称
     *
     * @return null|string
     */
    public function getActiveTheme()
    {
        return $this->activeTheme;
    }

    /**
     * 获取父主题名称
     *
     * @return null|string
     */
    public function getParentTheme()
    {
        return $this->parentTheme;
    }

    /**
     * 清除已注册的主题路径
     *
     * @return void
     */
    public function clearThemes(): void
    {
        $paths = $this->getViewFinder()->getPaths();

        if ($this->getActiveTheme()) {
            if (($key = array_search($this->getThemeViewPath($this->getActiveTheme()), $paths)) !== false) {
                unset($paths[$key]);
            }
        }

        if ($this->getParentTheme()) {
            if (($key = array_search($this->getThemeViewPath($this->getParentTheme()), $paths)) !== false) {
                unset($paths[$key]);
            }
        }

        $this->activeTheme = null;
        $this->parentTheme = null;
        $this->getViewFinder()->setPaths($paths);
    }

    /**
     * 注册主题
     *
     * @param string $theme 主题名称
     * @return void
     */
    public function registerTheme(string $theme): void
    {
        $this->getViewFinder()->prependLocation($this->getThemeViewPath($theme));

        $this->registerNameSpacesForTheme($theme);
    }

    /**
     * 为主题注册命名空间视图路径 (vendor views)
     *
     * @param string $theme 主题名称
     * @return void
     */
    public function registerNameSpacesForTheme(string $theme): void
    {
        $vendorViewsPath = $this->getThemeViewPath($theme) . DIRECTORY_SEPARATOR . 'vendor';
        if (is_dir($vendorViewsPath)) {
            $directories = scandir($vendorViewsPath);

            foreach ($directories as $namespace) {
                if ($namespace != '.' && $namespace != '..') {
                    $path = $vendorViewsPath . DIRECTORY_SEPARATOR . $namespace;
                    $this->getViewFinder()->prependNamespace($namespace, $path);
                }
            }
        }
    }
}
