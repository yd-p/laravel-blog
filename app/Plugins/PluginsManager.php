<?php

namespace App\Plugins;

use Composer\Autoload\ClassLoader;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use RuntimeException;

class PluginsManager
{
    protected string $pluginBasePath = 'plugins';
    protected string $pluginInstalledFile = 'plugins/installed.json';
    protected array $hooks = [];
    protected Filesystem $files;

    public function __construct()
    {
        $this->files = new Filesystem();
    }

    /**
     * 加载插件
     */
    public function loadPlugins(ClassLoader $loader, Application $app): void
    {
        $installedPlugins = $this->loadInstalledPlugins();
        foreach (glob(base_path($this->pluginBasePath . '/*'), GLOB_ONLYDIR) as $pluginPath) {
            $pluginName = basename($pluginPath);

            if (!in_array($pluginName, $installedPlugins)) {
                continue;
            }
            // 加载插件的自动加载文件
            $autoloadPath = $pluginPath . '/vendor/autoload.php';
            if (file_exists($autoloadPath)) {
                require_once $autoloadPath;
            }
            // 解析composer.json
            $composerFile = $pluginPath . '/composer.json';
            if (!file_exists($composerFile)) {
                continue;
            }
            try {
                $config = json_decode(file_get_contents($composerFile), true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                logger()->warning("插件 [$pluginName] 的 composer.json 无效: " . $e->getMessage());
                continue;
            }
            // 注册PSR-4命名空间
            if (isset($config['autoload']['psr-4'])) {
                foreach ($config['autoload']['psr-4'] as $namespace => $relPath) {
                    $fullPath = $pluginPath . '/' . trim($relPath, '/');
                    if (is_dir($fullPath)) {
                        $loader->addPsr4(rtrim($namespace, '\\') . '\\', $fullPath);
                    }
                }
            }
            // 注册服务提供者
            if (isset($config['extra']['laravel']['providers'])) {
                foreach ($config['extra']['laravel']['providers'] as $provider) {
                    try {
                        $app->register($provider);
                    } catch (\Throwable $e) {
                        logger()->error("插件 [$pluginName] 注册服务提供者 [$provider] 失败: " . $e->getMessage());
                    }
                }
            }
            // 注册插件钩子
            $this->registerPluginHooks($pluginName, $config);
        }
    }

    /**
     * 安装插件
     */
    public function installFromZip(string $zipPath): bool
    {
        $tempDir = sys_get_temp_dir() . '/plugin_' . Str::random(10);
        $this->files->makeDirectory($tempDir, 0755, true);

        try {
            // 解压ZIP
            $zip = new \ZipArchive();
            if ($zip->open($zipPath) !== true) {
                throw new RuntimeException('无法打开插件压缩包');
            }

            $zip->extractTo($tempDir);
            $zip->close();

            // 检测插件结构（寻找composer.json）
            $pluginDir = $this->findPluginRoot($tempDir);
            if (!$pluginDir) {
                throw new RuntimeException('无效的插件结构，未找到composer.json');
            }

            $pluginName = basename($pluginDir);
            $targetPath = base_path($this->pluginBasePath . '/' . $pluginName);

            // 检查是否已安装
            if ($this->files->exists($targetPath)) {
                throw new RuntimeException("插件 [$pluginName] 已存在");
            }

            // 移动插件到目标目录
            $this->files->move($pluginDir, $targetPath);

            // 安装依赖
            $this->installDependencies($targetPath);

            // 添加到已安装列表
            $this->addInstalledPlugin($pluginName);

            // 触发安装钩子
            $this->callHook('plugin.installed', $pluginName);

            return true;
        } catch (\Throwable $e) {
            logger()->error("安装插件失败: " . $e->getMessage());
            return false;
        } finally {
            $this->files->deleteDirectory($tempDir);
        }
    }

    /**
     * 查找插件根目录（包含composer.json的目录）
     */
    protected function findPluginRoot(string $dir): ?string
    {
        if ($this->files->exists($dir . '/composer.json')) {
            return $dir;
        }

        foreach ($this->files->directories($dir) as $subDir) {
            if ($this->files->exists($subDir . '/composer.json')) {
                return $subDir;
            }
        }

        return null;
    }

    /**
     * 安装插件依赖
     */
    protected function installDependencies(string $pluginPath): void
    {
        if (!$this->files->exists($pluginPath . '/composer.json')) {
            return;
        }

        $command = "cd " . escapeshellarg($pluginPath) . " && composer install --no-dev --prefer-dist --no-interaction";
        exec($command, $output, $result);

        if ($result !== 0) {
            throw new RuntimeException("插件依赖安装失败: " . implode("\n", $output));
        }
    }

    /**
     * 卸载插件（保留文件）
     */
    public function uninstall(string $pluginName): bool
    {
        if (!$this->isInstalled($pluginName)) {
            return false;
        }

        // 触发卸载前钩子
        $this->callHook('plugin.uninstalling', $pluginName);

        // 从已安装列表移除
        $installed = $this->loadInstalledPlugins();
        $installed = array_filter($installed, fn($name) => $name !== $pluginName);
        $this->saveInstalledPlugins($installed);

        // 触发卸载后钩子
        $this->callHook('plugin.uninstalled', $pluginName);

        return true;
    }

    /**
     * 删除插件（包括文件）
     */
    public function delete(string $pluginName): bool
    {
        if (!$this->isInstalled($pluginName)) {
            return false;
        }

        $pluginPath = base_path($this->pluginBasePath . '/' . $pluginName);

        // 触发删除前钩子
        $this->callHook('plugin.deleting', $pluginName);

        // 先卸载
        $this->uninstall($pluginName);

        // 删除文件
        if ($this->files->exists($pluginPath)) {
            $this->files->deleteDirectory($pluginPath);
        }

        // 触发删除后钩子
        $this->callHook('plugin.deleted', $pluginName);

        return true;
    }


    /**
     * 注册插件钩子（从composer.json读取）
     */
    protected function registerPluginHooks(string $pluginName, array $config): void
    {
        if (!isset($config['extra']['hooks'])) {
            return;
        }
        foreach ($config['extra']['hooks'] as $hookName => $callbacks) {
            foreach ((array)$callbacks as $callback) {
                $this->addHook($hookName, $callback, $pluginName);
            }
        }
    }

    /**
     * 添加钩子回调
     */
    protected function addHook(string $pluginName, string $hookName, callable|string $callback): void
    {
        if (!isset($this->hooks[$pluginName])) {
            $this->hooks[$pluginName] = [];
        }
        if (!isset($this->hooks[$pluginName][$hookName])) {
            $this->hooks[$pluginName][$hookName] = [];
        }

        $callbackId = $this->getCallbackId($callback, $pluginName);
        foreach ($this->hooks[$pluginName][$hookName] as $existing) {
            if ($this->getCallbackId($existing['callback'], $pluginName) === $callbackId) {
                return;
            }
        }

        $this->hooks[$pluginName][$hookName][] = [
            'callback' => $callback,
            'id' => $callbackId
        ];
    }

    protected function getCallbackId(callable|string $callback, string $pluginName): string
    {
        if (is_string($callback)) {
            // 字符串格式（如 "Class@method"），结合插件名确保唯一性
            return $pluginName . '|' . $callback;
        } elseif (is_array($callback) && isset($callback[0], $callback[1])) {
            // 数组格式（如 [Class::class, 'method']）
            $class = is_object($callback[0]) ? get_class($callback[0]) : $callback[0];
            return $pluginName . '|' . $class . '@' . $callback[1];
        } elseif ($callback instanceof \Closure) {
            // 闭包（通过哈希值区分）
            return $pluginName . '|closure:' . spl_object_hash($callback);
        }
        return '';
    }

    /**
     * 调用钩子
     */
    public function callHook(string $hookName, string $targetPlugin, ...$params): array
    {
        $results = [];
        // 只执行目标插件的钩子
        if (!isset($this->hooks[$targetPlugin][$hookName])) {
            return $results;
        }

        // 自动将目标插件名作为第一个参数，合并用户传入的其他参数
        $callbackParams = array_merge([$targetPlugin], $params);

        foreach ($this->hooks[$targetPlugin][$hookName] as $hook) {
            try {
                $callback = $hook['callback'];
                if (is_string($callback) && str_contains($callback, '@')) {
                    [$class, $method] = explode('@', $callback, 2);
                    $callback = [app($class), $method];
                }
                // 传递合并后的参数（含目标插件名）
                $results[] = call_user_func_array($callback, $callbackParams);
            } catch (\Throwable $e) {
                logger()->error("插件 [$targetPlugin] 执行钩子 [$hookName] 失败: " . $e->getMessage());
            }
        }
        return $results;
    }

    /**
     * 加载已安装插件列表
     */
    protected function loadInstalledPlugins(): array
    {
        $file = base_path($this->pluginInstalledFile);
        if (!file_exists($file)) {
            return [];
        }

        try {
            $data = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
            return is_array($data) ? $data : [];
        } catch (\JsonException $e) {
            logger()->warning("installed.json 解析失败: " . $e->getMessage());
            return [];
        }
    }

    /**
     * 保存已安装插件列表
     */
    protected function saveInstalledPlugins(array $plugins): void
    {
        $file = base_path($this->pluginInstalledFile);
        $this->files->ensureDirectoryExists(dirname($file));
        $this->files->put($file, json_encode(array_unique($plugins), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * 添加插件到已安装列表
     */
    protected function addInstalledPlugin(string $pluginName): void
    {
        $installed = $this->loadInstalledPlugins();
        $installed[] = $pluginName;
        $this->saveInstalledPlugins($installed);
    }

    /**
     * 检查插件是否已安装
     */
    public function isInstalled(string $pluginName): bool
    {
        return in_array($pluginName, $this->loadInstalledPlugins());
    }

    /**
     * 获取插件信息
     */
    public function getPluginInfo(string $pluginName): ?array
    {
        $pluginPath = base_path($this->pluginBasePath . '/' . $pluginName);
        $composerFile = $pluginPath . '/composer.json';

        if (!file_exists($composerFile)) {
            return null;
        }

        try {
            return json_decode(file_get_contents($composerFile), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            logger()->warning("插件 [$pluginName] 信息解析失败: " . $e->getMessage());
            return null;
        }
    }

    /**
     * 启用插件（添加到已安装列表并触发启动钩子）
     */
    public function enable(string $pluginName): bool
    {
        // 检查插件目录是否存在
        $pluginPath = base_path($this->pluginBasePath . '/' . $pluginName);
        if (!$this->files->isDirectory($pluginPath)) {
            logger()->error("启用插件失败：插件 [$pluginName] 目录不存在");
            return false;
        }
        // 检查是否已启用
        if ($this->isInstalled($pluginName)) {
            return true; // 已启用则直接返回成功
        }
        //手动注册该插件的钩子
        $this->registerSinglePluginHooks($pluginName);

        // 触发启用前钩子
        $this->callHook('plugin.enabling', $pluginName);

        // 添加到已安装列表（启用）
        $this->addInstalledPlugin($pluginName);

        // 触发启用后钩子
        $this->callHook('plugin.enabled', $pluginName);

        return true;
    }

    /**
     * 禁用插件（从已安装列表移除并触发禁用钩子）
     */
    public function disable(string $pluginName): bool
    {
        // 检查是否已安装
        if (!$this->isInstalled($pluginName)) {
            return true; // 未安装则直接返回成功
        }

        $this->registerSinglePluginHooks($pluginName);

        // 触发禁用前钩子
        $this->callHook('plugin.disabling', $pluginName);

        // 从已安装列表移除（禁用）
        $installed = $this->loadInstalledPlugins();
        $installed = array_filter($installed, fn($name) => $name !== $pluginName);
        $this->saveInstalledPlugins($installed);

        // 触发禁用后钩子
        $this->callHook('plugin.disabled', $pluginName);
        // 清理该插件的所有钩子（可选，彻底避免残留）
        $this->removePluginHooks($pluginName);
        return true;
    }

    // 手动注册单个插件的钩子（核心方法）
    protected function registerSinglePluginHooks(string $pluginName): void
    {
        $pluginPath = base_path($this->pluginBasePath . '/' . $pluginName);
        $composerFile = $pluginPath . '/composer.json';

        if (!is_dir($pluginPath) || !file_exists($composerFile)) {
            return;
        }

        try {
            $config = json_decode(file_get_contents($composerFile), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            logger()->error("插件 [$pluginName] 解析composer.json失败：" . $e->getMessage());
            return;
        }

        // 临时注册自动加载（保持不变）
        if (isset($config['autoload']['psr-4'])) {
            $loader = include base_path('vendor/autoload.php');
            if ($loader instanceof ClassLoader) {
                foreach ($config['autoload']['psr-4'] as $namespace => $relPath) {
                    $fullPath = $pluginPath . '/' . trim($relPath, '/');
                    if (is_dir($fullPath)) {
                        $loader->addPsr4(rtrim($namespace, '\\') . '\\', $fullPath);
                    }
                }
            }
        }

        // 注册钩子（按插件分组）
        if (isset($config['extra']['hooks'])) {
            foreach ($config['extra']['hooks'] as $hookName => $callbacks) {
                foreach ((array)$callbacks as $callback) {
                    $this->addHook($pluginName, $hookName, $callback); // 传入插件名
                }
            }
        }
    }


    // 移除指定插件的所有钩子
    protected function removePluginHooks(string $pluginName): void
    {
        unset($this->hooks[$pluginName]);
    }
}
