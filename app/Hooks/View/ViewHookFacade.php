<?php

namespace App\Hooks\View;

use Illuminate\Support\Facades\Facade;

/**
 * 视图钩子 Facade
 * 
 * @method static string beforeRender(string $viewPattern, callable $callback, int $priority = 10)
 * @method static string afterRender(string $viewPattern, callable $callback, int $priority = 10)
 * @method static string injectData(string $viewPattern, callable $callback, int $priority = 10)
 * @method static string modifyTemplate(string $viewPattern, callable $callback, int $priority = 10)
 * @method static string switchTheme(string $viewPattern, callable $callback, int $priority = 10)
 * @method static array executeBeforeRender(string $viewName, array $data = [])
 * @method static array executeAfterRender(string $viewName, string $content, array $data = [])
 * @method static array executeDataInjection(string $viewName, array $data = [])
 * @method static array registerBatch(array $hooks)
 * @method static array getViewHookStats()
 * @method static void clearViewHookCache()
 * @method static array getRegisteredViews()
 * @method static void setViewData(string $viewName, array $data)
 * @method static array getViewData(string $viewName)
 * 
 * @see \App\Hooks\View\ViewHookManager
 */
class ViewHookFacade extends Facade
{
    /**
     * 获取组件的注册名称
     */
    protected static function getFacadeAccessor(): string
    {
        return ViewHookManager::class;
    }
}